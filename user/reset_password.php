<?php
session_start();

if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header("Location: forgot_password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | 3legant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

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
            margin-bottom: 10px; 
            font-size: 16px; 
            line-height: 1.6;
        }

        .otp-note {
            color: var(--color-gray-400);
            margin-bottom: 28px;
            font-size: 14px;
        }

        .form-group { 
            display: flex; 
            flex-direction: column; 
            margin-bottom: 24px;
            position: relative; 
        }

        .form-group label { 
            font-size: 12px; 
            font-weight: 700; 
            color: var(--color-gray-400); 
            margin-bottom: 8px; 
            text-transform: uppercase; 
        }

        .password-input-wrapper {
            position: relative; 
            width: 100%;
            margin-top: 8px; 
        }

        .password-input-wrapper input {
            width: 100%;
            padding: 12px 35px 12px 0;
            border: none;
            border-bottom: 1px solid var(--color-gray-200);
            background: transparent;
            outline: none;
            font-size: 16px;
            display: block;
            box-sizing: border-box;
        }

        .password-input-wrapper input:focus {
            border-bottom-color: var(--color-black);
        }

        .toggle-password {
            position: absolute;
            right: 0;
            top: 50%; 
            transform: translateY(-50%); 
            cursor: pointer;
            color: var(--color-gray-400);
            font-size: 18px;
            z-index: 100; 
            padding: 5px; 
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
            <h2>Set New Password</h2>
            <p class="sub-title">
                Your code has been verified. Create<br>
                your new password.
            </p>
            <p class="otp-note">OTP Code: 6 digits</p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/AuthController.php?action=reset_password">
                <div class="form-group">
                    <label>New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="newPassword" name="new_password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="newPassword"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="confirmPassword" name="confirm_password" required>
                        <i class="fa-regular fa-eye toggle-password" data-target="confirmPassword"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">RESET PASSWORD</button>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.toggle-password').forEach(function(icon) {
    icon.addEventListener('click', function () {
        const targetId = this.getAttribute('data-target');
        const input = document.getElementById(targetId);

        if (input.type === 'password') {
            input.type = 'text';
            this.classList.remove('fa-eye');
            this.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            this.classList.remove('fa-eye-slash');
            this.classList.add('fa-eye');
        }
    });
});
</script>

</body>
</html>