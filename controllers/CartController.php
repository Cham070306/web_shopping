<?php
/**
 * CartController — xử lý AJAX cho giỏ hàng
 * Tất cả response trả về JSON.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

header('Content-Type: application/json');

$orderModel = new Order($conn);
$action     = $_GET['action'] ?? $_POST['action'] ?? '';

// Lấy user_id và session_id
$user_id    = $_SESSION['user']['id'] ?? null;
$session_id = session_id();

switch ($action) {
    case 'add':         handleAdd();         break;
    case 'update':      handleUpdate();      break;
    case 'remove':      handleRemove();      break;
    case 'count':       handleCount();       break;
    case 'applyCoupon': handleApplyCoupon(); break;
    case 'get':         handleGet();         break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/* ================================================================
 *  HELPERS
 * ================================================================ */

/**
 * Format giá VNĐ
 */
function formatVND($price): string
{
    return number_format((int) $price, 0, ',', '.') . ' đ';
}

/**
 * Tính shipping fee: FREE nếu subtotal >= 500.000₫, else 30.000₫
 */
function calcShipping($subtotal): int
{
    return $subtotal >= 500000 ? 0 : 30000;
}

/**
 * Đọc JSON body hoặc POST form data
 */
function getInput(): array
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: [];
    }
    return $_POST;
}

/**
 * Tính subtotal của toàn bộ giỏ hàng
 */
function calcCartTotal($items): float
{
    $total = 0;
    foreach ($items as $item) {
        $unitPrice = ($item['sale_price'] && $item['sale_price'] > 0)
            ? (float) $item['sale_price']
            : (float) $item['price'];

        // Cộng price_diff nếu có variant
        if (!empty($item['price_diff'])) {
            $unitPrice += (float) $item['price_diff'];
        }

        $total += $unitPrice * (int) $item['quantity'];
    }
    return $total;
}

/* ================================================================
 *  ACTION HANDLERS
 * ================================================================ */

/**
 * Thêm sản phẩm vào giỏ
 * POST: {product_id, variant_id?, quantity?}
 */
function handleAdd()
{
    global $orderModel, $conn, $user_id, $session_id;

    $input      = getInput();
    $product_id = (int) ($input['product_id'] ?? 0);
    $variant_id = !empty($input['variant_id']) ? (int) $input['variant_id'] : null;
    $quantity   = max(1, (int) ($input['quantity'] ?? 1));

    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart.', 'require_login' => true]);
        return;
    }

    if (!$product_id) {
        echo json_encode(['success' => false, 'message' => 'Thiếu product_id']);
        return;
    }

    // Kiểm tra sản phẩm tồn tại + lấy stock
    $stmt = $conn->prepare("SELECT id, stock, is_active FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product || !$product['is_active']) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        return;
    }

    // Kiểm tra stock (variant hoặc product)
    $availableStock = (int) $product['stock'];
    if ($variant_id) {
        $vStmt = $conn->prepare("SELECT stock FROM product_variants WHERE id = ? AND product_id = ?");
        $vStmt->bind_param('ii', $variant_id, $product_id);
        $vStmt->execute();
        $variant = $vStmt->get_result()->fetch_assoc();
        if ($variant) {
            $availableStock = (int) $variant['stock'];
        }
    }

    if ($availableStock < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock for this product']);
        return;
    }

    $result = $orderModel->addToCart($user_id, $session_id, $product_id, $variant_id, $quantity);

    if ($result) {
        $cartCount = $orderModel->getCartCount($user_id, $session_id);
        echo json_encode([
            'success'    => true,
            'message'    => 'Added to cart',
            'cart_count' => $cartCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại']);
    }
}

/**
 * Cập nhật số lượng item trong giỏ
 * POST: {cart_id, quantity}
 */
function handleUpdate()
{
    global $orderModel, $conn, $user_id, $session_id;

    $input    = getInput();
    $cart_id  = (int) ($input['cart_id'] ?? 0);
    $quantity = (int) ($input['quantity'] ?? 0);

    if (!$cart_id || $quantity < 1) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }

    // Lấy thông tin cart item để kiểm tra stock
    $stmt = $conn->prepare(
        "SELECT c.product_id, c.variant_id, p.stock, p.price, p.sale_price
         FROM cart c
         JOIN products p ON c.product_id = p.id
         WHERE c.id = ?"
    );
    $stmt->bind_param('i', $cart_id);
    $stmt->execute();
    $cartItem = $stmt->get_result()->fetch_assoc();

    if (!$cartItem) {
        echo json_encode(['success' => false, 'message' => 'Item không tồn tại']);
        return;
    }

    // Kiểm tra stock
    $maxStock = (int) $cartItem['stock'];
    if ($cartItem['variant_id']) {
        $vStmt = $conn->prepare("SELECT stock FROM product_variants WHERE id = ?");
        $vStmt->bind_param('i', $cartItem['variant_id']);
        $vStmt->execute();
        $variant = $vStmt->get_result()->fetch_assoc();
        if ($variant) {
            $maxStock = (int) $variant['stock'];
        }
    }

    if ($quantity > $maxStock) {
        echo json_encode([
            'success'   => false,
            'message'   => "Chỉ còn $maxStock sản phẩm trong kho",
            'max_stock' => $maxStock
        ]);
        return;
    }

    $orderModel->updateCartQty($cart_id, $quantity);

    // Tính lại subtotal item
    $unitPrice = ($cartItem['sale_price'] && $cartItem['sale_price'] > 0)
        ? (float) $cartItem['sale_price']
        : (float) $cartItem['price'];
    $itemSubtotal = $unitPrice * $quantity;

    // Tính lại tổng giỏ hàng
    $cartItems = $orderModel->getCartItems($user_id, $session_id);
    $cartTotal = calcCartTotal($cartItems);
    $cartCount = $orderModel->getCartCount($user_id, $session_id);
    $shipping  = calcShipping($cartTotal);

    echo json_encode([
        'success'       => true,
        'item_subtotal' => formatVND($itemSubtotal),
        'cart_total'    => formatVND($cartTotal),
        'cart_count'    => $cartCount,
        'shipping'      => $shipping > 0 ? formatVND($shipping) : 'FREE',
        'grand_total'   => formatVND($cartTotal + $shipping),
        'raw_subtotal'  => $cartTotal,
        'raw_shipping'  => $shipping
    ]);
}

