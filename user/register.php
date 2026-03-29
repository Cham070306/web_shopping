<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | 3legant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-black: #141718; 
            --color-white: #FFFFFF;
            --color-gray-100: #F3F5F7; 
            --color-gray-200: #E8ECEF;
            --color-gray-400: #6C7275; 
            --color-green: #38CB89;
            --color-red: #FF5630;
        }
        body { 
            margin: 0; 
            font-family: 'Inter', sans-serif; 
            background: var(--color-white); }
        .auth-wrapper { 
            display: flex; 
            min-height: 100vh; }
        
        .auth-side-bg { 
            flex: 1; 
            background-color: var(--color-gray-100); 
            background-image: url('../image/login1.png'); 
            background-position: center; 
            background-size: cover; 
            position: relative;
        }
        .auth-side-bg::after {
            content: "3legant"; 
            position: absolute; 
            top: 40px; 
            left: 50%;
            transform: translateX(-50%); 
            font-size: 24px; 
            font-weight: 800;
        }

        .auth-content { 
            flex: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 40px 24px; 
        }
        .form-container { 
            max-width: 440px; 
            width: 100%; }
        
        h2 { 
            font-size: 40px; 
            margin: 0 0 12px 0; 
            font-weight: 700; 
            letter-spacing: -1px; 
        }
        .sub-title { 
            color: var(--color-gray-400); 
            margin-bottom: 32px; 
            font-size: 16px; 
        }
        .sub-title a { 
            color: var(--color-green); 
            text-decoration: none; 
            font-weight: 600; 
        }

        .form-group { 
            display: flex; 
            flex-direction: column; 
            margin-bottom: 24px; 
        }
        .form-group label { 
            font-size: 12px; 
            font-weight: 700; 
            color: var(--color-gray-400); 
            margin-bottom: 8px; 
            text-transform: uppercase; 
        }
        .form-group input { 
            border: none; 
            border-bottom: 1px solid var(--color-gray-200); 
            padding: 12px 0; font-size: 16px; outline: none; 
            background: transparent; }
        .form-group input:focus { 
            border-bottom-color: var(--color-black); 
        }

        .policy-row { 
            display: flex; 
            align-items: flex-start; 
            gap: 12px; 
            margin-bottom: 32px; 
            color: var(--color-gray-400); 
            font-size: 14px; 
        }
        .policy-row input { 
            width: 18px; 
            height: 18px; 
            accent-color: var(--color-black); 
            margin-top: 2px; 
        }
        .policy-row b { 
            color: var(--color-black); 
            cursor: pointer; 
        }

        .btn-submit { 
            width: 100%; 
            padding: 16px; 
            background-color: var(--color-black); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
        }
        .alert { 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 14px; border: 1px solid; }
        .alert-error { 
            color: var(--color-red); 
            background: #FFF9F8; 
            border-color: var(--color-red); }
        .alert-success { 
            color: var(--color-green); 
            background: #F4FDF9; 
            border-color: var(--color-green); 
        }

        @media (max-width: 900px) { 
            .auth-wrapper { 
                flex-direction: column; 
            }
            .auth-side-bg { 
                height: 300px; 
                flex: none; 
            }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-side-bg"></div>

    <div class="auth-content">
        <div class="form-container">
            <h2>Sign Up</h2>
            <p class="sub-title">Already have an account? <a href="login.php">Sign In</a></p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/AuthController.php?action=register">
                <div class="form-group">
                    <label>Your Name</label>
                    <input type="text" name="name" placeholder="Your name" required>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Email address" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm password" required>
                </div>

                <div class="policy-row">
                    <input type="checkbox" name="policy" required>
                    <span>I agree with <b>Privacy Policy</b> and <b>Terms of Use</b></span>
                </div>

                <button type="submit" class="btn-submit">Sign Up</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>