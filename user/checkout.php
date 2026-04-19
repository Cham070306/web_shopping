<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Order.php';

$orderModel = new Order($conn);
$user_id    = $_SESSION['user']['id'] ?? null;
$session_id = session_id();
$cartItems  = $orderModel->getCartItems($user_id, $session_id);

// Redirect nếu giỏ hàng trống
if (empty($cartItems)) {
    $_SESSION['error'] = 'Giỏ hàng trống, vui lòng thêm sản phẩm trước.';
    header("Location: cart.php");
    exit;
}

// Xử lý POST → checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../controllers/OrderController.php';
    // processCheckout() sẽ tự redirect
    exit;
}

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int)$price, 0, ',', '.') . ' đ';
    }
}

// Tính subtotal
$subtotal = 0;
foreach ($cartItems as &$item) {
    $unit_price = ($item['sale_price'] && $item['sale_price'] > 0) ? (float)$item['sale_price'] : (float)$item['price'];
    $unit_price += (float)($item['price_diff'] ?? 0);
    $item['unit_price'] = $unit_price;
    $item['line_total'] = $unit_price * (int)$item['quantity'];
    $subtotal += $item['line_total'];
}
unset($item);

$shipping = $subtotal >= 500000 ? 0 : 30000;

// Coupon từ session (đã apply ở cart page)
$couponDiscount = 0;
$couponCode = '';
if (!empty($_SESSION['applied_coupon'])) {
    $couponDiscount = (int)$_SESSION['applied_coupon']['discount'];
    $couponCode = $_SESSION['applied_coupon']['code'];
}

$total = $subtotal - $couponDiscount + $shipping;
if ($total < 0) $total = 0;

// Prefill nếu đã login
$prefill = ['name' => '', 'email' => '', 'phone' => '', 'address' => '', 'city' => ''];
if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    $prefill['name']  = $_SESSION['user']['name'] ?? '';
    $prefill['email'] = $_SESSION['user']['email'] ?? '';
    $prefill['phone'] = $_SESSION['user']['phone'] ?? '';

    // Lấy default address
    $addrStmt = $conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $addrStmt->bind_param('i', $uid);
    $addrStmt->execute();
    $defaultAddr = $addrStmt->get_result()->fetch_assoc();
    if ($defaultAddr) {
        $prefill['name']    = $defaultAddr['full_name'] ?: $prefill['name'];
        $prefill['phone']   = $defaultAddr['phone'] ?: $prefill['phone'];
        $prefill['address'] = $defaultAddr['address_line'] ?? $defaultAddr['address'] ?? '';
        $prefill['city']    = $defaultAddr['city'] ?? '';
    }
}

