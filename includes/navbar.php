<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
:root {
    --black: #141718;
    --gray-400: #6C7275;
    --white: #FFFFFF;
}

.navbar {
    width: 100%;
    height: 72px;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 9999;
    transition: all 0.3s ease;
}

.navbar.scrolled {
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 8px 24px rgba(20, 23, 24, 0.08);
    border-bottom: 1px solid rgba(232, 236, 239, 0.8);
}

.navbar-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 20px;
}

.logo {
    font-size: 24px;
    font-weight: 600;
    color: var(--black);
    text-decoration: none;
    line-height: 1;
}

.menu {
    display: flex;
    gap: 40px;
}

.menu-header {
    display: none !important;
}

.menu a {
    font-size: 14px;
    font-weight: 500;
    color: var(--gray-400);
    text-decoration: none;
    padding: 8px 0;
    border-bottom: 2px solid transparent;
    transition: 0.2s;
}

.menu a:hover {
    color: var(--black);
}

.menu a.active {
    color: var(--black);
    font-weight: 600;
    border-bottom: 2px solid var(--black);
}

/* ── Mega Menu Dropdown ── */
.nav-has-dropdown {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
    padding: 8px 0;
}
.nav-has-dropdown > a {
    padding: 0;
    border-bottom: none !important;
}
.dropdown-icon {
    font-size: 10px;
    transition: 0.3s;
    color: var(--gray-400);
}
.nav-has-dropdown:hover .dropdown-icon {
    transform: rotate(180deg);
    color: var(--black);
}
.nav-dropdown {
    position: absolute;
    top: 100%;
    left: -20px;
    background: #fff;
    min-width: 240px; /* Slightly wider to fit Accessories & Decor on one line */
    box-shadow: 0 16px 40px rgba(0,0,0,0.08);
    border-radius: 8px;
    padding: 12px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(15px);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 10000;
}
/* Show dropdown on hover */
.nav-has-dropdown:hover .nav-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}
.nav-dropdown a {
    display: block !important;
    padding: 10px 24px !important;
    border-bottom: none !important;
    color: var(--gray-400) !important;
    font-weight: 500 !important;
    white-space: nowrap; /* Prevent wrapping */
    transition: all 0.2s;
}
.nav-dropdown a:hover {
    color: var(--black) !important;
    background: #F3F5F7;
    padding-left: 28px !important; /* Nice hover indent effect */
}

.icons {
    display: flex;
    align-items: center;
    gap: 16px;
}

.icons a {
    text-decoration: none;
    display: flex;
    align-items: center;
}

.icon-link,
.cart-wrapper {
    display: flex;
    align-items: center;
    text-decoration: none;
    cursor: pointer;
}

.cart-wrapper {
    gap: 5px;
    position: relative;
}

.icons img {
    width: 20px;
    height: 20px;
    object-fit: contain;
    transition: transform 0.2s ease;
}

.icons a:hover img {
    transform: scale(1.08);
}

.cart-wrapper img {
    width: 24px;
    height: 24px;
}

.cart-badge {
    background-color: var(--black);
    color: white;
    font-size: 12px;
    font-weight: 700;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.desktop {
    display: flex;
}

#menu-toggle,
.menu-btn {
    display: none;
}

#menu-toggle:checked ~ .menu {
    left: 0;
}

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(27, 30, 31, 0.6);
    display: none;
    z-index: 10000;
}

#menu-toggle:checked ~ .overlay {
    display: block;
}

.menu-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    font-weight: 600;
}

.close-btn {
    font-size: 22px;
    cursor: pointer;
}

@media (max-width: 768px) {
    .desktop {
        display: none !important;
    }

    .navbar {
        height: 64px;
        background: rgba(255, 255, 255, 0.95);
    }

    .navbar-container {
        padding: 0 28px;
    }

    .menu-btn {
        display: block;
        order: 1;
        cursor: pointer;
    }

    .menu-btn img {
        width: 20px;
        height: 20px;
        object-fit: contain;
    }

    .navbar .logo {
        order: 2;
        flex: 1;
        font-size: 25px;
        margin-left: 10px;
        padding-bottom: 2px;
    }

    .navbar .icons {
        order: 3;
        gap: 10px;
    }

    .navbar .menu {
        position: fixed;
        top: 0;
        left: -100%;
        width: 60%;
        height: 100%;
        background: white;
        flex-direction: column;
        padding: 24px;
        gap: 0;
        transition: 0.3s ease-in-out;
        box-shadow: 2px 0 10px #3d4243;
        z-index: 10001;
        display: flex;
        box-sizing: border-box;
    }

    .menu-header {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        font-size: 18px;
        font-weight: 600;
        color: var(--black);
    }

    .navbar .menu a {
        font-size: 16px;
        border-bottom: 1px solid #d1d1d1;
        padding: 14px 0;
    }

    .navbar .menu a.active {
        border-bottom: 1px solid var(--black);
    }

    .navbar .menu a.active::after {
        display: none;
    }
}
</style>

<nav class="navbar" id="mainNavbar">
    <div class="navbar-container">
        <input type="checkbox" id="menu-toggle">

        <label for="menu-toggle" class="menu-btn">
            <img src="../assets/image/menu.png" alt="menu">
        </label>

        <a href="index.php" class="logo">3legant.</a>

        <div class="menu">
            <div class="menu-header">
                <span>3Elegant</span>
                <label for="menu-toggle" class="close-btn">✕</label>
            </div>

            <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a>
            
            <a href="shop.php" class="<?= ($current_page == 'shop.php' && !isset($_GET['cat'])) ? 'active' : '' ?>">Shop</a>
            
            <!-- Dropdown for Product/Categories -->
            <div class="nav-has-dropdown">
                <a href="javascript:void(0)" class="<?= (isset($_GET['cat']) || $current_page == 'product_detail.php') ? 'active' : '' ?>">Product</a>
                <i class="fa-solid fa-chevron-down dropdown-icon"></i>
                <div class="nav-dropdown">
                    <a href="shop.php">All Products</a>
                    <a href="shop.php?cat=living-room">Living Room</a>
                    <a href="shop.php?cat=bedroom">Bedroom</a>
                    <a href="shop.php?cat=kitchen">Kitchen</a>
                    <a href="shop.php?cat=dining-room">Dining Room</a>
                    <a href="shop.php?cat=outdoor">Outdoor</a>
                    <a href="shop.php?cat=decor">Accessories & Decor</a>
                </div>
            </div>

            <a href="contact.php" class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>">Contact Us</a>
        </div>

        <div class="icons">
            <a href="shop.php" class="icon-link desktop">
                <img src="../assets/image/search 02.png" alt="search">
            </a>

            <a href="my_account.php" class="icon-link desktop">
                <img src="../assets/image/Vector1.png" alt="account">
            </a>

            <a href="cart.php" class="cart-wrapper">
                <img src="../assets/image/shopping bag.png" alt="bag">
                <span id="cart-count" class="cart-badge">
                    <?php
                        $total = 0;
                        if (isset($_SESSION['cart'])) {
                            foreach ($_SESSION['cart'] as $item) {
                                $total += $item['quantity'];
                            }
                        }
                        echo $total;
                    ?>
                </span>
            </a>
        </div>

        <label for="menu-toggle" class="overlay"></label>
    </div>
</nav>

<script>
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('mainNavbar');

    if (window.scrollY > 10) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});
</script>