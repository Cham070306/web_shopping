<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../models/Order.php';
require_once '../models/OrderDetail.php';

$order_code = $_SESSION['last_order_code'] ?? null;
if (!$order_code) {
    header("Location: index.php");
    exit;
}

$orderModel  = new Order($conn);
$detailModel = new OrderDetail($conn);
$order = $orderModel->getOrderByCode($order_code);

if (!$order) {
    header("Location: index.php");
    exit;
}

$items = $detailModel->getItemsByOrderId($order['id']);

// Xóa session để không reload lại được
unset($_SESSION['last_order_code'], $_SESSION['last_order_id']);

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int)$price, 0, ',', '.') . ' đ';
    }
}

function completeThumb($thumb) {
    if (!$thumb) return '../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../assets/images/' . htmlspecialchars($thumb);
}

$paymentLabels = [
    'cod'           => 'Cash on Delivery (COD)',
    'bank_transfer' => 'Bank Transfer',
    'momo'          => 'MoMo Wallet'
];
$statusLabels = [
    'pending'   => ['Pending', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Confirmed', '#2196F3', '#E3F2FD'],
    'shipping'  => ['Shipping', '#9C27B0', '#F3E5F5'],
    'delivered' => ['Delivered', '#38CB89', '#E8F9EE'],
    'cancelled' => ['Cancelled', '#FF5630', '#FFF0F0'],
];
$orderStatus = $statusLabels[$order['status']] ?? ['N/A', '#6C7275', '#F3F5F7'];
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
.complete-page { max-width: 640px; margin: 0 auto; padding: 60px 20px 100px; text-align: center; }

.check-icon {
    width: 80px; height: 80px; border-radius: 50%; background: #38CB89;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;
    animation: scaleIn 0.4s ease;
}
@keyframes scaleIn { from { transform: scale(0); } to { transform: scale(1); } }
.check-icon svg { width: 40px; height: 40px; color: #fff; }

.complete-heading { font-size: 28px; font-weight: 600; margin-bottom: 8px; color: #141718; }
.complete-sub { font-size: 14px; color: #6C7275; margin-bottom: 40px; }

/* Order card */
.order-card {
    background: #fff; border: 1px solid #E8ECEF; border-radius: 12px;
    padding: 28px 24px; text-align: left; margin-bottom: 24px;
}
.order-card-title { font-size: 16px; font-weight: 600; margin-bottom: 20px; color: #141718; }

.order-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.order-info-item {}
.order-info-label { font-size: 12px; color: #6C7275; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
.order-info-value { font-size: 14px; font-weight: 600; color: #141718; }
.order-code-value {
    font-family: 'Courier New', monospace; background: #F3F5F7; padding: 6px 12px;
    border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; font-size: 14px;
}
.copy-btn {
    background: none; border: none; cursor: pointer; color: #6C7275; padding: 2px;
    transition: color 0.2s;
}
.copy-btn:hover { color: #141718; }

.status-badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
}

.divider { height: 1px; background: #E8ECEF; margin: 20px 0; }

/* Items list */
.item-row { display: flex; align-items: center; gap: 14px; padding: 12px 0; border-bottom: 1px solid #F3F5F7; }
.item-row:last-child { border-bottom: none; }
.item-img { width: 56px; height: 56px; object-fit: cover; border-radius: 6px; background: #F3F5F7; }
.item-info { flex: 1; min-width: 0; }
.item-name { font-size: 14px; font-weight: 600; color: #141718; }
.item-variant { font-size: 12px; color: #6C7275; }
.item-calc { font-size: 13px; color: #6C7275; white-space: nowrap; }
.item-total { font-size: 14px; font-weight: 600; color: #141718; white-space: nowrap; }

/* Totals */
.total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
.total-row .label { color: #6C7275; }
.total-row .value { font-weight: 600; color: #141718; }
.total-row.grand { border-top: 1px solid #E8ECEF; padding-top: 16px; margin-top: 8px; }
.total-row.grand .label { font-size: 16px; font-weight: 600; color: #141718; }
.total-row.grand .value { font-size: 20px; }
.free-ship { color: #38CB89; font-weight: 600; }
.discount-val { color: #38CB89; }

/* Buttons */
.complete-actions { display: flex; gap: 16px; justify-content: center; margin-top: 32px; }
.btn-secondary {
    padding: 14px 28px; border: 1.5px solid #141718; background: #fff; color: #141718;
    border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none; transition: all 0.2s;
}
.btn-secondary:hover { background: #F3F5F7; }
.btn-primary {
    padding: 14px 28px; background: #141718; color: #fff; border: none; border-radius: 8px;
    font-weight: 600; font-size: 14px; text-decoration: none; transition: background 0.2s;
}
.btn-primary:hover { background: #343839; }

@media (max-width: 768px) {
    .order-info-grid { grid-template-columns: 1fr; }
    .complete-actions { flex-direction: column; }
    .complete-heading { font-size: 24px; }
}
</style>

<div class="complete-page">
    <!-- Success icon -->
    <div class="check-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
    </div>

    <h1 class="complete-heading">Order Successful!</h1>
    <p class="complete-sub">Thank you for your purchase. We will contact you shortly.</p>

    <!-- Order Info -->
    <div class="order-card">
        <h3 class="order-card-title">Order Information</h3>
        <div class="order-info-grid">
            <div class="order-info-item">
                <div class="order-info-label">Order Code</div>
                <div class="order-info-value">
                    <span class="order-code-value">
                        <?= htmlspecialchars($order['order_code']) ?>
                        <button class="copy-btn" onclick="copyCode()" title="Copy">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </span>
                </div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Order Date</div>
                <div class="order-info-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Payment Method</div>
                <div class="order-info-value"><?= $paymentLabels[$order['payment_method']] ?? $order['payment_method'] ?></div>
            </div>
            <div class="order-info-item">
                <div class="order-info-label">Status</div>
                <div class="order-info-value">
                    <span class="status-badge" style="background: <?= $orderStatus[2] ?>; color: <?= $orderStatus[1] ?>;">
                        <?= $orderStatus[0] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hướng dẫn Thanh Toán QR Code (Nếu dùng Bank/MoMo) -->
    <?php if (in_array($order['payment_method'], ['bank_transfer', 'momo'])): ?>
    <div class="order-card" style="text-align: center;">
        <h3 class="order-card-title" style="margin-bottom: 8px;">Payment QR Code</h3>
        <p style="font-size:14px; color:#6C7275; margin-bottom: 24px;">Please use your banking app or MoMo Wallet to scan this QR.</p>
        
        <div style="padding: 16px; background:#fff; border:2px dashed #E8ECEF; border-radius:12px; display:inline-block; margin-bottom: 20px;">
            <!-- Dummy QR Demo link generated from order code -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=PAYMENT_DEMO_<?= htmlspecialchars($order['order_code']) ?>_AMOUNT_<?= $order['total'] ?>" alt="Payment QR" style="width:200px; height:200px; display:block;">
        </div>

        <div style="font-size: 14px; margin-bottom: 8px;">
            <span style="color:#6C7275;">Transfer Message:</span> 
            <strong style="color:#141718; font-family: monospace; background:#F3F5F7; padding: 2px 6px; border-radius: 4px;"><?= htmlspecialchars($order['order_code']) ?></strong>
        </div>
        <div style="font-size: 14px; margin-bottom: 20px;">
            <span style="color:#6C7275;">Amount:</span> 
            <strong style="color:#38CB89; font-size: 18px;"><?= formatVND($order['total']) ?></strong>
        </div>

        <div style="background:#FFF7ED; padding:12px 16px; border-radius:8px; font-size:13px; color:#EA580C; max-width: 400px; margin: 0 auto; text-align: left; line-height: 1.5;">
            <strong>Integration Demo:</strong> This simulated QR is for demonstration only. In a real environment, dynamic APIs from VietQR or Momo Sandbox would be integrated.
        </div>
    </div>
    <?php endif; ?>

    <!-- Items -->
    <div class="order-card">
        <h3 class="order-card-title">Ordered Products</h3>
        <?php foreach ($items as $it): ?>
            <div class="item-row">
                <img src="<?= completeThumb($it['thumbnail']) ?>" alt="" class="item-img" onerror="this.src='../assets/images/sofa.jpg'">
                <div class="item-info">
                    <div class="item-name"><?= htmlspecialchars($it['product_name']) ?></div>
                    <?php if ($it['variant']): ?>
                        <div class="item-variant"><?= htmlspecialchars($it['variant']) ?></div>
                    <?php endif; ?>
                </div>
                <div class="item-calc"><?= $it['quantity'] ?> × <?= formatVND($it['price']) ?></div>
                <div class="item-total"><?= formatVND($it['subtotal']) ?></div>
            </div>
        <?php endforeach; ?>

        <div class="divider"></div>

        <div class="total-row">
            <span class="label">Subtotal</span>
            <span class="value"><?= formatVND($order['subtotal']) ?></span>
        </div>
        <div class="total-row">
            <span class="label">Shipping</span>
            <span class="value"><?= $order['shipping_fee'] > 0 ? formatVND($order['shipping_fee']) : '<span class="free-ship">FREE</span>' ?></span>
        </div>
        <?php if ($order['discount'] > 0): ?>
            <div class="total-row">
                <span class="label">Discount<?= $order['coupon_code'] ? ' (' . htmlspecialchars($order['coupon_code']) . ')' : '' ?></span>
                <span class="value discount-val">- <?= formatVND($order['discount']) ?></span>
            </div>
        <?php endif; ?>
        <div class="total-row grand">
            <span class="label">Total</span>
            <span class="value"><?= formatVND($order['total']) ?></span>
        </div>
    </div>

    <!-- Actions -->
    <div class="complete-actions">
        <a href="my_orders.php" class="btn-secondary">View Orders</a>
        <a href="shop.php" class="btn-primary">Continue Shopping</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<script>
function copyCode() {
    const code = '<?= $order['order_code'] ?>';
    navigator.clipboard.writeText(code).then(() => {
        const btn = document.querySelector('.copy-btn');
        btn.innerHTML = '<i class="fa-solid fa-check" style="color:#38CB89"></i>';
        setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 2000);
    });
}
</script>
