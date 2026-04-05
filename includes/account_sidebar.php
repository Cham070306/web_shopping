<?php $current_page = basename($_SERVER['PHP_SELF']); ?>
<style>
    .sidebar-wrapper { 
        width: 312px; 
        background: #F3F5F7; 
        border-radius: 16px; 
        padding: 40px 16px; 
        height: fit-content;
        flex-shrink: 0; 
    }

    .avatar-section { 
        text-align: center; 
        margin-bottom: 32px; 
        display: block !important; 
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
    }
    .camera-icon img {
        width: 20px; 
        height: 20px;
        object-fit: contain;

    }

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

    .desktop-nav a.active { 
        color: #141718; 
        border-bottom: 1.5px solid #141718; 
    }

    .mobile-dropdown { display: none; }

    @media (max-width: 768px) {
        .sidebar-wrapper { 
            width: 100%; 
            padding: 40px 16px; 
            margin-bottom: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .avatar-section { 
            margin-bottom: 24px;
        }

        .desktop-nav { 
            display: none; 
        }

        .mobile-dropdown { 
            display: block; 
            width: 100%; 
        }

        .mobile-dropdown select {
            width: 100%; 
            padding: 12px 16px; 
            font-size: 16px; 
            font-weight: 600;
            border: 1px solid #CBCBCB; 
            border-radius: 8px;
            background-color: #fff;
            outline: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23141718' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 18px;
        }
    }
</style>

<aside class="sidebar-wrapper">
    <div class="avatar-section">
        <div class="avatar-container">
            <img src="../assets/uploads/<?= htmlspecialchars($user_avatar) ?>" id="avatar-preview" alt="Avatar">
            <form id="avatar-form" action="../controllers/AuthController.php?action=update_full" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_page" value="<?= basename($_SERVER['PHP_SELF']) ?>">
                
                <label for="avatar-upload" class="camera-icon">
                    <img src="../assets/image/camera.png" alt="Camera" style="width: 16px; height: 16px;">
                </label>
                <input type="file" id="avatar-upload" name="avatar" hidden accept="image/*">
            </form>
        </div>
        <h3 class="user-name"><?= htmlspecialchars($user_name) ?></h3>
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
        </select>
    </div>
</aside>
