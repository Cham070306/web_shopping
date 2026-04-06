<?php
$currentPage = $currentPage ?? 'dashboard';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
}

body{
    font-family:'Inter',sans-serif;
}

.admin-sidebar{
    position:fixed;
    top:0;
    left:0;
    width:292px;
    height:100vh;
    background:linear-gradient(180deg,#020817 0%, #030b24 100%);
    color:#ffffff;
    padding:22px 18px 20px;
    overflow-y:auto;
    overflow-x:hidden;
}

.admin-sidebar::-webkit-scrollbar{
    width:6px;
}
.admin-sidebar::-webkit-scrollbar-thumb{
    background:#1e293b;
    border-radius:20px;
}

.sidebar-logo{
    display:flex;
    align-items:center;
    gap:14px;
    margin-bottom:22px;
}

.logo-icon{
    width:40px;
    height:40px;
    border-radius:16px;
    background:linear-gradient(135deg,#6d6af8 0%, #2f326f 100%);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    font-weight:800;
    color:#fff;
    box-shadow:0 10px 24px rgba(109,106,248,.35);
    flex-shrink:0;
}

.logo-icon span{
    font-size:14px;
    font-weight:800;
    line-height:1;
}

.logo-text h2{
    font-size:24px;
    line-height:1.1;
    font-weight:800;
    color:#ffffff;
    margin-bottom:4px;
}

.logo-text p{
    font-size:12px;
    color:#8aa0c8;
    font-weight:500;
}

.workspace-card{
    background:linear-gradient(180deg,rgba(17,25,55,.96) 0%, rgba(10,17,41,.96) 100%);
    border:1px solid rgba(255,255,255,.10);
    border-radius:18px;
    padding:18px 16px;
    margin-bottom:26px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.03),
        0 8px 20px rgba(0,0,0,.18);
}

.sidebar-section-label{
    display:block;
    font-size:11px;
    letter-spacing:4px;
    color:#88a0c9;
    text-transform:uppercase;
    margin-bottom:14px;
}

.workspace-body{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}

.workspace-body h4{
    font-size:15px;
    line-height:1.35;
    font-weight:800;
    color:#ffffff;
    margin-bottom:4px;
}

.workspace-body p{
    font-size:13px;
    color:#8ba1c8;
}

.workspace-status{
    padding:7px 12px;
    border-radius:999px;
    background:rgba(16,185,129,.12);
    color:#34d399;
    font-size:12px;
    font-weight:700;
    white-space:nowrap;
}

.menu-group{
    margin-bottom:24px;
}

.menu-title{
    display:block;
    font-size:11px;
    letter-spacing:4px;
    color:#7f96bf;
    text-transform:uppercase;
    margin:0 0 10px 16px;
}

.sidebar-nav{
    display:flex;
    flex-direction:column;
    gap:6px;
}

.sidebar-link{
    display:flex;
    align-items:center;
    gap:14px;
    width:100%;
    min-height:42px;
    padding:12px 16px;
    border-radius:18px;
    color:#ffffff;
    text-decoration:none;
    font-size:15px;
    font-weight:600;
    transition:all .22s ease;
}

.sidebar-link i{
    width:20px;
    min-width:20px;
    font-size:15px;
    text-align:center;
    color:#9fb0cf;
    transition:all .22s ease;
}

.sidebar-link span{
    line-height:1.2;
}

.sidebar-link:hover{
    background:rgba(255,255,255,.05);
    transform:translateX(2px);
}

.sidebar-link.active{
    background:linear-gradient(90deg,rgba(37,48,74,.92) 0%, rgba(31,40,60,.92) 100%);
    box-shadow:0 10px 24px rgba(0,0,0,.18);
}

.sidebar-link.active i,
.sidebar-link.active span{
    color:#ffffff;
}

.sidebar-footer{
    margin-top:14px;
    padding-bottom:6px;
}

.admin-card{
    background:linear-gradient(180deg,rgba(17,25,55,.96) 0%, rgba(10,17,41,.96) 100%);
    border:1px solid rgba(255,255,255,.10);
    border-radius:18px;
    padding:16px;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.03),
        0 8px 20px rgba(0,0,0,.18);
}

