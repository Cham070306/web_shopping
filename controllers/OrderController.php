<?php
/**
 * OrderController — xử lý checkout (user) + quản lý đơn hàng (admin)
 *
 * Entry 1: POST từ user/checkout.php → processCheckout()
 * Entry 2: AJAX từ admin → action=update_status | update_payment | get_detail
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/OrderDetail.php';

$orderModel  = new Order($conn);
$detailModel = new OrderDetail($conn);
$action      = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper: flash redirect
function flashRedirect($url, $type, $msg)
{
    $_SESSION[$type] = $msg;
    header("Location: $url");
    exit();
}

// Helper: format VNĐ
function formatPrice($price): string
{
    return number_format((int) $price, 0, ',', '.') . ' đ';
}

// ── Routing ──────────────────────────────────────────────────────────
switch ($action) {

    case 'checkout':
        processCheckout();
        break;

    case 'update_status':
    case 'admin_update_status':
        adminUpdateStatus();
        break;

    case 'update_payment':
    case 'admin_update_payment':
        adminUpdatePayment();
        break;

    case 'get_detail':
        adminGetDetail();
        break;

    case 'cancel':
        userCancelOrder();
        break;

    default:
        // Nếu là POST từ checkout form (không có action param riêng)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
            processCheckout();
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
}

/* ================================================================
 *  USER — Xử lý đặt hàng (checkout)
 * ================================================================ */

function processCheckout()
{
    global $orderModel, $detailModel, $conn;

    $user_id    = $_SESSION['user']['id'] ?? null;
    $session_id = session_id();

    // ── 1. Validate input ──
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $note      = trim($_POST['note'] ?? '');
    $payment   = $_POST['payment_method'] ?? 'cod';
    $coupon_code = trim($_POST['coupon_code'] ?? '');

    // Validate payment method
    $allowedPayments = ['cod', 'bank_transfer', 'momo'];
    if (!in_array($payment, $allowedPayments)) {
        $payment = 'cod';
    }

    if (!$full_name || !$email || !$phone || !$address) {
        flashRedirect('../user/checkout.php', 'error', 'Vui lòng điền đầy đủ thông tin giao hàng');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashRedirect('../user/checkout.php', 'error', 'Email không hợp lệ');
    }

    // ── 2. Lấy cart items ──
    $cartItems = $orderModel->getCartItems($user_id, $session_id);

    if (empty($cartItems)) {
        flashRedirect('../user/cart.php', 'error', 'Giỏ hàng trống, không thể đặt hàng');
    }

    // ── 3. Tính subtotal ──
    $subtotal = 0;
    foreach ($cartItems as $item) {
        $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0)
            ? (float) $item['sale_price']
            : (float) $item['price'];

        if (!empty($item['price_diff'])) {
            $unitPrice += (float) $item['price_diff'];
        }

        $subtotal += $unitPrice * (int) $item['quantity'];
    }

    // ── 4. Validate & tính coupon ──
    $discount = 0;
    $appliedCoupon = '';

    if ($coupon_code) {
        $coupon = $orderModel->validateCoupon($coupon_code, $subtotal);
        if ($coupon) {
            if ($coupon['type'] === 'percent') {
                $discount = $subtotal * (float) $coupon['value'] / 100;
                if ($coupon['max_discount'] && $discount > (float) $coupon['max_discount']) {
                    $discount = (float) $coupon['max_discount'];
                }
            } else {
                $discount = (float) $coupon['value'];
                if ($discount > $subtotal) {
                    $discount = $subtotal;
                }
            }
            $discount = round($discount);
            $appliedCoupon = $coupon['code'];
        }
    } elseif (!empty($_SESSION['applied_coupon'])) {
        // Sử dụng coupon đã apply ở trang cart
        $cached = $_SESSION['applied_coupon'];
        $coupon = $orderModel->validateCoupon($cached['code'], $subtotal);
        if ($coupon) {
            $discount = (int) $cached['discount'];
            $appliedCoupon = $cached['code'];
        }
        unset($_SESSION['applied_coupon']);
    }

    // ── 5. Tính shipping & total ──
    $shipping_fee = $subtotal >= 500000 ? 0 : 30000;
    $total = $subtotal - $discount + $shipping_fee;

    if ($total < 0) $total = 0;

    // ── 6. Kiểm tra stock từng sản phẩm (race condition check) ──
    foreach ($cartItems as $item) {
        $checkStmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND stock >= ?");
        $qty = (int) $item['quantity'];
        $checkStmt->bind_param('ii', $item['product_id'], $qty);
        $checkStmt->execute();
        $stockCheck = $checkStmt->get_result()->fetch_assoc();

        if (!$stockCheck) {
            flashRedirect(
                '../user/cart.php',
                'error',
                'Sản phẩm "' . htmlspecialchars($item['name']) . '" không đủ số lượng trong kho. Vui lòng cập nhật giỏ hàng.'
            );
        }
    }

    // ── 7. Bắt đầu transaction ──
    $conn->begin_transaction();

    try {
        // ── 8. Generate order code ──
        $order_code = $orderModel->generateOrderCode();

        // ── 9. INSERT orders ──
        $orderData = [
            'user_id'        => $user_id,
            'order_code'     => $order_code,
            'full_name'      => $full_name,
            'email'          => $email,
            'phone'          => $phone,
            'address'        => $address,
            'city'           => $city,
            'note'           => $note,
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'shipping_fee'   => $shipping_fee,
            'total'          => $total,
            'coupon_code'    => $appliedCoupon ?: null,
            'payment_method' => $payment,
            'payment_status' => 'pending',
            'status'         => 'pending'
        ];

        $order_id = $orderModel->createOrder($orderData);

        if (!$order_id) {
            throw new Exception('Không thể tạo đơn hàng');
        }

        // ── 10. INSERT order_items + giảm stock ──
        foreach ($cartItems as $item) {
            $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0)
                ? (float) $item['sale_price']
                : (float) $item['price'];

            if (!empty($item['price_diff'])) {
                $unitPrice += (float) $item['price_diff'];
            }

            $qty          = (int) $item['quantity'];
            $itemSubtotal = $unitPrice * $qty;

            // Build variant string
            $variantStr = '';
            if (!empty($item['color'])) $variantStr .= $item['color'];
            if (!empty($item['size']))  $variantStr .= ($variantStr ? ' / ' : '') . $item['size'];

            $orderItem = [
                'product_id'   => $item['product_id'],
                'product_name' => $item['name'],
                'product_sku'  => null,
                'variant'      => $variantStr ?: null,
                'price'        => $unitPrice,
                'quantity'     => $qty,
                'subtotal'     => $itemSubtotal,
                'thumbnail'    => $item['thumbnail']
            ];

            $detailModel->addItem($order_id, $orderItem);

            // Giảm stock
            if (!$detailModel->decreaseStock($item['product_id'], $qty)) {
                throw new Exception('Sản phẩm "' . $item['name'] . '" không đủ hàng');
            }
        }

        // ── 11. Xoá giỏ hàng ──
        $orderModel->clearCart($user_id, $session_id);

        // ── 12. Commit ──
        $conn->commit();

        // ── 13. Lưu order code vào session để trang complete hiển thị ──
        $_SESSION['last_order_code'] = $order_code;
        $_SESSION['last_order_id']   = $order_id;

        // ── 14. Redirect trang hoàn thành ──
        header("Location: ../user/order_complete.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        flashRedirect('../user/checkout.php', 'error', 'Đặt hàng thất bại: ' . $e->getMessage());
    }
}

