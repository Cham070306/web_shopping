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
        <div class="stat-value">25,840,000₫</div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;"><i class="fa-solid fa-arrow-trend-up"></i> 12.5%</span> vs last month</div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">1,248</div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;"><i class="fa-solid fa-arrow-trend-up"></i> 5.2%</span> vs last month</div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Active Customers</div>
        <div class="stat-value">486</div>
        <div class="stat-note"><span class="badge badge-green" style="margin-right:4px;"><i class="fa-solid fa-arrow-trend-up"></i> 18%</span> vs last month</div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value" style="color: var(--red);">12</div>
        <div class="stat-note" style="color: var(--red);">Needs attention immediately</div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="adm-card" style="margin-bottom: 30px;">
    <div style="padding: 20px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--gray-300);">
        <h3 style="font-size: 16px; font-weight:600;">Recent Orders</h3>
        <a href="#" style="font-size: 13px; color: var(--blue); text-decoration:none; font-weight:500;">View All</a>
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
            <tr>
                <td style="color: var(--gray-400);">#ORD-8012</td>
                <td style="font-weight: 500;">Nhật Linh</td>
                <td style="color: var(--gray-400);">09 Apr, 2026</td>
                <td style="font-weight: 500;">1,250,000₫</td>
                <td><span class="badge" style="background:#FFF7ED; color:#EA580C;">Pending</span></td>
                <td><a href="#" style="color: var(--gray-400);"><i class="fa-solid fa-ellipsis"></i></a></td>
            </tr>
            <tr>
                <td style="color: var(--gray-400);">#ORD-8011</td>
                <td style="font-weight: 500;">Hải Nam</td>
                <td style="color: var(--gray-400);">08 Apr, 2026</td>
                <td style="font-weight: 500;">5,400,000₫</td>
                <td><span class="badge badge-blue">Processing</span></td>
                <td><a href="#" style="color: var(--gray-400);"><i class="fa-solid fa-ellipsis"></i></a></td>
            </tr>
            <tr>
                <td style="color: var(--gray-400);">#ORD-8010</td>
                <td style="font-weight: 500;">Minh Châu</td>
                <td style="color: var(--gray-400);">06 Apr, 2026</td>
                <td style="font-weight: 500;">850,000₫</td>
                <td><span class="badge badge-green">Completed</span></td>
                <td><a href="#" style="color: var(--gray-400);"><i class="fa-solid fa-ellipsis"></i></a></td>
            </tr>
            <tr>
                <td style="color: var(--gray-400);">#ORD-8009</td>
                <td style="font-weight: 500;">Thế Anh</td>
                <td style="color: var(--gray-400);">05 Apr, 2026</td>
                <td style="font-weight: 500;">3,200,000₫</td>
                <td><span class="badge badge-green">Completed</span></td>
                <td><a href="#" style="color: var(--gray-400);"><i class="fa-solid fa-ellipsis"></i></a></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include 'layouts/admin_footer.php'; ?>
