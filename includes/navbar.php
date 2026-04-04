<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
html, body {
    margin: 0;
    padding: 0;
}

/* Topbar trên cùng */
.topbar {
    position: fixed;
    top: 0;
    width: 100%;
    height: 40px;
    background: #141718;
    color: white;
    z-index: 10000;

    display: flex;
    align-items: center;
    justify-content: center;
}

/* Navbar nằm dưới topbar */
.navbar {
    position: fixed;
    top: -10px; /* 👈 QUAN TRỌNG */
    padding-top: 60px; /* 👈 TĂNG PHẦN TRÊN */
    padding-bottom: 10px;
    width: 100%;
    background: white;
    z-index: 9999;
}

/* Đẩy nội dung xuống */
body {
    padding-top: 70px; /* 40 + navbar */
}
</style>
<nav class="navbar">
    <div class="container navbar-container">

        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-btn">
            ☰
        </label>

        <div class="logo">3legant.</div>

        <div class="menu">
            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a>
            <a href="shop.php" class="<?= ($current_page == 'shop.php') ? 'active' : '' ?>">Shop</a>
            <a href="product.php" class="<?= ($current_page == 'product.php') ? 'active' : '' ?>">Product</a>
            <a href="contact.php" class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>">Contact Us</a>
        </div>

        <div class="icons">
            <i class="fa-solid fa-magnifying-glass"></i>
            <i class="fa-regular fa-user"></i>
            <i class="fa-solid fa-bag-shopping"></i>
        </div>

    </div>
</nav>