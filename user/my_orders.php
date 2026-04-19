<?php
require_once '../config/config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';
require_once '../models/Order.php';
require_once '../models/OrderDetail.php';

$user_id       = $_SESSION['user']['id'];
$user_name     = $_SESSION['user']['name'] ?? '';
$user_email    = $_SESSION['user']['email'] ?? '';
$user_avatar   = $_SESSION['user']['avatar'] ?? 'default.jpg';
$status_filter = $_GET['status'] ?? '';
$orderModel    = new Order($conn);
$orders        = $orderModel->getOrdersByUser($user_id, $status_filter);

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int)$price, 0, ',', '.') . ' đ';
    }
}

// Chi tiết đơn hàng
$detail_code = $_GET['detail'] ?? null;
$detailOrder = null;
$detailItems = [];
if ($detail_code) {
    $detailModel = new OrderDetail($conn);
    $detailOrder = $orderModel->getOrderByCode($detail_code);
    if ($detailOrder && (int)$detailOrder['user_id'] === $user_id) {
        $detailItems = $detailModel->getItemsByOrderId($detailOrder['id']);
    } else {
        $detailOrder = null;
    }
}

// POST cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $cancel_id = (int)$_POST['order_id'];
    $orderModel->cancelOrder($cancel_id, $user_id);
    header("Location: my_orders.php");
    exit;
}

function orderThumb($thumb) {
    if (!$thumb) return '../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../assets/images/' . htmlspecialchars($thumb);
}

