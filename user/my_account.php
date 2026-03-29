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

        /* --- Footer --- */
        .main-footer { 
            background: var(--black); 
            color: var(--white); 
            padding: 80px 0 40px; 
            margin-top: 100px; 
        }
        .footer-container { 
            max-width: 1120px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }
        
        .footer-top { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-bottom: 40px; 
            border-bottom: 1px solid #343839; 
        }
        
        .footer-logo { 
            font-size: 24px; 
            font-weight: 600; 
        }
        .footer-logo span { 
            color: var(--gray-400); 
        }
        
        .footer-nav { 
            display: flex; 
            gap: 40px; 
        }
        .footer-nav a { 
            color: var(--gray-200); 
            text-decoration: none; 
            font-size: 14px; 
        }

        .footer-bottom { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding-top: 32px;
            font-size: 12px;
        }

        .footer-legal { 
            display: flex; 
            gap: 28px; 
            margin-left: 28px; 
        }
        .footer-legal a { 
            color: var(--white); 
            text-decoration: none; 
            font-weight: 600; 
        }

        .social-icons { 
            display: flex; 
            gap: 24px; 
        }
        .social-icons a { 
            color: var(--white); 
            font-size: 18px; 
            text-decoration: none; 
            opacity: 0.8; 
            transition: 0.3s; 
        }
        .social-icons a:hover { 
            color: var(--gray-400);  
        }

        @media (max-width: 768px) {
            .account-layout { 
                flex-direction: column; 
                gap: 40px; 
            }
            .footer-top { 
                flex-direction: column; 
                gap: 32px; 
                text-align: center; 
            }
            .footer-bottom { 
                flex-direction: column-reverse; 
                gap: 24px; 
                text-align: center; 
            }
            .footer-legal { 
                margin-left: 0; 
                justify-content: center; 
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
                <form action="../controllers/AuthController.php?action=update_profile" method="POST">
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

    <footer class="main-footer">
        <div class="footer-container">
            <div class="footer-top">
                <div class="footer-logo">3legant<span>.</span></div>
                <nav class="footer-nav">
                    <a href="index.php">Home</a>
                    <a href="shop.php">Shop</a>
                    <a href="product.php">Product</a>
                    <a href="blog.php">Blog</a>
                    <a href="contact.php">Contact Us</a>
                </nav>
            </div>
            
            <div class="footer-bottom">
                <div style="display: flex; align-items: center; flex-wrap: wrap;">
                    <p style="color: var(--gray-200); margin: 0;">Copyright © 2026 3legant. All rights reserved</p>
                    <div class="footer-legal">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Use</a>
                    </div>
                </div>
                
                <div class="social-icons">
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>