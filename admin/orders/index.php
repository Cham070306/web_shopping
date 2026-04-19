<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/config.php";
require_once "../../config/database.php";
require_once "../../models/Order.php";

$orderModel = new Order($conn);

$status   = $_GET['status']  ?? '';
$search   = trim($_GET['search'] ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset   = ($page_num - 1) * $per_page;

$filters = ['status' => $status, 'search' => $search];
$orders  = $orderModel->getAllOrders($filters, $per_page, $offset);
$total   = $orderModel->countAllOrders($filters);
$pages   = max(1, ceil($total / $per_page));

$stats = $orderModel->getAdminDashboardStats();

$currentPage = 'orders';
$pageTitle   = 'Quản lý đơn hàng';
$breadcrumb  = 'Sales / Orders';
$base_path   = '../';

include '../layouts/admin_header.php';

$statusLabels = [
    'pending'   => ['Chờ xử lý', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Xác nhận', '#2196F3', '#E3F2FD'],
    'shipping'  => ['Đang giao', '#9C27B0', '#F3E5F5'],
    'delivered' => ['Đã giao', '#38CB89', '#E8F9EE'],
    'cancelled' => ['Đã hủy', '#FF5630', '#FFF0F0'],
];

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int)$price, 0, ',', '.') . ' đ';
    }
}
?>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
.stat-card {
    background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #E8ECEF;
    display: flex; flex-direction: column; gap: 8px;
}
.stat-title { font-size: 13px; color: #6C7275; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; }
.stat-value { font-size: 24px; font-weight: 700; color: #141718; }

.filter-bar {
    background: #fff; padding: 16px; border-radius: 12px; border: 1px solid #E8ECEF;
    display: flex; gap: 16px; margin-bottom: 24px; align-items: center;
}
.filter-input { flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid #E8ECEF; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif;}
.filter-select { padding: 10px 14px; border: 1px solid #E8ECEF; border-radius: 8px; outline: none; background: #fff; font-family: 'Inter', sans-serif; }
.btn-filter { padding: 10px 24px; background: #141718; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-filter:hover { background: #343839; }

.status-select-inline {
    padding: 6px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; border: none;
    outline: none; cursor: pointer; appearance: none; -webkit-appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23141718%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat; background-position: right 8px top 50%; background-size: 8px auto;
    padding-right: 24px !important; transition: all 0.2s;
}

.pagination { display: flex; gap: 8px; justify-content: center; margin-top: 24px; padding-bottom: 40px; }
.page-btn {
    width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
    border: 1px solid #E8ECEF; border-radius: 8px; color: #141718; text-decoration: none; font-size: 14px;
}
.page-btn:hover { background: #F3F5F7; }
.page-btn.active { background: #141718; color: #fff; border-color: #141718; }
</style>

<div class="adm-page-header">
    <div>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-title">Tổng đơn hàng</span>
        <span class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-title">Doanh thu tổng</span>
        <span class="stat-value"><?= formatVND($stats['revenue'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-title">Đơn hàng mới (Chờ xử lý)</span>
        <span class="stat-value"><?= number_format($stats['pending_orders'] ?? 0) ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-title">Người dùng</span>
        <span class="stat-value"><?= number_format($stats['total_customers'] ?? 0) ?></span>
    </div>
</div>

<!-- Filter -->
<form class="filter-bar" method="GET">
    <input type="text" name="search" class="filter-input" placeholder="Tìm mã đơn, tên, email, sđt..." value="<?= htmlspecialchars($search) ?>">
    <select name="status" class="filter-select">
        <option value="">Tất cả trạng thái</option>
        <?php foreach ($statusLabels as $k => $v): ?>
            <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-filter">Tìm kiếm</button>
</form>

<!-- Table -->
<div class="adm-card">
    <table class="adm-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>SĐT</th>
                <th>Tổng tiền</th>
                <th>PTTT</th>
                <th>Trạng thái</th>
                <th>Ngày</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr><td colspan="8" style="text-align: center; padding: 40px; color: #6C7275;">Không tìm thấy đơn hàng nào.</td></tr>
            <?php else: ?>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 600;">
                            <a href="detail.php?id=<?= $ord['id'] ?>" style="color: #141718; text-decoration: none; border-bottom: 1px dashed;">
                                <?= htmlspecialchars($ord['order_code']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($ord['full_name']) ?></td>
                        <td><?= htmlspecialchars($ord['phone']) ?></td>
                        <td style="font-weight: 600;"><?= formatVND($ord['total']) ?></td>
                        <td><span style="font-size: 12px; color: #6C7275; text-transform: uppercase;"><?= htmlspecialchars($ord['payment_method']) ?></span></td>
                        <td>
                            <?php $currConf = $statusLabels[$ord['status']] ?? ['Unknown','#000','#eee']; ?>
                            <select class="status-select-inline sts-<?= $ord['id'] ?>"
                                    style="background-color: <?= $currConf[2] ?>; color: <?= $currConf[1] ?>;"
                                    onchange="updateStatus(<?= $ord['id'] ?>, this)">
                                <?php foreach ($statusLabels as $stKey => $stConf): ?>
                                    <option value="<?= $stKey ?>" <?= $stKey === $ord['status'] ? 'selected' : '' ?>
                                            data-bg="<?= $stConf[2] ?>" data-color="<?= $stConf[1] ?>">
                                        <?= $stConf[0] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td style="font-size: 13px; color: #6C7275;"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                        <td style="text-align: right;">
                            <a href="detail.php?id=<?= $ord['id'] ?>" class="btn btn-outline btn-sm">
                                <i class="fa-solid fa-eye"></i> Chi tiết
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"
           class="page-btn <?= $i === $page_num ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php include '../layouts/admin_footer.php'; ?>

<script>
async function updateStatus(orderId, selectEl) {
    const newStatus = selectEl.value;
    const option = selectEl.options[selectEl.selectedIndex];
    const bg = option.dataset.bg;
    const color = option.dataset.color;

    try {
        const fd = new FormData();
        fd.append('order_id', orderId);
        fd.append('status', newStatus);

        const res = await fetch('../../controllers/OrderController.php?action=admin_update_status', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        if (data.success) {
            selectEl.style.backgroundColor = bg;
            selectEl.style.color = color;
        } else {
            alert(data.message || 'Cập nhật thất bại');
            window.location.reload();
        }
    } catch (e) {
        console.error(e);
        alert('Lỗi kết nối!');
    }
}
</script>