.admin-top{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:16px;
}

.admin-avatar{
    width:44px;
    height:44px;
    border-radius:50%;
    background:linear-gradient(180deg,#3a416b 0%, #2e3356 100%);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:21px;
    font-weight:800;
    color:#ffffff;
    flex-shrink:0;
}

.admin-info h4{
    font-size:15px;
    font-weight:800;
    color:#ffffff;
    margin-bottom:4px;
}

.admin-info p{
    font-size:13px;
    color:#8ba1c8;
}

.reset-btn{
    width:100%;
    border:none;
    outline:none;
    cursor:pointer;
    background:#ffffff;
    color:#111827;
    font-size:14px;
    font-weight:700;
    padding:13px 16px;
    border-radius:14px;
    transition:all .2s ease;
}

.reset-btn:hover{
    background:#f3f4f6;
    transform:translateY(-1px);
}

/* content demo */
.admin-main{
    margin-left:292px;
    min-height:100vh;
    background:linear-gradient(180deg,#f6f8fc 0%, #eef2ff 100%);
    padding:28px;
}

@media (max-width: 991px){
    .admin-sidebar{
        position:relative;
        width:100%;
        height:auto;
    }

    .admin-main{
        margin-left:0;
        padding:18px;
    }
}
</style>

<aside class="admin-sidebar">

    <div class="sidebar-logo">
        <div class="logo-icon"><span>3</span></div>
        <div class="logo-text">
            <h2>3legant</h2>
        </div>
    </div>

    <div class="workspace-card">
        <span class="sidebar-section-label">Workspace</span>
        <div class="workspace-body">
            <div>
                <h4>Premium Store<br>Admin</h4>
                <p>Demo data + localStorage</p>
            </div>
            <span class="workspace-status">Online</span>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">Overview</span>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="sidebar-link <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-pie"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">Catalog</span>
        <div class="sidebar-nav">
            <a href="categories.php" class="sidebar-link <?php echo $currentPage == 'categories' ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group"></i>
                <span>Categories</span>
            </a>

            <a href="products.php" class="sidebar-link <?php echo $currentPage == 'products' ? 'active' : ''; ?>">
                <i class="fa-solid fa-box-open"></i>
                <span>Products</span>
            </a>

            <a href="product-detail.php" class="sidebar-link <?php echo $currentPage == 'product-detail' ? 'active' : ''; ?>">
                <i class="fa-solid fa-eye"></i>
                <span>Product Detail</span>
            </a>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">Sales</span>
        <div class="sidebar-nav">
            <a href="orders.php" class="sidebar-link <?php echo $currentPage == 'orders' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bag-shopping"></i>
                <span>Orders</span>
            </a>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">Operations</span>
        <div class="sidebar-nav">
            <a href="inventory.php" class="sidebar-link <?php echo $currentPage == 'inventory' ? 'active' : ''; ?>">
                <i class="fa-solid fa-warehouse"></i>
                <span>Inventory</span>
            </a>

            <a href="customers.php" class="sidebar-link <?php echo $currentPage == 'customers' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i>
                <span>Customers</span>
            </a>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">Growth</span>
        <div class="sidebar-nav">
            <a href="coupons.php" class="sidebar-link <?php echo $currentPage == 'coupons' ? 'active' : ''; ?>">
                <i class="fa-solid fa-tags"></i>
                <span>Coupons</span>
            </a>

            <a href="reports.php" class="sidebar-link <?php echo $currentPage == 'reports' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-column"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>

    <div class="menu-group">
        <span class="menu-title">System</span>
        <div class="sidebar-nav">
            <a href="settings.php" class="sidebar-link <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="admin-card">
            <div class="admin-top">
                <div class="admin-avatar">AD</div>
                <div class="admin-info">
                    <h4>Admin Nguyễn</h4>
                    <p>admin@3elegant.com</p>
                </div>
            </div>
            <button type="button" class="reset-btn">Reset demo data</button>
        </div>
    </div>

</aside>