<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | 3legant</title>
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
            background-image: url('../assets/image/login1.png'); 
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
            line-height: 1.6;
        }

        .sub-title a { 
            color: var(--color-black); 
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

        .form-group input[type="email"] { 
            border: none; 
            border-bottom: 1px solid var(--color-gray-200); 
            padding: 12px 0;
            font-size: 16px; 
            outline: none; 
            background: transparent; 
        }

        .form-group input:focus { 
            border-bottom-color: var(--color-black); 
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
            margin-top: 8px;
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

        @media (max-width: 768px) { 
            .auth-wrapper { 
                flex-direction: column; 
            }

            .auth-side-bg { 
                height: 350px; 
                flex: none; 
            }

            .auth-side-bg::after {
                top: 25px; 
                font-size: 25px; 
                font-weight: 600;
            }

            h2 {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-side-bg"></div>

    <div class="auth-content">
        <div class="form-container">
            <h2>Forgot Password</h2>
            <p class="sub-title">
                Enter your email address and we’ll send<br>
                a code to reset your password.<br>
                <a href="login.php">Back to login</a>
            </p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/AuthController.php?action=forgot_password">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="e.g. yourname@email.com" required>
                </div>

                <button type="submit" class="btn-submit">SEND CODE</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>