$statusConfig = [
    'pending'   => ['Chờ xác nhận', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Đã xác nhận', '#2196F3', '#E3F2FD'],
    'shipping'  => ['Đang giao',   '#9C27B0', '#F3E5F5'],
    'delivered' => ['Đã giao',     '#38CB89', '#E8F9EE'],
    'cancelled' => ['Đã hủy',      '#FF5630', '#FFF0F0'],
];

$paymentLabels = [
    'cod'           => 'COD',
    'bank_transfer' => 'Chuyển khoản',
    'momo'          => 'MoMo'
];

$paymentStatusLabels = [
    'pending'  => ['Chưa thanh toán', '#FFAB00', '#FFF7ED'],
    'paid'     => ['Đã thanh toán',   '#38CB89', '#E8F9EE'],
    'failed'   => ['Thất bại',        '#FF5630', '#FFF0F0'],
    'refunded' => ['Đã hoàn tiền',    '#2196F3', '#E3F2FD'],
];

// Timeline steps
$timelineSteps = ['pending', 'confirmed', 'shipping', 'delivered'];
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
.page-header { font-size: 40px; font-weight: 600; text-align: center; margin-bottom: 60px; letter-spacing: -0.5px; }
.account-layout { display: flex; gap: 60px; padding-bottom: 80px; }
.account-main-content { flex: 1; min-width: 0; }
.section-title { font-size: 20px; font-weight: 600; margin-bottom: 24px; }

/* Status tabs */
.status-tabs { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; }
.status-tab {
    padding: 8px 20px; border: 1px solid #E8ECEF; border-radius: 40px; font-size: 13px;
    font-weight: 500; text-decoration: none; color: #6C7275; transition: all 0.2s; cursor: pointer;
}
.status-tab:hover { border-color: #141718; color: #141718; }
.status-tab.active { background: #141718; color: #fff; border-color: #141718; }

/* Order card */
.order-card {
    border: 1px solid #E8ECEF; border-radius: 12px; padding: 24px; margin-bottom: 16px;
    transition: box-shadow 0.2s;
}
.order-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.04); }

.order-card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.order-code { font-weight: 600; font-size: 14px; color: #141718; font-family: 'Courier New', monospace; }
.order-date { font-size: 13px; color: #6C7275; }
.status-badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
}

.order-card-items { display: flex; gap: 8px; align-items: center; margin-bottom: 16px; }
.order-thumb { width: 56px; height: 56px; object-fit: cover; border-radius: 8px; background: #F3F5F7; border: 1px solid #E8ECEF; }
.order-more { font-size: 13px; color: #6C7275; font-weight: 500; padding: 0 8px; }

.order-card-bottom { display: flex; justify-content: space-between; align-items: center; }
.order-total { font-size: 16px; font-weight: 600; color: #141718; }
.order-actions { display: flex; gap: 8px; }
.btn-view {
    padding: 8px 20px; background: #141718; color: #fff; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; transition: background 0.2s;
}
.btn-view:hover { background: #343839; }
.btn-cancel-sm {
    padding: 8px 16px; background: #fff; color: #FF5630; border: 1px solid #FF5630; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.btn-cancel-sm:hover { background: #FFF0F0; }

/* Empty */
.orders-empty { text-align: center; padding: 60px 20px; color: #6C7275; }
.orders-empty-icon { font-size: 56px; color: #E8ECEF; margin-bottom: 20px; }
.orders-empty h3 { font-size: 20px; font-weight: 600; color: #141718; margin-bottom: 8px; }
.btn-shop-link {
    display: inline-block; margin-top: 20px; padding: 12px 32px; background: #141718; color: #fff;
    border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;
}

/* ─── Detail view ─── */
.back-link {
    display: inline-flex; align-items: center; gap: 8px; color: #6C7275; text-decoration: none;
    font-size: 14px; font-weight: 500; margin-bottom: 24px; transition: color 0.2s;
}
.back-link:hover { color: #141718; }

.detail-card { border: 1px solid #E8ECEF; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.detail-card-title { font-size: 16px; font-weight: 600; margin-bottom: 20px; color: #141718; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.detail-label { font-size: 12px; color: #6C7275; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
.detail-value { font-size: 14px; font-weight: 500; color: #141718; }

/* Timeline */
.timeline-wrap { padding: 24px 0 8px; }
.timeline {
    display: flex; justify-content: space-between; position: relative;
}
.timeline::before {
    content: ''; position: absolute; top: 16px; left: 40px; right: 40px;
    height: 2px; background: #E8ECEF; z-index: 0;
}
.timeline-step { display: flex; flex-direction: column; align-items: center; z-index: 1; width: 25%; }
.timeline-dot {
    width: 32px; height: 32px; border-radius: 50%; border: 2px solid #E8ECEF;
    background: #fff; display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 600; color: #6C7275; margin-bottom: 8px; transition: all 0.3s;
}
.timeline-step.done .timeline-dot { background: #38CB89; border-color: #38CB89; color: #fff; }
.timeline-step.current .timeline-dot { background: #141718; border-color: #141718; color: #fff; }
.timeline-step.cancelled .timeline-dot { background: #FF5630; border-color: #FF5630; color: #fff; }
.timeline-label { font-size: 12px; color: #6C7275; text-align: center; }
.timeline-step.done .timeline-label { color: #38CB89; }
.timeline-step.current .timeline-label { color: #141718; font-weight: 600; }

/* Items table */
.items-table { width: 100%; border-collapse: collapse; }
.items-table th {
    text-align: left; font-size: 12px; font-weight: 600; color: #6C7275; text-transform: uppercase;
    padding: 0 0 12px; border-bottom: 1px solid #E8ECEF; letter-spacing: 0.05em;
}
.items-table td { padding: 14px 0; border-bottom: 1px solid #F3F5F7; vertical-align: middle; }
.items-table .product-cell { display: flex; align-items: center; gap: 12px; }
.items-table .product-cell img { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; background: #F3F5F7; }
.items-table .product-cell .name { font-size: 13px; font-weight: 600; }
.items-table .product-cell .variant { font-size: 12px; color: #6C7275; }

/* Totals in detail */
.detail-totals { margin-top: 16px; }
.detail-total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
.detail-total-row .label { color: #6C7275; }
.detail-total-row .value { font-weight: 600; }
.detail-total-row.grand { border-top: 1px solid #E8ECEF; padding-top: 16px; margin-top: 8px; }
.detail-total-row.grand .label { font-weight: 600; color: #141718; font-size: 16px; }
.detail-total-row.grand .value { font-size: 20px; }
.discount-val { color: #38CB89; }
.free-ship { color: #38CB89; font-weight: 600; }

.btn-cancel-order {
    margin-top: 20px; padding: 12px 28px; background: #fff; color: #FF5630;
    border: 1.5px solid #FF5630; border-radius: 8px; font-weight: 600; font-size: 14px;
    cursor: pointer; transition: all 0.2s;
}
.btn-cancel-order:hover { background: #FFF0F0; }

@media (max-width: 768px) {
    .account-layout { flex-direction: column; gap: 10px; align-items: center; }
    .account-main-content { width: 100%; }
    .page-header { font-size: 28px; margin-bottom: 30px; }
    .detail-grid { grid-template-columns: 1fr; }
    .timeline { flex-wrap: wrap; gap: 8px; }
    .timeline::before { display: none; }
    .order-card-bottom { flex-direction: column; gap: 12px; align-items: flex-start; }
}
</style>

<div class="container" style="margin-top: 60px;">
    <h1 class="page-header">My Account</h1>

    <div class="account-layout">
        <?php include '../includes/account_sidebar.php'; ?>

        <div class="account-main-content">

            <?php if ($detailOrder): ?>
            <!-- ═══════════ DETAIL VIEW ═══════════ -->
            <a href="my_orders.php" class="back-link">
                <i class="fa-solid fa-chevron-left"></i> Đơn hàng của tôi
            </a>

            <h2 class="section-title"><?= htmlspecialchars($detailOrder['order_code']) ?></h2>

            <!-- Timeline -->
            <?php if ($detailOrder['status'] !== 'cancelled'): ?>
            <div class="detail-card">
                <div class="timeline-wrap">
                    <div class="timeline">
                        <?php
                        $currentIdx = array_search($detailOrder['status'], $timelineSteps);
                        if ($currentIdx === false) $currentIdx = -1;
                        $stepLabels = ['Chờ xác nhận', 'Đã xác nhận', 'Đang giao', 'Đã giao'];
                        foreach ($timelineSteps as $i => $step):
                            $cls = '';
                            if ($i < $currentIdx) $cls = 'done';
                            elseif ($i === $currentIdx) $cls = 'current';
                        ?>
                            <div class="timeline-step <?= $cls ?>">
                                <div class="timeline-dot">
                                    <?= $cls === 'done' ? '✓' : ($i + 1) ?>
                                </div>
                                <div class="timeline-label"><?= $stepLabels[$i] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="detail-card" style="text-align:center;">
                <span class="status-badge" style="background:#FFF0F0; color:#FF5630; font-size:14px; padding:8px 20px;">Đơn hàng đã bị hủy</span>
            </div>
            <?php endif; ?>

            <!-- Order Info -->
            <div class="detail-card">
                <h3 class="detail-card-title">Thông tin đơn hàng</h3>
                <div class="detail-grid">
                    <div><div class="detail-label">Mã đơn</div><div class="detail-value" style="font-family:monospace;"><?= htmlspecialchars($detailOrder['order_code']) ?></div></div>
                    <div><div class="detail-label">Ngày đặt</div><div class="detail-value"><?= date('d/m/Y H:i', strtotime($detailOrder['created_at'])) ?></div></div>
                    <div>
                        <div class="detail-label">Trạng thái</div>
                        <?php $sc = $statusConfig[$detailOrder['status']] ?? ['N/A','#6C7275','#F3F5F7']; ?>
                        <span class="status-badge" style="background:<?= $sc[2] ?>; color:<?= $sc[1] ?>;"><?= $sc[0] ?></span>
                    </div>
                    <div>
                        <div class="detail-label">Thanh toán</div>
                        <?php $ps = $paymentStatusLabels[$detailOrder['payment_status']] ?? ['N/A','#6C7275','#F3F5F7']; ?>
                        <span class="status-badge" style="background:<?= $ps[2] ?>; color:<?= $ps[1] ?>;"><?= $ps[0] ?></span>
                    </div>
                </div>
            </div>

            <!-- Shipping info -->
            <div class="detail-card">
                <h3 class="detail-card-title">Thông tin giao hàng</h3>
                <div class="detail-grid">
                    <div><div class="detail-label">Người nhận</div><div class="detail-value"><?= htmlspecialchars($detailOrder['full_name']) ?></div></div>
                    <div><div class="detail-label">Số điện thoại</div><div class="detail-value"><?= htmlspecialchars($detailOrder['phone']) ?></div></div>
                    <div style="grid-column:1/-1;"><div class="detail-label">Địa chỉ</div><div class="detail-value"><?= htmlspecialchars($detailOrder['address']) ?><?= $detailOrder['city'] ? ', ' . htmlspecialchars($detailOrder['city']) : '' ?></div></div>
                    <?php if ($detailOrder['note']): ?>
                        <div style="grid-column:1/-1;"><div class="detail-label">Ghi chú</div><div class="detail-value"><?= htmlspecialchars($detailOrder['note']) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Items -->
            <div class="detail-card">
                <h3 class="detail-card-title">Sản phẩm (<?= count($detailItems) ?>)</h3>
                <table class="items-table">
                    <thead>
                        <tr><th>Sản phẩm</th><th>Giá</th><th>SL</th><th style="text-align:right;">Thành tiền</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailItems as $it): ?>
                        <tr>
                            <td>
                                <div class="product-cell">
                                    <img src="<?= orderThumb($it['thumbnail']) ?>" alt="" onerror="this.src='../assets/images/sofa.jpg'">
                                    <div>
                                        <div class="name"><?= htmlspecialchars($it['product_name']) ?></div>
                                        <?php if ($it['variant']): ?><div class="variant"><?= htmlspecialchars($it['variant']) ?></div><?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td style="color:#6C7275;"><?= formatVND($it['price']) ?></td>
                            <td><?= $it['quantity'] ?></td>
                            <td style="text-align:right; font-weight:600;"><?= formatVND($it['subtotal']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="detail-totals">
                    <div class="detail-total-row">
                        <span class="label">Subtotal</span>
                        <span class="value"><?= formatVND($detailOrder['subtotal']) ?></span>
                    </div>
                    <div class="detail-total-row">
                        <span class="label">Shipping</span>
                        <span class="value"><?= $detailOrder['shipping_fee'] > 0 ? formatVND($detailOrder['shipping_fee']) : '<span class="free-ship">FREE</span>' ?></span>
                    </div>
                    <?php if ($detailOrder['discount'] > 0): ?>
                        <div class="detail-total-row">
                            <span class="label">Discount<?= $detailOrder['coupon_code'] ? ' (' . htmlspecialchars($detailOrder['coupon_code']) . ')' : '' ?></span>
                            <span class="value discount-val">- <?= formatVND($detailOrder['discount']) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="detail-total-row grand">
                        <span class="label">Total</span>
                        <span class="value"><?= formatVND($detailOrder['total']) ?></span>
                    </div>
                    <div class="detail-total-row">
                        <span class="label">Phương thức</span>
                        <span class="value"><?= $paymentLabels[$detailOrder['payment_method']] ?? $detailOrder['payment_method'] ?></span>
                    </div>
                </div>
            </div>

            <?php if ($detailOrder['status'] === 'pending'): ?>
                <form method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="order_id" value="<?= $detailOrder['id'] ?>">
                    <button type="submit" class="btn-cancel-order">
                        <i class="fa-solid fa-xmark"></i> Hủy đơn hàng
                    </button>
                </form>
            <?php endif; ?>

            <?php else: ?>
            <!-- ═══════════ LIST VIEW ═══════════ -->
            <h2 class="section-title">Đơn hàng của tôi</h2>

            <!-- Status Tabs -->
            <div class="status-tabs">
                <a href="my_orders.php" class="status-tab <?= !$status_filter ? 'active' : '' ?>">Tất cả</a>
                <a href="my_orders.php?status=pending" class="status-tab <?= $status_filter === 'pending' ? 'active' : '' ?>">Chờ xác nhận</a>
                <a href="my_orders.php?status=confirmed" class="status-tab <?= $status_filter === 'confirmed' ? 'active' : '' ?>">Đã xác nhận</a>
                <a href="my_orders.php?status=shipping" class="status-tab <?= $status_filter === 'shipping' ? 'active' : '' ?>">Đang giao</a>
                <a href="my_orders.php?status=delivered" class="status-tab <?= $status_filter === 'delivered' ? 'active' : '' ?>">Đã giao</a>
                <a href="my_orders.php?status=cancelled" class="status-tab <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Đã hủy</a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="orders-empty">
                    <div class="orders-empty-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Hãy bắt đầu mua sắm ngay!</p>
                    <a href="shop.php" class="btn-shop-link">Shop Now</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $ord):
                    $sc = $statusConfig[$ord['status']] ?? ['N/A', '#6C7275', '#F3F5F7'];

                    // Lấy thumbnails cho preview
                    $thumbStmt = $conn->prepare(
                        "SELECT thumbnail FROM order_items WHERE order_id = ? LIMIT 4"
                    );
                    $thumbStmt->bind_param('i', $ord['id']);
                    $thumbStmt->execute();
                    $thumbs = $thumbStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $itemCount = (int)($ord['item_count'] ?? 0);
                ?>
                <div class="order-card">
                    <div class="order-card-top">
                        <div>
                            <span class="order-code"><?= htmlspecialchars($ord['order_code']) ?></span>
                            <span class="order-date" style="margin-left:12px;"><?= date('d/m/Y', strtotime($ord['created_at'])) ?></span>
                        </div>
                        <span class="status-badge" style="background: <?= $sc[2] ?>; color: <?= $sc[1] ?>;">
                            <?= $sc[0] ?>
                        </span>
                    </div>
                    <div class="order-card-items">
                        <?php
                        $shown = 0;
                        foreach ($thumbs as $t):
                            if ($shown >= 3) break;
                            $shown++;
                        ?>
                            <img src="<?= orderThumb($t['thumbnail']) ?>" alt="" class="order-thumb" onerror="this.src='../assets/images/sofa.jpg'">
                        <?php endforeach; ?>
                        <?php if ($itemCount > 3): ?>
                            <span class="order-more">+<?= $itemCount - 3 ?> sản phẩm</span>
                        <?php endif; ?>
                    </div>
                    <div class="order-card-bottom">
                        <div class="order-total"><?= formatVND($ord['total']) ?></div>
                        <div class="order-actions">
                            <a href="my_orders.php?detail=<?= urlencode($ord['order_code']) ?>" class="btn-view">Xem chi tiết</a>
                            <?php if ($ord['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Hủy đơn hàng này?')">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                    <button type="submit" class="btn-cancel-sm">Hủy</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
