<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

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