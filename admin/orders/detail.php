<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/config.php";
require_once "../../config/database.php";
require_once "../../models/Order.php";
require_once "../../models/OrderDetail.php";

$id = (int)($_GET['id'] ?? 0);
$orderModel  = new Order($conn);
$detailModel = new OrderDetail($conn);

$order = $orderModel->getOrderById($id);
if (!$order) {
    header("Location: index.php");
    exit;
}
$items = $detailModel->getItemsByOrderId($id);

$currentPage = 'orders';
$pageTitle   = 'Chi tiết đơn hàng';
$breadcrumb  = 'Sales / Orders / #' . $order['order_code'];
$base_path   = '../';

include '../layouts/admin_header.php';

$statusLabels = [
    'pending'   => ['Chờ xử lý', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Xác nhận', '#2196F3', '#E3F2FD'],
    'shipping'  => ['Đang giao', '#9C27B0', '#F3E5F5'],
    'delivered' => ['Đã giao', '#38CB89', '#E8F9EE'],
    'cancelled' => ['Đã hủy', '#FF5630', '#FFF0F0'],
];

$paymentStatusLabels = [
    'pending'  => ['Chưa thanh toán', '#FFAB00', '#FFF7ED'],
    'paid'     => ['Đã thanh toán',   '#38CB89', '#E8F9EE'],
    'failed'   => ['Thất bại',        '#FF5630', '#FFF0F0'],
    'refunded' => ['Đã hoàn tiền',    '#2196F3', '#E3F2FD'],
];

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int)$price, 0, ',', '.') . ' đ';
    }
}
function orderThumb($thumb) {
    if (!$thumb) return '../../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../../assets/images/' . htmlspecialchars($thumb);
}
?>

<style>
.detail-layout { display: flex; gap: 24px; align-items: flex-start; margin-top: 20px; }
.detail-left { flex: 0 0 60%; display: flex; flex-direction: column; gap: 24px; }
.detail-right { flex: 0 0 calc(40% - 24px); display: flex; flex-direction: column; gap: 24px; }

