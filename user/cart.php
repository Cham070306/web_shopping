<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Order.php';

$orderModel = new Order($conn);
$user_id    = $_SESSION['user']['id'] ?? null;
$session_id = session_id();
$cartItems  = $orderModel->getCartItems($user_id, $session_id);

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
$total    = $subtotal + $shipping;

// Resolve thumbnail
function cartThumb($thumb) {
    if (!$thumb) return '../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../assets/images/' . htmlspecialchars($thumb);
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
/* ── Cart Page — 3legant Figma ── */

.page-title { font-size: 44px; font-weight: 500; text-align: center; margin-bottom: 32px; letter-spacing: -0.5px; font-family: 'Poppins', sans-serif; }

/* Steps indicator */
.cart-steps { display: flex; justify-content: center; align-items: center; gap: 0; margin-bottom: 48px; }
.cart-step { display: flex; align-items: center; gap: 12px; font-size: 14px; color: #B1B5B8; position: relative; }
.cart-step.active { color: #141718; }
.cart-step.active .step-circle { background: #141718; color: #fff; border-color: #141718; }
.cart-step.done { color: #38CB89; }
.cart-step.done .step-circle { background: #38CB89; border-color: #38CB89; color: #fff; }
.step-circle {
    width: 36px; height: 36px; border-radius: 50%; border: 1.5px solid #B1B5B8;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;
    flex-shrink: 0; background: #fff; color: #B1B5B8; transition: all 0.3s;
}
.step-label { font-weight: 500; white-space: nowrap; }
.step-line { width: 80px; height: 2px; background: #E8ECEF; margin: 0 24px; flex-shrink: 0; }
.cart-step.active ~ .step-line { background: #E8ECEF; }

/* Layout */
.cart-layout { display: flex; gap: 80px; padding-bottom: 80px; align-items: flex-start; justify-content: space-between; }
.cart-left { flex: 1; min-width: 0; max-width: 640px; }
.cart-right { width: 400px; flex-shrink: 0; margin-left: auto; }

/* Table */
.cart-table { width: 100%; border-collapse: collapse; }
.cart-table thead th {
    text-align: left; font-size: 12px; font-weight: 600; color: #6C7275;
    text-transform: uppercase; letter-spacing: 0.05em;
    padding: 0 0 16px 0; border-bottom: 2px solid #141718;
}
.cart-table thead th.th-qty { text-align: center; }
.cart-table thead th.th-price { text-align: center; }
.cart-table thead th.th-subtotal { text-align: right; }
.cart-table tbody td { padding: 24px 0; border-bottom: 1px solid #E8ECEF; vertical-align: middle; }

/* Product cell */
.cart-product { display: flex; align-items: center; gap: 16px; }
.cart-product img { width: 80px; height: 96px; object-fit: cover; border-radius: 4px; background: #F3F5F7; }
.cart-product-info { display: flex; flex-direction: column; gap: 4px; }
.cart-product-name { font-weight: 600; font-size: 14px; color: #141718; text-decoration: none; }
.cart-product-name:hover { text-decoration: underline; }
.cart-product-variant { font-size: 12px; color: #6C7275; }
.btn-remove-link {
    display: inline-flex; align-items: center; gap: 4px; margin-top: 8px;
    font-size: 13px; color: #6C7275; background: none; border: none; cursor: pointer;
    padding: 0; transition: color 0.2s; font-family: 'Inter', sans-serif;
}
.btn-remove-link:hover { color: #FF5630; }
.btn-remove-link svg { width: 14px; height: 14px; }

/* Price */
.td-price { text-align: center; }
.price-original { text-decoration: line-through; color: #6C7275; font-size: 13px; margin-right: 6px; }
.price-current { font-weight: 500; color: #141718; font-size: 14px; }

/* Qty control */
.td-qty { text-align: center; }
.qty-control { display: inline-flex; align-items: center; border: 1px solid #CBCFD2; border-radius: 8px; overflow: hidden; }
.qty-btn {
    width: 32px; height: 32px; background: #fff; border: none; cursor: pointer;
    font-size: 16px; color: #141718; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.qty-btn:hover { background: #F3F5F7; }
.qty-input {
    width: 32px; height: 32px; text-align: center; border: none; border-left: 1px solid #CBCFD2;
    border-right: 1px solid #CBCFD2; font-size: 13px; font-weight: 600; outline: none;
    font-family: 'Inter', sans-serif; background: #fff;
}
.qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }

/* Subtotal */
.td-subtotal { font-weight: 600; font-size: 14px; color: #141718; text-align: right; }

/* ── Coupon ── */
.coupon-section { margin-top: 40px; }
.coupon-heading { font-size: 18px; font-weight: 600; margin-bottom: 6px; color: #141718; }
.coupon-sub { font-size: 13px; color: #6C7275; margin-bottom: 16px; }
.coupon-row {
    display: flex; align-items: center; border: 1px solid #CBCFD2; border-radius: 8px;
    overflow: hidden; max-width: 420px;
}
.coupon-icon { padding: 0 12px 0 16px; color: #6C7275; display: flex; align-items: center; }
.coupon-input {
    flex: 1; padding: 14px 8px; border: none; font-size: 14px; outline: none;
    font-family: 'Inter', sans-serif; background: transparent;
}
.coupon-input::placeholder { color: #6C7275; }
.btn-coupon {
    padding: 14px 24px; background: transparent; color: #141718; border: none; border-left: 1px solid #CBCFD2;
    font-weight: 600; font-size: 14px; cursor: pointer; white-space: nowrap; transition: background 0.2s;
}
.btn-coupon:hover { background: #F3F5F7; }
.coupon-msg { margin-top: 8px; font-size: 13px; }
.coupon-msg.success { color: #38CB89; }
.coupon-msg.error { color: #FF5630; }

/* ── Cart Summary (Right) ── */
.cart-summary-card {
    border: 1px solid #E8ECEF; border-radius: 8px; padding: 28px 24px;
    position: sticky; top: 100px;
}
.summary-title { font-size: 20px; font-weight: 600; margin-bottom: 20px; color: #141718; }

/* Shipping options */
.shipping-option {
    display: flex; align-items: center; justify-content: space-between; padding: 14px 16px;
    border: 1px solid #E8ECEF; border-radius: 8px; margin-bottom: 10px; cursor: pointer;
    transition: border-color 0.2s;
}
.shipping-option:hover { border-color: #141718; }
.shipping-option.selected { border-color: #141718; }
.shipping-option input[type="radio"] { display: none; }
.ship-left { display: flex; align-items: center; gap: 12px; }
.ship-radio {
    width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid #CBCFD2;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.shipping-option.selected .ship-radio { border-color: #141718; }
.shipping-option.selected .ship-radio::after {
    content: ''; width: 10px; height: 10px; background: #141718; border-radius: 50%; display: block;
}
.ship-label { font-size: 14px; font-weight: 500; color: #141718; }
.ship-price { font-size: 14px; font-weight: 600; color: #141718; white-space: nowrap; }

/* Summary rows */
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
.summary-row.border-top { border-top: 1px solid #E8ECEF; margin-top: 8px; padding-top: 16px; }
.summary-label { font-size: 14px; color: #6C7275; }
.summary-value { font-size: 14px; font-weight: 600; color: #141718; }
.summary-total .summary-label { font-size: 16px; color: #141718; font-weight: 600; }
.summary-total .summary-value { font-size: 20px; }
.discount-row { display: none; }
.discount-row.active { display: flex; }
.summary-discount { color: #38CB89; }
.free-ship { color: #38CB89; font-weight: 600; }

.btn-checkout {
    display: block; width: 100%; padding: 16px; background: #141718; color: #fff; border: none;
    border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center;
    text-decoration: none; margin-top: 20px; transition: background 0.2s;
    font-family: 'Inter', sans-serif;
}
.btn-checkout:hover { background: #343839; }

/* Empty state */
.cart-empty { text-align: center; padding: 80px 20px; }
.cart-empty-icon { font-size: 64px; color: #E8ECEF; margin-bottom: 24px; }
.cart-empty h2 { font-size: 24px; font-weight: 600; margin-bottom: 12px; }
.cart-empty p { color: #6C7275; margin-bottom: 32px; }
.btn-shop {
    display: inline-block; padding: 14px 40px; background: #141718; color: #fff;
    text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;
    transition: background 0.2s;
}
.btn-shop:hover { background: #343839; }

/* Row animation */
.cart-row { transition: opacity 0.3s ease, transform 0.3s ease; }
.cart-row.removing { opacity: 0; transform: translateX(-20px); }

/* ── Responsive ── */
@media (max-width: 992px) {
    .cart-layout { gap: 24px; }
    .cart-left { max-width: none; }
    .cart-right { width: 320px; }
    .step-line { width: 40px; margin: 0 12px; }
}

@media (max-width: 768px) {
    .cart-layout { flex-direction: column; }
    .cart-right { width: 100%; }
    .page-title { font-size: 28px; margin-bottom: 20px; }

    /* Steps: simplified */
    .cart-steps { justify-content: space-between; padding: 0 10px; margin-bottom: 32px; }
    .step-line { flex: 1; min-width: 0; width: auto; margin: 0 8px; }
    .step-label { display: none; }
    .cart-step.active .step-label { display: inline; font-size: 13px; }

    /* Table → card layout */
    .cart-table thead { display: none; }
    .cart-table tbody tr {
        display: grid; grid-template-columns: 80px 1fr auto; grid-template-rows: auto auto;
        gap: 0 14px; padding: 16px 0; border-bottom: 1px solid #E8ECEF; align-items: center;
    }
    .cart-table tbody td { padding: 0; border: none; }

    /* Product image */
    .cart-table tbody td:nth-child(1) { grid-row: 1 / 3; grid-column: 1; }
    .cart-product { flex-direction: column; gap: 0; }
    .cart-product img { width: 80px; height: 80px; border-radius: 6px; }
    .cart-product-info { display: none; }

    /* Name + variant inline */
    .cart-table tbody td:nth-child(1)::after { display: none; }

    /* Mobile: card row with name in col 2 */
    .mob-name { display: none; }

    /* Price cell → top right */
    .cart-table tbody td:nth-child(2) { grid-row: 1; grid-column: 2; text-align: left; }

    /* Qty → bottom left of col 2 */
    .cart-table tbody td:nth-child(3) { grid-row: 2; grid-column: 2; text-align: left; margin-top: 8px; }

    /* Subtotal → top right */
    .cart-table tbody td:nth-child(4) { grid-row: 1; grid-column: 3; text-align: right; }

    /* Remove → bottom right */
    .cart-table tbody td:nth-child(5) { grid-row: 2; grid-column: 3; text-align: right; }

    .coupon-row { max-width: 100%; }
    .cart-summary-card { position: static; }
}

@media (max-width: 480px) {
    .page-title { font-size: 24px; }
    .step-circle { width: 30px; height: 30px; font-size: 12px; }
}
</style>

<div class="container" style="margin-top: 40px;">
    <h1 class="page-title">Cart</h1>

    <!-- Steps Indicator -->
    <div class="cart-steps">
        <div class="cart-step active">
            <span class="step-circle">1</span>
            <span class="step-label">Shopping cart</span>
        </div>
        <div class="step-line"></div>
        <div class="cart-step">
            <span class="step-circle">2</span>
            <span class="step-label">Checkout details</span>
        </div>
        <div class="step-line"></div>
        <div class="cart-step">
            <span class="step-circle">3</span>
            <span class="step-label">Order complete</span>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <div class="cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <h2>Your Cart is Empty</h2>
            <p>You haven't added any products to your cart yet.</p>
            <a href="shop.php" class="btn-shop">Shop Now</a>
        </div>
    <?php else: ?>
        <div class="cart-layout" id="cart-container">
            <!-- LEFT: Cart Table -->
            <div class="cart-left">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="th-qty">Quantity</th>
                            <th class="th-price">Price</th>
                            <th class="th-subtotal">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cart-body">
                        <?php foreach ($cartItems as $item):
                            $hasSale = $item['sale_price'] && $item['price'] > $item['sale_price'];
                            $img = cartThumb($item['thumbnail']);
                        ?>
                        <tr class="cart-row" data-cart-id="<?= $item['id'] ?>" data-unit-price="<?= $item['unit_price'] ?>">
                            <td>
                                <div class="cart-product">
                                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='../assets/images/sofa.jpg'">
                                    <div class="cart-product-info">
                                        <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="cart-product-name">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                        <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                                            <span class="cart-product-variant">
                                                <?php if (!empty($item['color'])): ?>Color: <?= htmlspecialchars($item['color']) ?><?php endif; ?>
                                                <?php if (!empty($item['size'])): ?><?= !empty($item['color']) ? ', ' : '' ?>Size: <?= htmlspecialchars($item['size']) ?><?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                        <button class="btn-remove-link" onclick="removeItem(<?= $item['id'] ?>, this)" title="Remove">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td class="td-qty">
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="changeQty(<?= $item['id'] ?>, -1)">−</button>
                                    <input type="number" class="qty-input" id="qty-<?= $item['id'] ?>"
                                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['variant_stock'] ?? $item['stock'] ?>"
                                           onchange="updateQty(<?= $item['id'] ?>, this.value)">
                                    <button class="qty-btn" onclick="changeQty(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                            </td>
                            <td class="td-price">
                                <?php if ($hasSale): ?>
                                    <span class="price-original"><?= formatVND($item['price']) ?></span>
                                <?php endif; ?>
                                <span class="price-current"><?= formatVND($item['unit_price']) ?></span>
                            </td>
                            <td class="td-subtotal" id="subtotal-<?= $item['id'] ?>">
                                <?= formatVND($item['line_total']) ?>
                            </td>
                            <td class="td-actions" style="display:none;"></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Coupon -->
                <div class="coupon-section">
                    <h3 class="coupon-heading">Have a coupon?</h3>
                    <p class="coupon-sub">Add your code for an instant cart discount</p>
                    <div class="coupon-row">
                        <span class="coupon-icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="6" width="20" height="12" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </span>
                        <input type="text" id="coupon-input" class="coupon-input" placeholder="Coupon Code">
                        <button class="btn-coupon" onclick="applyCoupon()">Apply</button>
                    </div>
                    <div id="coupon-msg" class="coupon-msg"></div>
                </div>
            </div>

            <!-- RIGHT: Cart Summary -->
            <div class="cart-right">
                <div class="cart-summary-card">
                    <h3 class="summary-title">Cart summary</h3>

                    <!-- Shipping options -->
                    <label class="shipping-option selected" onclick="selectShipping(this, 0)">
                        <input type="radio" name="shipping_type" value="free" checked>
                        <div class="ship-left">
                            <span class="ship-radio"></span>
                            <span class="ship-label">Free shipping</span>
                        </div>
                        <span class="ship-price"><?= formatVND(0) ?></span>
                    </label>
                    <label class="shipping-option" onclick="selectShipping(this, 30000)">
                        <input type="radio" name="shipping_type" value="express">
                        <div class="ship-left">
                            <span class="ship-radio"></span>
                            <span class="ship-label">Express shipping</span>
                        </div>
                        <span class="ship-price">+<?= formatVND(30000) ?></span>
                    </label>

                    <div style="margin-top: 16px;">
                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value" id="summary-subtotal"><?= formatVND($subtotal) ?></span>
                        </div>
                        <div class="summary-row discount-row" id="discount-row">
                            <span class="summary-label">Discount</span>
                            <span class="summary-value summary-discount" id="summary-discount">- 0 đ</span>
                        </div>
                        <div class="summary-row border-top summary-total">
                            <span class="summary-label">Total</span>
                            <span class="summary-value" id="summary-total"><?= formatVND($total) ?></span>
                        </div>
                    </div>

                    <a href="checkout.php" class="btn-checkout">Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

<script>
const CART_API = '../controllers/CartController.php';
let currentDiscount = 0;

function fmt(n) {
    return Number(n).toLocaleString('vi-VN') + ' đ';
}

function updateCartBadge(count) {
    const badges = document.querySelectorAll('#cart-count');
    badges.forEach(b => b.textContent = count);
}

let currentShipping = 0;

function selectShipping(el, price) {
    document.querySelectorAll('.shipping-option').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
    currentShipping = price;
    recalcSummary();
}

function recalcSummary() {
    let subtotal = 0;
    document.querySelectorAll('.cart-row').forEach(row => {
        const unitPrice = parseFloat(row.dataset.unitPrice);
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        subtotal += unitPrice * qty;
    });
    const total = subtotal - currentDiscount + currentShipping;

    document.getElementById('summary-subtotal').textContent = fmt(subtotal);
    document.getElementById('summary-total').textContent = fmt(Math.max(0, total));
}

function changeQty(cartId, delta) {
    const input = document.getElementById('qty-' + cartId);
    const newVal = Math.max(1, parseInt(input.value) + delta);
    input.value = newVal;
    updateQty(cartId, newVal);
}

async function updateQty(cartId, qty) {
    qty = Math.max(1, parseInt(qty));
    const input = document.getElementById('qty-' + cartId);
    input.value = qty;

    try {
        const res = await fetch(CART_API + '?action=update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_id: cartId, quantity: qty })
        });
        const data = await res.json();

        if (data.success) {
            document.getElementById('subtotal-' + cartId).textContent = data.item_subtotal;
            updateCartBadge(data.cart_count);
            recalcSummary();
        } else {
            alert(data.message);
            if (data.max_stock) input.value = data.max_stock;
        }
    } catch (e) { console.error('Update error:', e); }
}

async function removeItem(cartId, btn) {
    const row = btn.closest('.cart-row');
    row.classList.add('removing');

    try {
        const res = await fetch(CART_API + '?action=remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ cart_id: cartId })
        });
        const data = await res.json();

        if (data.success) {
            setTimeout(() => {
                row.remove();
                updateCartBadge(data.cart_count);
                recalcSummary();

                // Empty state
                if (document.querySelectorAll('.cart-row').length === 0) {
                    document.getElementById('cart-container').innerHTML = `
                        <div class="cart-empty" style="width:100%">
                            <div class="cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                            <h2>Your Cart is Empty</h2>
                            <p>You haven't added any products to your cart yet.</p>
                            <a href="shop.php" class="btn-shop">Shop Now</a>
                        </div>`;
                }
            }, 300);
        } else {
            row.classList.remove('removing');
        }
    } catch (e) {
        row.classList.remove('removing');
        console.error('Remove error:', e);
    }
}

async function applyCoupon() {
    const code = document.getElementById('coupon-input').value.trim();
    const msgEl = document.getElementById('coupon-msg');
    if (!code) { msgEl.className = 'coupon-msg error'; msgEl.textContent = 'Please enter a coupon code'; return; }

    // Lấy subtotal hiện tại
    let subtotal = 0;
    document.querySelectorAll('.cart-row').forEach(row => {
        const unitPrice = parseFloat(row.dataset.unitPrice);
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        subtotal += unitPrice * qty;
    });

    try {
        const res = await fetch(CART_API + '?action=applyCoupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ coupon_code: code, subtotal: subtotal })
        });
        const data = await res.json();

        if (data.success) {
            currentDiscount = data.discount;
            document.getElementById('summary-discount').textContent = data.discount_text;
            document.getElementById('discount-row').classList.add('active');
            msgEl.className = 'coupon-msg success';
            msgEl.textContent = 'Coupon "' + data.coupon_code + '" applied successfully!';
            recalcSummary();
        } else {
            currentDiscount = 0;
            document.getElementById('discount-row').classList.remove('active');
            msgEl.className = 'coupon-msg error';
            msgEl.textContent = data.message;
            recalcSummary();
        }
    } catch (e) { console.error('Coupon error:', e); }
}
</script>