/**
 * Xoá item khỏi giỏ
 * POST: {cart_id}
 */
function handleRemove()
{
    global $orderModel, $user_id, $session_id;

    $input   = getInput();
    $cart_id = (int) ($input['cart_id'] ?? 0);

    if (!$cart_id) {
        echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
        return;
    }

    $orderModel->removeCartItem($cart_id);

    // Tính lại
    $cartItems = $orderModel->getCartItems($user_id, $session_id);
    $cartTotal = calcCartTotal($cartItems);
    $cartCount = $orderModel->getCartCount($user_id, $session_id);
    $shipping  = calcShipping($cartTotal);

    echo json_encode([
        'success'      => true,
        'cart_count'   => $cartCount,
        'cart_total'   => formatVND($cartTotal),
        'shipping'     => $shipping > 0 ? formatVND($shipping) : 'FREE',
        'grand_total'  => formatVND($cartTotal + $shipping),
        'raw_subtotal' => $cartTotal,
        'raw_shipping' => $shipping
    ]);
}

/**
 * Lấy số lượng giỏ hàng (badge navbar)
 * GET — không cần body
 */
function handleCount()
{
    global $orderModel, $user_id, $session_id;

    $count = $orderModel->getCartCount($user_id, $session_id);
    echo json_encode(['count' => $count]);
}

/**
 * Áp dụng mã giảm giá
 * POST: {coupon_code, subtotal}
 */
function handleApplyCoupon()
{
    global $orderModel;

    $input    = getInput();
    $code     = trim($input['coupon_code'] ?? '');
    $subtotal = (float) ($input['subtotal'] ?? 0);

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mã giảm giá']);
        return;
    }

    $coupon = $orderModel->validateCoupon($code, $subtotal);

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Mã không hợp lệ, đã hết hạn hoặc không đủ điều kiện']);
        return;
    }

    // Tính discount
    if ($coupon['type'] === 'percent') {
        $discount = $subtotal * (float) $coupon['value'] / 100;
        // Áp dụng max_discount nếu có
        if ($coupon['max_discount'] && $discount > (float) $coupon['max_discount']) {
            $discount = (float) $coupon['max_discount'];
        }
    } else {
        // type = fixed
        $discount = (float) $coupon['value'];
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }
    }

    $discount = round($discount);

    // Lưu vào session để checkout sử dụng
    $_SESSION['applied_coupon'] = [
        'code'     => $coupon['code'],
        'type'     => $coupon['type'],
        'value'    => $coupon['value'],
        'discount' => $discount
    ];

    echo json_encode([
        'success'       => true,
        'discount'      => $discount,
        'discount_text' => '- ' . formatVND($discount),
        'coupon_code'   => $coupon['code']
    ]);
}

/**
 * Lấy toàn bộ giỏ hàng (cho trang cart.php render)
 * GET — không cần body
 */
function handleGet()
{
    global $orderModel, $user_id, $session_id;

    $items     = $orderModel->getCartItems($user_id, $session_id);
    $cartTotal = calcCartTotal($items);
    $shipping  = calcShipping($cartTotal);

    echo json_encode([
        'success'      => true,
        'items'        => $items,
        'cart_total'   => formatVND($cartTotal),
        'shipping'     => $shipping > 0 ? formatVND($shipping) : 'FREE',
        'grand_total'  => formatVND($cartTotal + $shipping),
        'raw_subtotal' => $cartTotal,
        'raw_shipping' => $shipping,
        'cart_count'   => $orderModel->getCartCount($user_id, $session_id)
    ]);
}