.dt-card { background: #fff; border: 1px solid #E8ECEF; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.dt-title { font-size: 16px; font-weight: 600; color: #141718; margin-bottom: 20px; }

/* Table items */
.dt-table { width: 100%; border-collapse: collapse; }
.dt-table th { text-align: left; font-size: 12px; color: #6C7275; text-transform: uppercase; padding-bottom: 12px; border-bottom: 1px solid #E8ECEF; font-weight: 600; }
.dt-table td { padding: 16px 0; border-bottom: 1px solid #F3F5F7; vertical-align: middle; }
.dt-product { display: flex; gap: 12px; align-items: center; }
.dt-product img { width: 48px; height: 48px; border-radius: 6px; object-fit: cover; border: 1px solid #E8ECEF; }
.dt-product-name { font-weight: 600; font-size: 14px; color: #141718; }
.dt-product-var { font-size: 12px; color: #6C7275; }

.dt-totals { margin-top: 24px; display: flex; flex-direction: column; gap: 12px; font-size: 14px; }
.dt-row { display: flex; justify-content: space-between; }
.dt-row .val { font-weight: 600; color: #141718; }
.dt-row.grand { border-top: 1px solid #E8ECEF; padding-top: 16px; margin-top: 8px; font-size: 18px; }
.dt-row.grand .val { font-size: 24px; }
.discount-val { color: #38CB89; }

/* Info Grid */
.info-grid { display: grid; gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 4px; }
.info-label { font-size: 12px; color: #6C7275; text-transform: uppercase; font-weight: 600; }
.info-val { font-size: 14px; color: #141718; font-weight: 500; }

.info-badge { display: inline-block; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; width: fit-content; }

/* Status Update Select */
.dt-select {
    width: 100%; padding: 10px 14px; border: 1px solid #E8ECEF; border-radius: 8px;
    font-size: 14px; outline: none; margin-bottom: 12px;
}
.btn-update {
    width: 100%; padding: 10px; background: #141718; color: #fff; border: none; border-radius: 8px;
    font-weight: 600; cursor: pointer; transition: 0.2s;
}
.btn-update:hover { background: #343839; }

/* Toast */
.adm-toast {
    position: fixed; bottom: 24px; right: 24px; background: #141718; color: #fff;
    padding: 12px 24px; border-radius: 8px; font-size: 14px; display: none; z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: fadeUp 0.3s ease;
}
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 992px) {
    .detail-layout { flex-direction: column; }
    .detail-left, .detail-right { flex: 1 1 100%; width: 100%; }
}
</style>

<div class="adm-page-header">
    <div>
        <h1>Chi tiết đơn hàng</h1>
        <p><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
</div>

<div class="detail-layout">
    <!-- LEFT COLUMN -->
    <div class="detail-left">
        <!-- Products -->
        <div class="dt-card">
            <h3 class="dt-title">Chi tiết sản phẩm</h3>
            <table class="dt-table">
                <thead>
                    <tr><th>Sản phẩm</th><th>Đơn giá</th><th>SL</th><th style="text-align:right;">Thành tiền</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <tr>
                        <td>
                            <div class="dt-product">
                                <img src="<?= orderThumb($it['thumbnail']) ?>" alt="">
                                <div>
                                    <div class="dt-product-name"><?= htmlspecialchars($it['product_name']) ?></div>
                                    <?php if ($it['variant']): ?><div class="dt-product-var"><?= htmlspecialchars($it['variant']) ?></div><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td style="color:#6C7275; font-size:14px;"><?= formatVND($it['price']) ?></td>
                        <td style="font-size:14px; font-weight:500;"><?= $it['quantity'] ?></td>
                        <td style="text-align:right; font-weight:600; font-size:14px;"><?= formatVND($it['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="dt-totals">
                <div class="dt-row"><span>Subtotal</span><span class="val"><?= formatVND($order['subtotal']) ?></span></div>
                <div class="dt-row"><span>Shipping</span><span class="val"><?= $order['shipping_fee'] > 0 ? formatVND($order['shipping_fee']) : '<span style="color:#38CB89">FREE</span>' ?></span></div>
                <?php if ($order['discount'] > 0): ?>
                    <div class="dt-row"><span>Discount<?= $order['coupon_code'] ? ' ('.htmlspecialchars($order['coupon_code']).')' : '' ?></span><span class="val discount-val">- <?= formatVND($order['discount']) ?></span></div>
                <?php endif; ?>
                <div class="dt-row grand"><span>Total</span><span class="val"><?= formatVND($order['total']) ?></span></div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="detail-right">
        <!-- Info -->
        <div class="dt-card">
            <h3 class="dt-title">Thông tin giao dịch</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Mã đơn hàng</span>
                    <span class="info-val" style="font-family:monospace; background:#F3F5F7; padding:4px 8px; border-radius:4px; width:fit-content;"><?= htmlspecialchars($order['order_code']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ngày đặt</span>
                    <span class="info-val"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cổng thanh toán</span>
                    <span class="info-val" style="text-transform:uppercase;"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>
            </div>
        </div>

        <!-- Khách hàng -->
        <div class="dt-card">
            <h3 class="dt-title">Thông tin khách hàng</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Họ tên & Email</span>
                    <span class="info-val"><?= htmlspecialchars($order['full_name']) ?> <br> <span style="color:#6C7275; font-size:13px;"><?= htmlspecialchars($order['email'] ?? 'No email') ?></span></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Số điện thoại</span>
                    <span class="info-val"><?= htmlspecialchars($order['phone']) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nơi giao</span>
                    <span class="info-val"><?= htmlspecialchars($order['address']) ?><?= $order['city'] ? ', '.htmlspecialchars($order['city']) : '' ?></span>
                </div>
                <?php if ($order['note']): ?>
                <div class="info-item" style="background:#FFF8E1; padding:12px; border-radius:8px; border:1px solid #FFE082;">
                    <span class="info-label" style="color:#F57F17;">Ghi chú của khách</span>
                    <span class="info-val" style="color:#F57F17; font-style:italic;"><?= htmlspecialchars($order['note']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trạng thái đơn -->
        <div class="dt-card">
            <h3 class="dt-title">Trạng thái đơn hàng</h3>
            <div style="margin-bottom: 20px;">
                <?php $curr = $statusLabels[$order['status']] ?? ['Unknown','#000','#eee']; ?>
                <span class="info-badge" id="badge-status" style="background:<?= $curr[2] ?>; color:<?= $curr[1] ?>;"><?= $curr[0] ?></span>
            </div>
            <select id="sel-status" class="dt-select">
                <?php foreach ($statusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $k === $order['status'] ? 'selected' : '' ?>><?= $v[0] ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-update" onclick="updateOrderStatus()">Cập nhật</button>
        </div>

        <!-- Trạng thái TT -->
        <div class="dt-card">
            <h3 class="dt-title">Trạng thái thanh toán</h3>
            <div style="margin-bottom: 20px;">
                <?php $currP = $paymentStatusLabels[$order['payment_status']] ?? ['Unknown','#000','#eee']; ?>
                <span class="info-badge" id="badge-payment" style="background:<?= $currP[2] ?>; color:<?= $currP[1] ?>;"><?= $currP[0] ?></span>
            </div>
            <select id="sel-payment" class="dt-select">
                <?php foreach ($paymentStatusLabels as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $k === $order['payment_status'] ? 'selected' : '' ?>><?= $v[0] ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn-update" onclick="updatePaymentStatus()">Cập nhật</button>
        </div>

    </div>
</div>

<div id="toastMsg" class="adm-toast"><i class="fa-solid fa-check-circle" style="color:#38CB89; margin-right:8px;"></i> <span id="toastText"></span></div>

<?php include '../layouts/admin_footer.php'; ?>

<script>
const statusConfig = <?= json_encode($statusLabels) ?>;
const payConfig = <?= json_encode($paymentStatusLabels) ?>;
const orderId = <?= $order['id'] ?>;

function showToast(msg) {
    const t = document.getElementById('toastMsg');
    document.getElementById('toastText').textContent = msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 3000);
}

async function updateOrderStatus() {
    const val = document.getElementById('sel-status').value;
    try {
        const fd = new FormData();
        fd.append('order_id', orderId);
        fd.append('status', val);

        const res = await fetch('../../controllers/OrderController.php?action=admin_update_status', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            const b = document.getElementById('badge-status');
            b.style.backgroundColor = statusConfig[val][2];
            b.style.color = statusConfig[val][1];
            b.textContent = statusConfig[val][0];
            showToast('Cập nhật trạng thái đơn thành công');
        } else { alert(data.message); }
    } catch (e) { alert('Lỗi mạng'); }
}

async function updatePaymentStatus() {
    const val = document.getElementById('sel-payment').value;
    try {
        const fd = new FormData();
        fd.append('order_id', orderId);
        fd.append('payment_status', val);

        const res = await fetch('../../controllers/OrderController.php?action=admin_update_payment', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            const b = document.getElementById('badge-payment');
            b.style.backgroundColor = payConfig[val][2];
            b.style.color = payConfig[val][1];
            b.textContent = payConfig[val][0];
            showToast('Cập nhật thanh toán thành công');
        } else { alert(data.message); }
    } catch (e) { alert('Lỗi mạng'); }
}
</script>