// Thumb helper
function checkoutThumb($thumb) {
    if (!$thumb) return '../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../assets/images/' . htmlspecialchars($thumb);
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
.breadcrumb-nav { font-size: 14px; color: #6C7275; margin-bottom: 40px; }
.breadcrumb-nav a { color: #6C7275; text-decoration: none; }
.breadcrumb-nav a:hover { color: #141718; }
.breadcrumb-nav .current { color: #141718; font-weight: 500; }
.page-title { font-size: 40px; font-weight: 600; text-align: center; margin-bottom: 40px; letter-spacing: -0.5px; }

/* Steps indicator */
.checkout-steps { display: flex; justify-content: center; gap: 0; margin-bottom: 48px; }
.step-item { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #6C7275; }
.step-item.active { color: #141718; font-weight: 600; }
.step-item.done { color: #38CB89; }
.step-divider { width: 60px; height: 1px; background: #E8ECEF; margin: 0 16px; align-self: center; }
.step-num {
    width: 32px; height: 32px; border-radius: 50%; border: 1.5px solid #E8ECEF;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px;
}
.step-item.done .step-num { background: #38CB89; border-color: #38CB89; color: #fff; }
.step-item.active .step-num { background: #141718; border-color: #141718; color: #fff; }

.checkout-layout { display: flex; gap: 48px; padding-bottom: 80px; align-items: flex-start; }
.checkout-left { flex: 1; min-width: 0; }
.checkout-right { width: 420px; flex-shrink: 0; }

/* Form */
.form-title { font-size: 20px; font-weight: 600; margin-bottom: 24px; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group { display: flex; flex-direction: column; }
.form-group.full { grid-column: 1 / -1; }
.form-group label {
    font-size: 12px; font-weight: 700; color: #6C7275; text-transform: uppercase;
    margin-bottom: 8px; letter-spacing: 0.05em;
}
.form-group input, .form-group textarea, .form-group select {
    border: 1px solid #E8ECEF; border-radius: 8px; padding: 14px 16px; font-size: 14px;
    color: #141718; outline: none; font-family: 'Inter', sans-serif; transition: border-color 0.2s;
}
.form-group input:focus, .form-group textarea:focus { border-color: #141718; }
.form-group textarea { resize: vertical; min-height: 60px; }

/* Payment */
.payment-title { font-size: 20px; font-weight: 600; margin: 40px 0 20px; }
.payment-options { display: flex; flex-direction: column; gap: 12px; }
.payment-option {
    display: flex; align-items: center; gap: 12px; padding: 16px; border: 1px solid #E8ECEF;
    border-radius: 8px; cursor: pointer; transition: border-color 0.2s;
}
.payment-option:hover { border-color: #141718; }
.payment-option input[type="radio"] { display: none; }
.payment-option input[type="radio"]:checked + .payment-radio { border-color: #141718; }
.payment-option input[type="radio"]:checked + .payment-radio::after { content: ''; display: block; width: 8px; height: 8px; background: #141718; border-radius: 50%; }
.payment-option.selected { border-color: #141718; background: #FAFAFA; }
.payment-radio {
    width: 20px; height: 20px; border: 1.5px solid #E8ECEF; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.payment-icon { font-size: 20px; }
.payment-label { font-size: 14px; font-weight: 500; }

/* Error */
.checkout-error {
    background: #FFF0F0; color: #FF5630; padding: 14px 16px; border-radius: 8px;
    margin-bottom: 24px; border: 1px solid #FF5630; display: flex; align-items: center; gap: 10px;
    font-size: 14px;
}

/* Summary card */
.checkout-summary { background: #F3F5F7; border-radius: 12px; padding: 28px 24px; position: sticky; top: 100px; }
.summary-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; }
.summary-item { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #E8ECEF; }
.summary-item:last-of-type { border-bottom: none; }
.summary-item img { width: 56px; height: 56px; object-fit: cover; border-radius: 6px; background: #fff; }
.summary-item-info { flex: 1; min-width: 0; }
.summary-item-name { font-size: 13px; font-weight: 600; color: #141718; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.summary-item-variant { font-size: 12px; color: #6C7275; }
.summary-item-qty { font-size: 12px; color: #6C7275; }
.summary-item-price { font-size: 13px; font-weight: 600; white-space: nowrap; }

.summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: 14px; }
.summary-row .label { color: #6C7275; }
.summary-row .value { font-weight: 600; color: #141718; }
.summary-row.total { border-top: 1px solid #E8ECEF; padding-top: 16px; margin-top: 8px; }
.summary-row.total .label { font-size: 16px; font-weight: 600; color: #141718; }
.summary-row.total .value { font-size: 20px; }
.free-ship { color: #38CB89; font-weight: 600; }
.discount-val { color: #38CB89; }

.btn-place-order {
    display: block; width: 100%; padding: 16px; background: #141718; color: #fff; border: none;
    border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 24px;
    font-family: 'Inter', sans-serif; transition: background 0.2s;
}
.btn-place-order:hover { background: #343839; }

@media (max-width: 768px) {
    .checkout-layout { flex-direction: column; }
    .checkout-right { width: 100%; }
    .form-grid { grid-template-columns: 1fr; }
    .page-title { font-size: 28px; }
    .checkout-steps { display: none; }
}
</style>

<div class="container" style="margin-top: 40px;">
    <div class="breadcrumb-nav">
        <a href="index.php">Home</a> &gt; <a href="cart.php">Cart</a> &gt; <span class="current">Checkout</span>
    </div>

    <h1 class="page-title">Check Out</h1>

    <!-- Steps -->
    <div class="checkout-steps">
        <div class="step-item done"><span class="step-num">✓</span> Shopping Cart</div>
        <div class="step-divider"></div>
        <div class="step-item active"><span class="step-num">2</span> Checkout Details</div>
        <div class="step-divider"></div>
        <div class="step-item"><span class="step-num">3</span> Order Complete</div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="checkout-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="checkout.php">
        <input type="hidden" name="action" value="checkout">
        <input type="hidden" name="coupon_code" value="<?= htmlspecialchars($couponCode) ?>">

        <div class="checkout-layout">
            <!-- LEFT: Billing form -->
            <div class="checkout-left">
                <h2 class="form-title">Billing Details</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($prefill['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($prefill['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" name="phone" value="<?= htmlspecialchars($prefill['phone']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>City</label>
                        <input type="text" name="city" value="<?= htmlspecialchars($prefill['city']) ?>">
                    </div>
                    <div class="form-group full">
                        <label>Street Address *</label>
                        <textarea name="address" rows="2" required><?= htmlspecialchars($prefill['address']) ?></textarea>
                    </div>
                    <div class="form-group full">
                        <label>Order Note (optional)</label>
                        <textarea name="note" rows="2" placeholder="Ghi chú giao hàng..."></textarea>
                    </div>
                </div>

                <h2 class="payment-title">Payment Method</h2>
                <div class="payment-options">
                    <label class="payment-option selected" onclick="selectPayment(this)">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <span class="payment-radio"></span>
                        <span class="payment-icon">💵</span>
                        <span class="payment-label">Thanh toán khi nhận hàng (COD)</span>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment_method" value="bank_transfer">
                        <span class="payment-radio"></span>
                        <span class="payment-icon">🏦</span>
                        <span class="payment-label">Chuyển khoản ngân hàng</span>
                    </label>
                    <label class="payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="payment_method" value="momo">
                        <span class="payment-radio"></span>
                        <span class="payment-icon">📱</span>
                        <span class="payment-label">MoMo</span>
                    </label>
                </div>
            </div>

            <!-- RIGHT: Order summary -->
            <div class="checkout-right">
                <div class="checkout-summary">
                    <h3 class="summary-title">Your Order</h3>

                    <?php foreach ($cartItems as $item): ?>
                        <div class="summary-item">
                            <img src="<?= checkoutThumb($item['thumbnail']) ?>" alt="" onerror="this.src='../assets/images/sofa.jpg'">
                            <div class="summary-item-info">
                                <div class="summary-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                                    <div class="summary-item-variant"><?= htmlspecialchars(implode(' / ', array_filter([$item['color'], $item['size']]))) ?></div>
                                <?php endif; ?>
                                <div class="summary-item-qty">× <?= $item['quantity'] ?></div>
                            </div>
                            <div class="summary-item-price"><?= formatVND($item['line_total']) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top: 16px;">
                        <div class="summary-row">
                            <span class="label">Subtotal</span>
                            <span class="value"><?= formatVND($subtotal) ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Shipping</span>
                            <span class="value"><?= $shipping > 0 ? formatVND($shipping) : '<span class="free-ship">FREE</span>' ?></span>
                        </div>
                        <?php if ($couponDiscount > 0): ?>
                            <div class="summary-row">
                                <span class="label">Discount (<?= htmlspecialchars($couponCode) ?>)</span>
                                <span class="value discount-val">- <?= formatVND($couponDiscount) ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span class="label">Total</span>
                            <span class="value"><?= formatVND($total) ?></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-place-order">Place Order</button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function selectPayment(label) {
    document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type="radio"]').checked = true;
}
</script>
