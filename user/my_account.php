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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --black: #141718;
            --gray-600: #343839;
            --gray-400: #6C7275;
            --gray-200: #E8ECEF;
            --white: #FFFFFF;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            color: var(--black); 
            background: var(--white);
            -webkit-font-smoothing: antialiased;
        }

        .container { 
            max-width: 1120px; 
            margin: 60px auto; 
            padding: 0 20px; 
            min-height: 75vh; 
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

        @media (max-width: 768px) {
           
            .account-layout { 
                flex-direction: column; 
                gap: 40px; 
            }
            
            .footer .footercontainer {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 32px; 
                padding-top: 40px;
                padding-bottom: 40px;
            }
            .footer-top { 
                display: flex;
                flex-direction: column;
                text-align: center;  
                gap: 30px;
                width: 100%;
            }
            .footer-top .line {
                width: 24px;    
                height: 1px;  
                margin: 0;
            }

             .footer-bottom { 
                flex-direction: column; 
                gap: 25px; 
                text-align: center; 
            }
            .footer-brand {
                flex-direction: column;
                gap: 8px;
            }

            .footer-nav { 
                display: flex; 
                flex-direction: column; 
                gap: 30px; 
            }
            .footer-nav a { 
                margin: 0; 
            }
            .footer-legal { 
                flex-direction: column; 
                display: flex;
                gap: 25px; 
            }
            .legal-links {
                display: flex;
                justify-content: center;
                flex-wrap: wrap; 
                gap: 20px; 
                order: -2;
            }
            .legal-links a {
                margin: 0;
                color: var(--white);
                font-weight: 500;
            
            } 

            .footer-social {
                display: flex;
                justify-content: center;
                gap: 24px;
                width: 100%;
                order: -1;
            
            }
            .footer-social a {
                font-size: 18px;
            }

        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="page-header">My Account</h1>
        
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
                        <input type="text" name="display_name" value="<?= htmlspecialchars($userData['display_name'] ?? $user_name) ?>" required>
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
                    <a href="product.php">Product</a>
                    <a href="blog.php">Blog</a>
                    <a href="contact.php">Contact Us</a>
                </nav>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-legal">
                    <span>Copyright © 2026 3legant. All rights reserved</span>
                    <div class="legal-links">
                        <a href="Privacy Polic">Privacy Policy</a>
                        <a href="Terms of Use">Terms of Use</a>
                    </div>
                </div>
                
                <div class="footer-social">
                    <a href="instagram "><i class="fa-brands fa-instagram"></i></a>  
                    <a href="facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href=" youtube "><i class="fa-brands fa-youtube"></i></a>
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
            const form = document.getElementById('avatar-form');
            if (form) {
                form.submit();
            } else {
                alert("Lỗi: Không tìm thấy thẻ form có id='avatar-form'");
            }
        }
    });
</script>

</body>
</html>