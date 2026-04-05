<?php 
include "../includes/auth.php"; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | 3legant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --black: #141718;
            --gray-600: #343839;
            --gray-400: #6C7275;
            --gray-200: #E8ECEF;
            --white: #FFFFFF;
        }
        .navbar {
            width: 100%;
            height: 50px;
            background: white;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
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
        .menu a.active, .menu a:hover { 
            color: var(--black); 
        }
        
        .menu a.active { 
            color: var(--black); 
            border-bottom: 2px solid var(--black);
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

        .icons img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            cursor: pointer;
        }
        .icon-link, .cart-wrapper {
            display: flex;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
        }
        .cart-wrapper {
            display: flex !important; 
            align-items: center;
            gap: 5px;
            position: relative;
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
        .cart-wrapper img {
            width: 24px;
            height: 24px;
        }
        .desktop {
            display: flex;
        }

        #menu-toggle, .menu-btn { 
            display: none; 
        }
        #menu-toggle:checked ~ .menu {
            left: 0;
        }
       
        .overlay {
            position: fixed;
            inset: 0;
            background: #1e2223;;
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

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            color: var(--black); 
            background: var(--white);
            -webkit-font-smoothing: antialiased;
            padding-top: 30px;
        }

        .container { 
            max-width: 1120px; 
            margin: 60px auto; 
            padding: 0 20px; 
            min-height: 75vh; 
        }
        .breadcrumb {
            display: none !important; 
        }
        
        .page-header { 
            font-size: 40px; 
            font-weight: 600; 
            text-align: center; 
            margin-bottom: 60px; 
            letter-spacing: -0.5px; 
        }

        .account-layout { 
            display: flex; 
            gap: 60px; 
        }
        .account-main-content { 
            flex: 1; 
        }
        
        .section-title { 
            font-size: 20px; 
            font-weight: 600; 
            margin-bottom: 32px; 
        }

        .form-group { 
            display: flex; 
            flex-direction: column; 
            margin-bottom: 24px; 
        }
        
        .form-group label { 
            font-size: 12px; 
            font-weight: 700; 
            color: var(--gray-400); 
            text-transform: uppercase; 
            margin-bottom: 8px; 
        }

        .form-group input { 
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 16px;
            color: var(--black);
            outline: none;
            transition: border-color 0.2s;
        }

        .form-group input:focus { 
            border-color: var(--black); 
        }
        .form-group input::placeholder { 
            color: #B1B5C3; 
        }

        .display-name-info { 
            font-size: 12px; 
            color: var(--gray-400); 
            margin-top: 8px; 
            line-height: 1.4;
        }

        .btn-save { 
            background: var(--black); 
            color: var(--white); 
            padding: 12px 40px; 
            border: none; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 16px; 
            cursor: pointer; 
            margin-top: 16px;
        }

        .footer { 
            background: var(--black); 
            color: #fff; 
            padding: 80px 0 40px 0; 
        }
        .footercontainer { 
            max-width: 1120px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        .footer-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-bottom: 40px; 
            border-bottom: 1px solid var(--gray-600); 
            margin-bottom: 32px; 
        }
        .footer-brand { 
            display: flex; 
            align-items: center; 
            gap: 20px; 
        }
        .logo-light { 
            font-size: 24px; 
            font-weight: 600; 
        }
        .footer-brand .line { 
            width: 1px; 
            height: 24px; 
            background: var(--gray-600); 
        }
        .footer-nav a { 
            color: #fff; 
            margin-left: 40px; 
            font-size: 14px;
            text-decoration: none;
        }
        
        .footer-bottom { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            font-size: 12px; 
            color: var(--gray-200); 
            text-decoration: none;
        }
        .footer-legal { 
            display: flex; 
            gap: 28px; 
        }
        .footer-legal a { 
            color: #fff; 
            font-weight: 600;
            text-decoration: none; 
        }
        .footer-social { 
            display: flex; 
            gap: 24px; 
        }
        .footer-social a { 
            color: var(--white); 
            font-size: 18px; 
        }

        .alert-box {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.5s ease; 
        }
        .success-alert { 
            background: #E8F9EE; 
            color: #38CB89; 
            border: 1px solid #38CB89; 
        }
        .error-alert { 
            background: #FFF0F0; 
            color: #FF5630; 
            border: 1px solid #FF5630; 
        }

        @media (max-width: 768px) {
            body { 
                padding-top: 20px; 
            }
            .desktop{
                display: none !important;
            }

            .navbar-container {
                padding: 0 28px;
            }
            .menu-btn { 
                display: block; 
                order: 1; 
               
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
                padding-bottom: 5px;
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
                padding-bottom: 10px; 
            }
            .navbar .menu a.active::after { 
                display: none; 
            }
            
            .overlay {
                position: fixed; 
                top: 0; left: 0; 
                width: 100%; 
                height: 100%;
                background: #1b1e1f9a;; 
                display: none; 
                z-index: 10000;
            }
            #menu-toggle:checked ~ .overlay {
                display: block;
            }
            .container { 
                max-width: 312px; 
                margin: 30px; 
                padding: 0 20px; 
            }
            .breadcrumb {
                display: flex !important;
                margin-bottom: 25px;
                margin-top: -10px;
                margin-left: -18px;
                display: flex;
            }
            .back-link {
                text-decoration: none;
                color: #6C7275;  
                font-size: 14px;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 8px; 
                transition: 0.3s;
            }

            .back-link:hover {
                color: #1d2021; 
            }
            .account-layout { 
                flex-direction: column;
                gap: 10px; 
                align-items: center;
            }
            .account-main-content {
                width: 100%;
            }
            .form-group { 
                display: flex;
                flex-direction: column;
                margin-bottom: 24px; 
                margin-top: 1px;
            }

            .form-group label {
                font-size: 12px;
                font-weight: 700;
                margin-bottom: 8px;
                color: var(--gray-400);
                text-transform: uppercase;
            }

            .form-group input { 
                padding: 12px 16px;
                font-size: 16px;
                border: 1px solid var(--gray-200);
                border-radius: 8px;
                width: 100%;
                box-sizing: border-box;
            }
            .section-title {
                font-size: 20px;
                font-weight: 600;
                margin-bottom: 24px;
            }
            .display-name-info {
                font-size: 12px;
                color: var(--gray-400);
                margin-top: 8px;
                line-height: 1.4;
            }
        
            .page-header {
                font-size: 28px;
                margin-bottom: 30px;
            }
            .footer .footercontainer {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 15px; 
                padding-top: 10px;
                padding-bottom: 35px;
            }
            .footer-top { 
                display: flex;
                flex-direction: column;
                text-align: center;  
                gap: 40px;
                width: 100%; 
                
            } 
            .footer-top .line {
                width: 24px;    
                height: 2px;  
                margin: 10px;
            }
            .footer-bottom { 
                flex-direction: column; 
                gap: 30px; 
                text-align: center; 
            }
            .footer-brand {
                flex-direction: column;
                gap: 8px;
            }

            .footer-nav { 
                display: flex; 
                flex-direction: column; 
                gap: 32px; 
            }
            .footer-nav a { 
                margin: 0; 
            }
            .footer-legal { 
                flex-direction: column; 
                display: flex;
                gap: 32px; 
            }
            .legal-links {
                display: flex;
                justify-content: center;
                flex-wrap: wrap; 
                gap: 32px; 
                order: -2;
            }
            .legal-links a {
                margin: 0;
                color: var(--white);
                font-weight: 600;
                gap:28px; 
            
            }
            .footer-social {
                display: flex;
                justify-content: center;
                gap: 24px;
                width: 100%;
                order: -1;
            
            }
            .footer-social a {
                font-size: 20px;
            }

        }
    </style>