/* ================================================================
 *  USER — Huỷ đơn hàng
 * ================================================================ */

function userCancelOrder()
{
    global $orderModel;

    $user_id  = $_SESSION['user']['id'] ?? null;
    $order_id = (int) ($_POST['order_id'] ?? 0);

    if (!$user_id || !$order_id) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }

    $result = $orderModel->cancelOrder($order_id, $user_id);

    header('Content-Type: application/json');
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã huỷ đơn hàng']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Không thể huỷ đơn hàng. Chỉ đơn "Chờ xác nhận" mới huỷ được.']);
    }
}

/* ================================================================
 *  ADMIN — Cập nhật trạng thái đơn hàng
 * ================================================================ */

function adminUpdateStatus()
{
    global $orderModel;

    header('Content-Type: application/json');

    // Kiểm tra quyền admin
    $user = $_SESSION['user'] ?? [];
    if (empty($user) || ($user['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        return;
    }

    $order_id = (int) ($_POST['order_id'] ?? 0);
    $status   = trim($_POST['status'] ?? '');

    $allowedStatuses = ['pending', 'confirmed', 'shipping', 'delivered', 'cancelled'];
    if (!$order_id || !in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }

    $result = $orderModel->updateOrderStatus($order_id, $status);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái đơn hàng']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
    }
}

/* ================================================================
 *  ADMIN — Cập nhật trạng thái thanh toán
 * ================================================================ */

function adminUpdatePayment()
{
    global $orderModel;

    header('Content-Type: application/json');

    $user = $_SESSION['user'] ?? [];
    if (empty($user) || ($user['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        return;
    }

    $order_id       = (int) ($_POST['order_id'] ?? 0);
    $payment_status = trim($_POST['payment_status'] ?? '');

    $allowedPayment = ['pending', 'paid', 'failed', 'refunded'];
    if (!$order_id || !in_array($payment_status, $allowedPayment)) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }

    $result = $orderModel->updatePaymentStatus($order_id, $payment_status);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật trạng thái thanh toán']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cập nhật thất bại']);
    }
}

/* ================================================================
 *  ADMIN — Lấy chi tiết đơn hàng (AJAX)
 * ================================================================ */

function adminGetDetail()
{
    global $orderModel, $detailModel;

    header('Content-Type: application/json');

    $user = $_SESSION['user'] ?? [];
    if (empty($user) || ($user['role'] ?? '') !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        return;
    }

    $order_id = (int) ($_GET['order_id'] ?? $_POST['order_id'] ?? 0);

    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu order_id']);
        return;
    }

    $order = $orderModel->getOrderById($order_id);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
        return;
    }

    $items = $detailModel->getItemsByOrderId($order_id);

    // Format giá
    $order['subtotal_formatted']     = formatPrice($order['subtotal']);
    $order['discount_formatted']     = formatPrice($order['discount']);
    $order['shipping_fee_formatted'] = $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'FREE';
    $order['total_formatted']        = formatPrice($order['total']);

    foreach ($items as &$item) {
        $item['price_formatted']    = formatPrice($item['price']);
        $item['subtotal_formatted'] = formatPrice($item['subtotal']);
    }

    echo json_encode([
        'success' => true,
        'order'   => $order,
        'items'   => $items
    ]);
}
