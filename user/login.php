<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | 3legant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-black: #141718; 
            --color-white: #FFFFFF;
            --color-gray-100: #F3F5F7; 
            --color-gray-200: #E8ECEF;
            --color-gray-400: #6C7275; 
            --color-red: #FF5630;
            --color-green: #38CB89; 
            --font-body: 'Inter', sans-serif;
        }

        body { 
            margin: 0; 
            font-family: var(--font-body); 
            background-color: var(--color-white); 
            color: var(--color-black); 
        }
        .auth-wrapper { 
            display: flex; 
            min-height: 100vh;
            width: 100%; 
        }
        
        .auth-side-bg { 
            flex: 1; 
            background-color: var(--color-gray-100); 
            background-image: url('../image/login1.png'); 
            background-position: center; 
            background-repeat: no-repeat; 
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
            padding: 50px 24px; 
        }
        .form-container { 
            max-width: 440px; 
            width: 100%; 
        }

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
        .form-group input[type="email"], .form-group input[type="password"] { 
            border: none; 
            border-bottom: 1px solid var(--color-gray-200); 
            padding: 12px 0; font-size: 16px; 
            outline: none; 
            background: transparent; 
        }
        .form-group input:focus { 
            border-bottom-color: var(--color-black); 
        }

        .form-options { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 32px; 
        }
        .remember-me { 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            font-size: 14px; 
            color: var(--color-gray-400); 
            cursor: pointer; 
        }
        .remember-me input { 
            width: 18px; 
            height: 18px; 
            cursor: pointer; 
            accent-color: var(--color-black); 
        }
        
        .forgot-password { 
            font-size: 14px; 
            font-weight: 600; 
            color: var(--color-black); 
            text-decoration: none; 
        }
        .forgot-password:hover { 
            text-decoration: underline; 
        }

        .btn-submit { 
            width: 100%; 
            padding: 16px; 
            background-color: var(--color-black); 
            color: var(--color-white); 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            font-weight: 600; 
            cursor: pointer; 
        }

        .msg { 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            border: 1px solid; 
        }
        .error { 
            color: var(--color-red); 
            background: #FFF9F8; 
            border-color: var(--color-red); 
        }
        .success { 
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
            <h2>Sign In</h2>
            <p class="sub-title">Don't have an account yet? <a href="register.php">Sign Up</a></p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/AuthController.php?action=login">
                <div class="form-group">
                    <label>Your Email Address</label>
                    <input type="email" name="email" placeholder="Email address" required>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>