<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin' || !str_ends_with($_user['email'] ?? '', '@3legant.com')) {
    header("Location: ../user/login.php");
    exit;
}

$currentPage = 'dashboard';
$pageTitle   = 'Dashboard';
$breadcrumb  = 'Overview / Dashboard';
$base_path   = '';

include 'layouts/admin_header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$orderModel = new Order($conn);
$stats = $orderModel->getAdminDashboardStats();
$recentOrders = $orderModel->getRecentOrders(5);
$lowStockCount = count($orderModel->getLowStockProducts(10));

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

<div class="adm-page-header">
    <div>
        <h1>Dashboard Overview</h1>
        <p>Business overview statistics</p>
    </div>
</div>

<!-- KPI Stats -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value"><?= formatVND($stats['revenue']) ?></div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;">Doanh thu hệ thống</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;"><?= number_format($stats['pending_orders']) ?> đơn chờ</span> Cần xử lý</div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Active Customers</div>
        <div class="stat-value"><?= number_format($stats['total_customers']) ?></div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;">Khách hàng</span></div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value" style="color: <?= $lowStockCount > 0 ? 'var(--red)' : '#38CB89' ?>;"><?= $lowStockCount ?></div>
        <div class="stat-note" style="color: <?= $lowStockCount > 0 ? 'var(--red)' : '#6C7275' ?>;"><?= $lowStockCount > 0 ? 'Needs attention immediately' : 'Kho hàng ổn định' ?></div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div style="padding: 20px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--gray-300);">
        <h3 style="font-size: 16px; font-weight:600;">Recent Orders</h3>
        <a href="orders/index.php" style="font-size: 13px; color: var(--blue); text-decoration:none; font-weight:500;">View All</a>
    </div>
    <table class="adm-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recentOrders)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px; color: #6C7275;">No recent orders.</td></tr>
            <?php else: ?>
                <?php foreach ($recentOrders as $ord): 
                    $currConf = $statusLabels[$ord['status']] ?? ['Unknown','#000','#eee'];
                ?>
                <tr>
                    <td style="color: var(--gray-400); font-family: monospace;">
                        <a href="orders/detail.php?id=<?= $ord['id'] ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($ord['order_code']) ?></a>
                    </td>
                    <td style="font-weight: 500;"><?= htmlspecialchars($ord['full_name']) ?></td>
                    <td style="color: var(--gray-400); font-size:13px;"><?= date('d M, Y', strtotime($ord['created_at'])) ?></td>
                    <td style="font-weight: 500;"><?= formatVND($ord['total']) ?></td>
                    <td>
                        <span class="badge" style="background:<?= $currConf[2] ?>; color:<?= $currConf[1] ?>; padding: 4px 8px; border-radius: 4px;">
                            <?= $currConf[0] ?>
                        </span>
                    </td>
                    <td>
                        <a href="orders/detail.php?id=<?= $ord['id'] ?>" style="color: var(--gray-400);"><i class="fa-solid fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'layouts/admin_footer.php'; ?>
