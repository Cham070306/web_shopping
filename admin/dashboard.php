<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3legant | Admin Dashboard</title>
    
    <!-- Fonts & Icons matching the front-end -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* 3legant Core Theme Variables */
        :root {
            --black-900: #141718;
            --gray-700: #343839;
            --gray-400: #6C7275;
            --gray-300: #E8ECEF;
            --gray-100: #F3F5F7;
            --white: #FFFFFF;
            --green: #38CB89;
            --red: #FF3333;
            --font-main: 'Inter', sans-serif;
            --font-heading: 'Poppins', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-main);
        }

        body {
            background-color: var(--gray-100);
            color: var(--black-900);
            display: flex;
            min-height: 100vh;
        }

        /* ----- SIDEBAR ----- */
        .sidebar {
            width: 260px;
            background: var(--white);
            border-right: 1px solid var(--gray-300);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 50;
        }

        .sidebar-header {
            height: 72px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid var(--gray-300);
        }

        .sidebar-logo {
            font-family: var(--font-heading);
            font-size: 24px;
            font-weight: 600;
            color: var(--black-900);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .sidebar-logo span {
            color: var(--gray-400);
            font-size: 14px;
            font-family: var(--font-main);
            font-weight: 500;
            margin-left: 8px;
        }

        .sidebar-menu {
            padding: 24px 16px;
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-title {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--gray-400);
            letter-spacing: 1px;
            margin: 12px 0 8px 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--gray-400);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .menu-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .menu-item:hover {
            background: var(--gray-100);
            color: var(--black-900);
        }

        .menu-item.active {
            background: var(--black-900);
            color: var(--white);
            font-weight: 600;
        }
        
        .menu-item.active i {
            color: var(--white);
        }

        /* ----- MAIN CONTENT ----- */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; /* prevent layout break */
        }

        /* ----- TOPNAV ----- */
        .topnav {
            height: 72px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-300);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .breadcrumb {
            font-size: 14px;
            font-weight: 500;
            color: var(--black-900);
        }
        .breadcrumb span {
            color: var(--gray-400);
        }

        .topnav-right {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .search-box {
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 14px;
        }
        .search-box input {
            border: 1px solid var(--gray-300);
            border-radius: 999px;
            padding: 10px 16px 10px 40px;
            font-size: 14px;
            width: 260px;
            outline: none;
            transition: 0.2s;
        }
        .search-box input:focus {
            border-color: var(--black-900);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }
        .admin-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--black-900);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        .admin-info p {
            font-size: 14px;
            font-weight: 600;
            color: var(--black-900);
        }
        .admin-info span {
            font-size: 12px;
            color: var(--gray-400);
        }

        /* ----- DASHBOARD CONTENT ----- */
        .content {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 24px;
        }
        .page-header h1 {
            font-family: var(--font-heading);
            font-size: 28px;
            font-weight: 600;
            color: var(--black-900);
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .kpi-card {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: box-shadow 0.2s;
        }
        .kpi-card:hover {
            box-shadow: 0 8px 24px rgba(20,23,24,0.04);
        }

        .kpi-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--gray-400);
            font-size: 14px;
            font-weight: 500;
        }
        .kpi-icon {
            width: 40px; height: 40px;
            border-radius: 8px;
            background: var(--gray-100);
            color: var(--black-900);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--black-900);
            letter-spacing: -1px;
        }
        
        .badge-green {
            background: rgba(56, 203, 137, 0.1);
            color: var(--green);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Orders Table Box */
        .table-box {
            background: var(--white);
            border: 1px solid var(--gray-300);
            border-radius: 12px;
            overflow: hidden;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--gray-300);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 {
            font-size: 18px;
            font-weight: 600;
        }
        .link-btn {
            font-size: 14px;
            font-weight: 600;
            color: var(--black-900);
            text-decoration: none;
            border-bottom: 1px solid currentColor;
            padding-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: var(--gray-100);
            color: var(--gray-400);
            text-transform: uppercase;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
            padding: 14px 24px;
            letter-spacing: 0.5px;
        }
        td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--gray-300);
            font-size: 14px;
            font-weight: 500;
            color: var(--black-900);
        }
        tr:last-child td {
            border-bottom: none;
        }

        /* Status Tags */
        .tag {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .tag.processing { background: #EEF2FF; color: #4F46E5; }
        .tag.completed { background: rgba(56, 203, 137, 0.1); color: var(--green); }
        .tag.pending { background: #FFF7ED; color: #EA580C; }

    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-logo">3legant.<span>Admin</span></a>
        </div>
        <nav class="sidebar-menu">
            <p class="menu-title">Overview</p>
            <a href="#" class="menu-item active"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            
            <p class="menu-title">Manage</p>
            <a href="#" class="menu-item"><i class="fa-solid fa-layer-group"></i> Categories</a>
            <a href="#" class="menu-item"><i class="fa-solid fa-box-open"></i> Products</a>
            <a href="#" class="menu-item"><i class="fa-solid fa-bag-shopping"></i> Orders</a>
            <a href="#" class="menu-item"><i class="fa-solid fa-users"></i> Customers</a>
            
            <p class="menu-title">System</p>
            <a href="#" class="menu-item"><i class="fa-solid fa-gear"></i> Settings</a>
            <a href="#" class="menu-item"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </nav>
    </aside>

    <div class="main-wrapper">
        <!-- Top Navbar -->
        <header class="topnav">
            <div class="breadcrumb">
                <span>Admin / </span> Dashboard
            </div>
            <div class="topnav-right">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Search anything...">
                </div>
                <div class="admin-profile">
                    <div class="admin-avatar">AD</div>
                    <div class="admin-info">
                        <p>Administrator</p>
                        <span>Manager</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="content">
            <div class="page-header">
                <h1>Dashboard Overview</h1>
            </div>

            <!-- KPI Statistics -->
            <div class="kpi-grid">
                <!-- Card 1 -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span>Total Revenue</span>
                        <div class="kpi-icon"><i class="fa-solid fa-wallet"></i></div>
                    </div>
                    <div class="kpi-value">25,840,000₫</div>
                    <div><span class="badge-green"><i class="fa-solid fa-arrow-trend-up"></i> 12.5%</span> <span style="font-size: 13px; color: var(--gray-400); margin-left: 8px;">vs last month</span></div>
                </div>

                <!-- Card 2 -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span>Total Orders</span>
                        <div class="kpi-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                    </div>
                    <div class="kpi-value">1,248</div>
                    <div><span class="badge-green"><i class="fa-solid fa-arrow-trend-up"></i> 5.2%</span></div>
                </div>

                <!-- Card 3 -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span>Active Customers</span>
                        <div class="kpi-icon"><i class="fa-solid fa-user-group"></i></div>
                    </div>
                    <div class="kpi-value">486</div>
                    <div><span class="badge-green"><i class="fa-solid fa-arrow-trend-up"></i> 18%</span></div>
                </div>

                <!-- Card 4 -->
                <div class="kpi-card">
                    <div class="kpi-header">
                        <span>Low Stock Items</span>
                        <div class="kpi-icon" style="background:#FFF7ED; color:#EA580C;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                    <div class="kpi-value">12</div>
                    <div style="font-size: 13px; color: var(--gray-400);">Needs attention</div>
                </div>
            </div>

            <!-- Recent Orders Data Table -->
            <div class="table-box">
                <div class="table-header">
                    <h3>Recent Orders</h3>
                    <a href="#" class="link-btn">View All</a>
                </div>
                <!-- Table -->
                <table>
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
                            <td>#ORD-8012</td>
                            <td>Nhật Linh</td>
                            <td>09 Apr, 2026</td>
                            <td>1,250,000₫</td>
                            <td><span class="tag pending">Pending</span></td>
                            <td><a href="#" style="color:var(--black-900);"><i class="fa-solid fa-ellipsis"></i></a></td>
                        </tr>
                        <tr>
                            <td>#ORD-8011</td>
                            <td>Hải Nam</td>
                            <td>08 Apr, 2026</td>
                            <td>5,400,000₫</td>
                            <td><span class="tag processing">Processing</span></td>
                            <td><a href="#" style="color:var(--black-900);"><i class="fa-solid fa-ellipsis"></i></a></td>
                        </tr>
                        <tr>
                            <td>#ORD-8010</td>
                            <td>Minh Châu</td>
                            <td>06 Apr, 2026</td>
                            <td>850,000₫</td>
                            <td><span class="tag completed">Completed</span></td>
                            <td><a href="#" style="color:var(--black-900);"><i class="fa-solid fa-ellipsis"></i></a></td>
                        </tr>
                        <tr>
                            <td>#ORD-8009</td>
                            <td>Thế Anh</td>
                            <td>05 Apr, 2026</td>
                            <td>3,200,000₫</td>
                            <td><span class="tag completed">Completed</span></td>
                            <td><a href="#" style="color:var(--black-900);"><i class="fa-solid fa-ellipsis"></i></a></td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>
