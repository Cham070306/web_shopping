<?php
/**
 * Admin Shared Header
 * Usage: include at the very top of every admin page AFTER session + auth check.
 * Variables to set before including:
 *   $currentPage  = 'dashboard' | 'categories' | 'products' | ...
 *   $pageTitle    = 'Page Title'
 *   $breadcrumb   = 'Section / Page'
 *   $base_path    = relative path to admin root (e.g. '../' or '')
 */
$_admin_user  = $_SESSION['user'] ?? [];
$_admin_name  = $_admin_user['name'] ?? 'Admin';
$_admin_email = $_admin_user['email'] ?? '';

$_nav_items = [
    ['page' => 'dashboard',   'href' => 'dashboard.php',          'icon' => 'fa-chart-pie',      'label' => 'Dashboard',  'group' => 'Overview'],
    ['page' => 'categories',  'href' => 'categories/index.php',   'icon' => 'fa-layer-group',    'label' => 'Categories', 'group' => 'Catalog'],
    ['page' => 'products',    'href' => 'products/index.php',     'icon' => 'fa-box-open',       'label' => 'Products',   'group' => 'Catalog'],
    ['page' => 'orders',      'href' => '#',                      'icon' => 'fa-bag-shopping',   'label' => 'Orders',     'group' => 'Sales'],
    ['page' => 'customers',   'href' => '#',                      'icon' => 'fa-users',          'label' => 'Customers',  'group' => 'Sales'],
    ['page' => 'store',       'href' => '../user/index.php',      'icon' => 'fa-store',          'label' => 'View Store', 'group' => 'System'],
    ['page' => 'logout',      'href' => '../controllers/LogoutController.php', 'icon' => 'fa-arrow-right-from-bracket', 'label' => 'Logout', 'group' => 'System'],
];

// Group nav items
$_nav_groups = [];
foreach ($_nav_items as $item) {
    $_nav_groups[$item['group']][] = $item;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | 3legant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_path ?>assets/admin.css">
</head>
<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<?php $sidebarThemeClass = ($currentPage === 'dashboard') ? 'adm-sidebar-light' : 'adm-sidebar-dark'; ?>
<aside class="adm-sidebar <?= $sidebarThemeClass ?>">
    <a href="<?= $base_path ?>../user/index.php" class="adm-sidebar-logo">
        3legant.<span>Admin</span>
    </a>

    <nav class="adm-nav">
        <?php
        $prev_group = null;
        foreach ($_nav_items as $item):
            if ($item['group'] !== $prev_group):
                $prev_group = $item['group'];
        ?>
            <span class="adm-nav-label"><?= $item['group'] ?></span>
        <?php endif; ?>
            <a href="<?= $base_path . $item['href'] ?>"
               class="adm-nav-link <?= ($currentPage ?? '') === $item['page'] ? 'active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="adm-sidebar-footer">
        <div class="adm-user-card">
            <div class="adm-avatar"><?= strtoupper(substr($_admin_name, 0, 1)) ?></div>
            <div class="adm-user-info">
                <p><?= htmlspecialchars($_admin_name) ?></p>
                <p><?= htmlspecialchars($_admin_email) ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- ═══════════════ MAIN ═══════════════════ -->
<div class="adm-main">
    <!-- Topbar -->
    <header class="adm-topbar">
        <div class="adm-breadcrumb">
            Admin / <span><?= htmlspecialchars($breadcrumb ?? ($pageTitle ?? '')) ?></span>
        </div>
        <div class="adm-topbar-right">
            <a href="<?= $base_path ?>../user/index.php"><i class="fa-solid fa-store"></i> View Store</a>
            <a href="<?= $base_path ?>../controllers/LogoutController.php" style="color: #FF3333;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </header>

    <!-- Content -->
    <div class="adm-content">
