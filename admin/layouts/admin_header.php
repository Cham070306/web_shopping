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
    ['page' => 'orders',      'href' => 'orders/index.php',       'icon' => 'fa-bag-shopping',   'label' => 'Orders',     'group' => 'Sales'],
    ['page' => 'inventory',   'href' => 'inventory/index.php',    'icon' => 'fa-warehouse',      'label' => 'Inventory',  'group' => 'Operations'],
    ['page' => 'customers',   'href' => 'customers.php',          'icon' => 'fa-users',          'label' => 'Customers',  'group' => 'System'],
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
    <link rel="stylesheet" href="<?= $base_path ?>assets/admin.css?v=<?= time() ?>">
</head>
<body>

<!-- ═══════════════ SIDEBAR OVERLAY (mobile) ═══════════════════ -->
<div class="adm-sidebar-overlay" id="sidebarOverlay"></div>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<?php $sidebarThemeClass = ($currentPage === 'dashboard') ? 'adm-sidebar-light' : 'adm-sidebar-dark'; ?>
<aside class="adm-sidebar <?= $sidebarThemeClass ?>" id="adminSidebar">
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
        <!-- Hamburger (mobile only) -->
        <button class="adm-hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="adm-breadcrumb">
            Admin / <span><?= htmlspecialchars($breadcrumb ?? ($pageTitle ?? '')) ?></span>
        </div>
        <div class="adm-topbar-right">
            <a href="<?= $base_path ?>../user/index.php">
                <i class="fa-solid fa-store"></i>
                <span> View Store</span>
            </a>
        </div>
    </header>

    <!-- Content -->
    <div class="adm-content">

<script>
(function() {
    const sidebar  = document.getElementById('adminSidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const hamburger = document.getElementById('hamburgerBtn');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    hamburger?.addEventListener('click', openSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Close on nav link click (mobile UX)
    document.querySelectorAll('.adm-nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
})();
</script>
