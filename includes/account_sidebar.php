<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<style>
    .sidebar-wrapper { 
        width: 260px; 
        background: #F3F5F7; 
        border-radius: 16px; 
        padding: 40px 16px; 
        height: fit-content; 
    }

    .avatar-section { 
        text-align: center; 
        margin-bottom: 32px; 
    }

    .avatar-container {
        position: relative; 
        width: 82px; 
        height: 82px; 
        margin: 0 auto 12px;
    }

    .avatar-container img {
        width: 100%; 
        height: 100%; 
        border-radius: 50%; 
        object-fit: cover;
        border: 2px solid #fff;
    }

    .camera-icon {
        position: absolute; 
        bottom: 2px; 
        right: 2px; 
        background: #141718; 
        color: white; 
        border-radius: 50%; 
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white; 
        font-size: 12px; 
        cursor: pointer;
        transition: 0.3s;
    }

    .camera-icon:hover { background: #343839; }

    .user-name { 
        font-size: 20px; 
        font-weight: 600; 
        color: #141718;
        margin: 0;
    }

    .desktop-nav { 
        display: flex; 
        flex-direction: column; 
        gap: 4px; 
    }

    .desktop-nav a { 
        padding: 12px 0; 
        text-decoration: none; 
        font-weight: 600; 
        font-size: 16px;
        color: #6C7275; 
        border-bottom: 1px solid transparent;
        transition: all 0.3s ease;
    }

    .desktop-nav a:hover { color: #141718; }
    .desktop-nav a.active { 
        color: #141718; 
        border-bottom: 1.5px solid #141718; 
    }

    .mobile-dropdown { display: none; }

    @media (max-width: 768px) {
        .sidebar-wrapper { 
            width: 100%; 
            background: transparent; 
            padding: 0; margin-bottom: 40px; 
        }
        .desktop-nav, .avatar-section { 
            display: none; }
        .mobile-dropdown { 
            display: block; 
        }
        .mobile-dropdown select {
            width: 100%; 
            padding: 16px; 
            font-size: 16px; 
            font-weight: 600; 
            color: #141718;
            border: 2px solid #E8ECEF; 
            border-radius: 8px; 
            background-color: #fff;
            appearance: none; 
            outline: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23141718' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat; 
            background-position: right 16px center;
        }
    }
</style>

<aside class="sidebar-wrapper">
    <div class="avatar-section">
        <div class="avatar-container">
            <img src="<?= !empty($_SESSION['user']['avatar']) ? '../uploads/'.$_SESSION['user']['avatar'] : '../assets/images/default-avatar.jpg' ?>" alt="Avatar">
            <label for="avatar-upload" class="camera-icon">📷</label>
            <input type="file" id="avatar-upload" hidden>
        </div>
        <h3 class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Guest User') ?></h3>
    </div>

    <nav class="desktop-nav">
        <a href="my_account.php" class="<?= $current_page == 'my_account.php' ? 'active' : '' ?>">Account</a>
        <a href="my_address.php" class="<?= $current_page == 'my_address.php' ? 'active' : '' ?>">Address</a>
        <a href="my_orders.php" class="<?= $current_page == 'my_orders.php' ? 'active' : '' ?>">Orders</a>
        <a href="wishlist.php" class="<?= $current_page == 'wishlist.php' ? 'active' : '' ?>">Wishlist</a>
        <a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">Contact Us</a>
        <a href="../controllers/AuthController.php?action=logout" style="color: #FF5630; border: none; margin-top: 20px;">Log Out</a>
    </nav>

    <div class="mobile-dropdown">
        <select onchange="window.location.href=this.value;">
            <option value="my_account.php" <?= $current_page == 'my_account.php' ? 'selected' : '' ?>>Account</option>
            <option value="my_address.php" <?= $current_page == 'my_address.php' ? 'selected' : '' ?>>Address</option>
            <option value="my_orders.php" <?= $current_page == 'my_orders.php' ? 'selected' : '' ?>>Orders</option>
            <option value="wishlist.php" <?= $current_page == 'wishlist.php' ? 'selected' : '' ?>>Wishlist</option>
            <option value="contact.php" <?= $current_page == 'contact.php' ? 'selected' : '' ?>>Contact Us</option>
            <option value="../controllers/AuthController.php?action=logout">Log Out</option>
        </select>
    </div>
</aside>