</head>

<body>
    <nav class="navbar">
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
                <a href="shop.php" class="<?= ($current_page == 'shop.php') ? 'active' : '' ?>">Shop</a>
                <a href="product.php" class="<?= ($current_page == 'product.php') ? 'active' : '' ?>">Product</a>
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
                            if(isset($_SESSION['cart'])) {
                                foreach($_SESSION['cart'] as $item) { $total += $item['quantity']; }
                            }
                            echo $total; 
                        ?>
                    </span>
                </a>
            </div>
            <label for="menu-toggle" class="overlay"></label>
        </div>
    </nav>

    <div class="container">
        <div class="breadcrumb">
            <a href="javascript:history.back()" class="back-link">
                <i class="fa-solid fa-chevron-left"></i> back
            </a>
        </div>
        <h1 class="page-header">My Account</h1>

        <?php 
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        include "../includes/auth.php"; 
        $user = $_SESSION['user'] ?? [];
        $user_name = $user['name'] ?? '';
        $user_email = $user['email'] ?? '';
        ?>

        <div id="notification-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-box success-alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #FFF0F0; color: #FF5630; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #FF5630; display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <div class="account-layout">    
            <?php include "../includes/account_sidebar.php"; ?>

            <div class="account-main-content">
               <form action="../controllers/AuthController.php?action=update_full" method="POST" enctype="multipart/form-data">
                    <h2 class="section-title">Account Details</h2>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Display Name *</label>
                        <input type="text" name="display_name" value="<?= htmlspecialchars($user_name) ?>" required>
                        <span class="display-name-info">This will be how your name will be displayed in the account section and in reviews</span>
                    </div>

                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user_email) ?>" required>
                    </div>

                    <h2 class="section-title" style="margin-top: 48px;">Password</h2>
                    
                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" name="old_password" placeholder="Old password">
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="New password">
                    </div>
                    
                    <div class="form-group">
                        <label>Repeat New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat new password">
                    </div>

                    <button type="submit" class="btn-save">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
    <div class="footercontainer">
        <div class="footer-top">
            <div class="footer-brand">
                <span class="logo-light">3legant.</span>
                <span class="line"></span>
                <span class="slogan">Gift & Decoration Store</span>
            </div>
            <nav class="footer-nav">
                <a href="index.php">Home</a>
                <a href="shop.php">Shop</a>
                <a href="Product.php">Product</a>
                <a href="blog.php">Blog</a>
                <a href="contact.php">Contact Us</a>
            </nav>
        </div>
        
        <div class="footer-bottom">
            <div class="footer-legal">
                <span>Copyright © 2026 3legant. All rights reserved</span>
                <div class="legal-links">
                    <a href="Privacy Policy">Privacy Policy</a>
                    <a href="Terms of Use">Terms of Use</a>
                </div>
            </div>
            <div class="footer-social">
                <a href="Instagram"><img src="../assets/image/instagram.png" alt="Instagram"></a>  
                <a href="Facebook"><img src="../assets/image/Vector 2998.png" alt="Facebook"></a>
                <a href="Youtube"><img src="../assets/image/youtube.png" alt="Youtube"></a>
            </div>
        </div>
    </div>
</footer>


    <script>
        document.getElementById('avatar-upload').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('avatar-preview').src = event.target.result;
                };
                reader.readAsDataURL(this.files[0]);
                document.getElementById('avatar-form').submit();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert-box');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0'; 
                    setTimeout(function() {
                        alert.remove(); 
                    }, 500);
                }, 1000); 
            });
        });
        
    </script>

</body>
</html>