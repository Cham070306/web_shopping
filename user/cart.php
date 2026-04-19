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
/* ── Cart Page ── */
.breadcrumb-nav { font-size: 14px; color: #6C7275; margin-bottom: 40px; }
.breadcrumb-nav a { color: #6C7275; text-decoration: none; }
.breadcrumb-nav a:hover { color: #141718; }
.breadcrumb-nav .current { color: #141718; font-weight: 500; }

.page-title { font-size: 40px; font-weight: 600; text-align: center; margin-bottom: 40px; letter-spacing: -0.5px; }

.cart-layout { display: flex; gap: 40px; padding-bottom: 80px; align-items: flex-start; }
.cart-left { flex: 1; min-width: 0; }
.cart-right { width: 380px; flex-shrink: 0; }

/* Table */
.cart-table { width: 100%; border-collapse: collapse; }
.cart-table thead th {
    text-align: left; font-size: 12px; font-weight: 600; color: #6C7275;
    text-transform: uppercase; letter-spacing: 0.05em;
    padding: 0 0 16px 0; border-bottom: 1px solid #E8ECEF;
}
.cart-table thead th:last-child { text-align: right; }
.cart-table tbody td { padding: 24px 0; border-bottom: 1px solid #E8ECEF; vertical-align: middle; }

.cart-product { display: flex; align-items: center; gap: 16px; }
.cart-product img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; background: #F3F5F7; }
.cart-product-info { display: flex; flex-direction: column; gap: 4px; }
.cart-product-name { font-weight: 600; font-size: 14px; color: #141718; }
.cart-product-variant { font-size: 12px; color: #6C7275; }

.price-original { text-decoration: line-through; color: #6C7275; font-size: 13px; margin-right: 6px; }
.price-current { font-weight: 500; color: #141718; font-size: 14px; }

/* Qty control */
.qty-control { display: inline-flex; align-items: center; border: 1px solid #E8ECEF; border-radius: 8px; overflow: hidden; }
.qty-btn {
    width: 36px; height: 36px; background: #fff; border: none; cursor: pointer;
    font-size: 18px; color: #141718; display: flex; align-items: center; justify-content: center;
    transition: background 0.15s;
}
.qty-btn:hover { background: #F3F5F7; }
.qty-input {
    width: 40px; height: 36px; text-align: center; border: none; border-left: 1px solid #E8ECEF;
    border-right: 1px solid #E8ECEF; font-size: 14px; font-weight: 600; outline: none;
    font-family: 'Inter', sans-serif;
}
.qty-input::-webkit-inner-spin-button { -webkit-appearance: none; }

.td-subtotal { font-weight: 600; font-size: 14px; color: #141718; }

.btn-remove-item {
    background: none; border: none; cursor: pointer; padding: 8px; color: #6C7275;
    transition: color 0.2s; display: flex; align-items: center; justify-content: center;
}
.btn-remove-item:hover { color: #FF5630; }
.td-actions { text-align: right; }

/* Coupon */
.coupon-row { display: flex; gap: 12px; margin-top: 24px; }
.coupon-input {
    flex: 1; padding: 14px 16px; border: 1px solid #E8ECEF; border-radius: 8px;
    font-size: 14px; outline: none; font-family: 'Inter', sans-serif;
}
.coupon-input:focus { border-color: #141718; }
.coupon-input::placeholder { color: #6C7275; }
.btn-coupon {
    padding: 14px 24px; background: #141718; color: #fff; border: none; border-radius: 8px;
    font-weight: 600; font-size: 14px; cursor: pointer; white-space: nowrap; transition: background 0.2s;
}
.btn-coupon:hover { background: #343839; }
.coupon-msg { margin-top: 8px; font-size: 13px; }
.coupon-msg.success { color: #38CB89; }
.coupon-msg.error { color: #FF5630; }

/* Summary */
.order-summary {
    background: #F3F5F7; border-radius: 12px; padding: 32px 24px;
    position: sticky; top: 100px;
}
.summary-title { font-size: 20px; font-weight: 600; margin-bottom: 24px; }
.summary-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; }
.summary-row.border-top { border-top: 1px solid #E8ECEF; margin-top: 8px; padding-top: 20px; }
.summary-label { font-size: 14px; color: #6C7275; }
.summary-value { font-size: 14px; font-weight: 600; color: #141718; }
.summary-total .summary-label { font-size: 16px; color: #141718; font-weight: 600; }
.summary-total .summary-value { font-size: 20px; }
.discount-row { display: none; }
.discount-row.active { display: flex; }
.summary-discount { color: #38CB89; }

.btn-checkout {
    display: block; width: 100%; padding: 16px; background: #141718; color: #fff; border: none;
    border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; text-align: center;
    text-decoration: none; margin-top: 24px; transition: background 0.2s;
    font-family: 'Inter', sans-serif;
}
.btn-checkout:hover { background: #343839; }

/* Free shipping badge */
.free-ship { color: #38CB89; font-weight: 600; }

/* Empty state */
.cart-empty {
    text-align: center; padding: 80px 20px;
}
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

/* Responsive */
@media (max-width: 768px) {
    .cart-layout { flex-direction: column; }
    .cart-right { width: 100%; }
    .page-title { font-size: 28px; margin-bottom: 24px; }
    .cart-table thead { display: none; }
    .cart-table tbody td { display: block; padding: 8px 0; border: none; }
    .cart-table tbody tr { border-bottom: 1px solid #E8ECEF; padding: 16px 0; display: block; }
    .td-actions { text-align: left; }
}
</style>

<div class="container" style="margin-top: 40px;">
    <div class="breadcrumb-nav">
        <a href="index.php">Home</a> &gt; <span class="current">Cart</span>
    </div>

    <h1 class="page-title">Cart</h1>

    <?php if (empty($cartItems)): ?>
        <div class="cart-empty">
            <div class="cart-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <h2>Giỏ hàng trống</h2>
            <p>Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
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
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
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
                                        <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="cart-product-name" style="text-decoration:none;color:inherit;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </a>
                                        <?php if (!empty($item['color']) || !empty($item['size'])): ?>
                                            <span class="cart-product-variant">
                                                <?= htmlspecialchars(implode(' / ', array_filter([$item['color'], $item['size']]))) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($hasSale): ?>
                                    <span class="price-original"><?= formatVND($item['price']) ?></span>
                                <?php endif; ?>
                                <span class="price-current"><?= formatVND($item['unit_price']) ?></span>
                            </td>
                            <td>
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="changeQty(<?= $item['id'] ?>, -1)">−</button>
                                    <input type="number" class="qty-input" id="qty-<?= $item['id'] ?>"
                                           value="<?= $item['quantity'] ?>" min="1" max="<?= $item['variant_stock'] ?? $item['stock'] ?>"
                                           onchange="updateQty(<?= $item['id'] ?>, this.value)">
                                    <button class="qty-btn" onclick="changeQty(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                            </td>
                            <td class="td-subtotal" id="subtotal-<?= $item['id'] ?>">
                                <?= formatVND($item['line_total']) ?>
                            </td>
                            <td class="td-actions">
                                <button class="btn-remove-item" onclick="removeItem(<?= $item['id'] ?>, this)" title="Xóa">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Coupon -->
                <div class="coupon-row">
                    <input type="text" id="coupon-input" class="coupon-input" placeholder="Nhập mã giảm giá">
                    <button class="btn-coupon" onclick="applyCoupon()">Apply</button>
                </div>
                <div id="coupon-msg" class="coupon-msg"></div>
            </div>

            <!-- RIGHT: Order Summary -->
            <div class="cart-right">
                <div class="order-summary">
                    <h3 class="summary-title">Order Summary</h3>

                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value" id="summary-subtotal"><?= formatVND($subtotal) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Shipping</span>
                        <span class="summary-value" id="summary-shipping">
                            <?= $shipping > 0 ? formatVND($shipping) : '<span class="free-ship">FREE</span>' ?>
                        </span>
                    </div>
                    <div class="summary-row discount-row" id="discount-row">
                        <span class="summary-label">Discount</span>
                        <span class="summary-value summary-discount" id="summary-discount">- 0 đ</span>
                    </div>
                    <div class="summary-row border-top summary-total">
                        <span class="summary-label">Total</span>
                        <span class="summary-value" id="summary-total"><?= formatVND($total) ?></span>
                    </div>
                    <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
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

function recalcSummary() {
    let subtotal = 0;
    document.querySelectorAll('.cart-row').forEach(row => {
        const unitPrice = parseFloat(row.dataset.unitPrice);
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        subtotal += unitPrice * qty;
    });
    const shipping = subtotal >= 500000 ? 0 : 30000;
    const total = subtotal - currentDiscount + shipping;

    document.getElementById('summary-subtotal').textContent = fmt(subtotal);
    document.getElementById('summary-shipping').innerHTML = shipping > 0 ? fmt(shipping) : '<span class="free-ship">FREE</span>';
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
                            <h2>Giỏ hàng trống</h2>
                            <p>Bạn chưa thêm sản phẩm nào vào giỏ hàng.</p>
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
    if (!code) { msgEl.className = 'coupon-msg error'; msgEl.textContent = 'Vui lòng nhập mã giảm giá'; return; }

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
            msgEl.textContent = 'Áp dụng mã "' + data.coupon_code + '" thành công!';
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
