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

$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$page_num = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page_num - 1) * $per_page;

$filters = ['status' => $status, 'search' => $search];
$orders = $orderModel->getAllOrders($filters, $per_page, $offset);
$total = $orderModel->countAllOrders($filters);
$pages = max(1, ceil($total / $per_page));

$stats = $orderModel->getAdminDashboardStats();

$currentPage = 'orders';
$pageTitle = 'Order Management';
$breadcrumb = 'Sales / Orders';
$base_path = '../';

include '../layouts/admin_header.php';

$statusLabels = [
    'pending' => ['Pending', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Confirmed', '#2196F3', '#E3F2FD'],
    'shipping' => ['Shipping', '#9C27B0', '#F3E5F5'],
    'delivered' => ['Delivered', '#38CB89', '#E8F9EE'],
    'cancelled' => ['Cancelled', '#FF5630', '#FFF0F0'],
];

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((int) $price, 0, ',', '.') . ' đ';
    }
}
?>

<style>
    /* CHỐT CHẶN CỨNG: Ép toàn bộ nội dung không được trôi ngang */
    .order-container {
        max-width: 1250px;
        width: 100%;
        margin: 0 auto;
        padding: 20px;
        box-sizing: border-box;
        overflow-x: hidden !important; /* Tuyệt đối không cho trang trôi */
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #E8ECEF;
    }

    .stat-value { font-size: 24px; font-weight: 700; color: #141718; display: block; margin-top: 5px; }

    /* Filter Bar */
    .filter-bar {
        background: #fff;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid #E8ECEF;
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .filter-input { flex: 1; min-width: 200px; padding: 10px; border: 1px solid #E8ECEF; border-radius: 8px; }

    /* BẢNG ĐỨNG IM */
    .order-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #E8ECEF;
        width: 100%;
        overflow: hidden; /* Cắt nội dung tràn */
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* KHÓA CỨNG CỘT */
    }

    th, td {
        padding: 12px 10px;
        text-align: left;
        border-bottom: 1px solid #E8ECEF;
        font-size: 13px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis; /* Dấu ... khi chữ dài */
    }

    th { background: #F3F5F7; color: #6C7275; font-size: 11px; text-transform: uppercase; font-weight: 700; }

    /* Chia tỉ lệ cột chuẩn */
    th:nth-child(1), td:nth-child(1) { width: 120px; } /* Code */
    th:nth-child(2), td:nth-child(2) { width: auto; }  /* Customer */
    th:nth-child(3), td:nth-child(3) { width: 110px; } /* Phone */
    th:nth-child(4), td:nth-child(4) { width: 110px; } /* Total */
    th:nth-child(5), td:nth-child(5) { width: 120px; } /* Status */
    th:nth-child(6), td:nth-child(6) { width: 140px; } /* Date */
    th:nth-child(7), td:nth-child(7) { width: 90px; }  /* Action */

    .status-select-inline {
        width: 100%;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        border: none;
        cursor: pointer;
    }

    .btn-view {
        padding: 6px 12px;
        background: #F3F5F7;
        border-radius: 6px;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        color: #141718;
    }
</style>

<div class="order-container">
    <div style="margin-bottom: 24px;">
        <h1 style="margin:0; font-size: 28px;"><?= htmlspecialchars($pageTitle) ?></h1>
        <p style="color: #6C7275; margin: 5px 0 0;"><?= htmlspecialchars($breadcrumb) ?></p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span style="font-size:12px; color:#6C7275; font-weight:600;">TOTAL ORDERS</span>
            <span class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></span>
        </div>
        <div class="stat-card">
            <span style="font-size:12px; color:#6C7275; font-weight:600;">REVENUE</span>
            <span class="stat-value"><?= formatVND($stats['revenue'] ?? 0) ?></span>
        </div>
        <div class="stat-card">
            <span style="font-size:12px; color:#6C7275; font-weight:600;">PENDING</span>
            <span class="stat-value"><?= number_format($stats['pending_orders'] ?? 0) ?></span>
        </div>
        <div class="stat-card">
            <span style="font-size:12px; color:#6C7275; font-weight:600;">CUSTOMERS</span>
            <span class="stat-value"><?= number_format($stats['total_customers'] ?? 0) ?></span>
        </div>
    </div>

    <form class="filter-bar" method="GET">
        <input type="text" name="search" class="filter-input" placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
        <select name="status" class="filter-input" style="max-width: 200px;">
            <option value="">All Status</option>
            <?php foreach ($statusLabels as $k => $v): ?>
                <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $v[0] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding: 10px 25px; background: #141718; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Search</button>
    </form>

    <div class="order-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="7" style="text-align: center; padding: 40px; color: #999;">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <tr>
                                <td style="font-weight: 700;">#<?= htmlspecialchars($ord['order_code']) ?></td>
                                <td title="<?= htmlspecialchars($ord['full_name']) ?>"><?= htmlspecialchars($ord['full_name']) ?></td>
                                <td><?= htmlspecialchars($ord['phone']) ?></td>
                                <td style="font-weight: 700;"><?= formatVND($ord['total']) ?></td>
                                <td>
                                    <?php $currConf = $statusLabels[$ord['status']] ?? ['Unknown', '#000', '#eee']; ?>
                                    <select class="status-select-inline" 
                                            style="background: <?= $currConf[2] ?>; color: <?= $currConf[1] ?>;"
                                            onchange="updateStatus(<?= $ord['id'] ?>, this)">
                                        <?php foreach ($statusLabels as $stKey => $stConf): ?>
                                            <option value="<?= $stKey ?>" <?= $stKey === $ord['status'] ? 'selected' : '' ?>
                                                    data-bg="<?= $stConf[2] ?>" data-color="<?= $stConf[1] ?>">
                                                <?= $stConf[0] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td style="color: #6C7275; font-size: 12px;"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></td>
                                <td><a href="detail.php?id=<?= $ord['id'] ?>" class="btn-view">Details</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
            selectEl.style.background = bg;
            selectEl.style.color = color;
        } else {
            alert('Error: ' + (data.message || 'Update failed'));
            location.reload();
        }
    } catch (e) {
        alert('Network error!');
    }
}
</script>