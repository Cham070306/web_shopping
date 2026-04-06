<?php
session_start();

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Code | 3legant</title>
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
            margin-bottom: 28px; 
            font-size: 16px; 
            line-height: 1.6;
        }

        .otp-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--color-gray-400);
            text-transform: uppercase;
            margin-bottom: 10px;
            display: block;
        }

        .otp-wrap {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }

        .otp-input {
            width: 56px;
            height: 56px;
            border: 1px solid var(--color-gray-200);
            border-radius: 8px;
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            outline: none;
            box-sizing: border-box;
        }

        .otp-input:focus {
            border-color: var(--color-black);
        }

        .helper {
            font-size: 14px;
            color: var(--color-gray-400);
            margin-bottom: 24px;
        }

        .helper a {
            color: var(--color-green);
            text-decoration: none;
            font-weight: 600;
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

            .otp-wrap {
                gap: 8px;
            }

            .otp-input {
                width: 46px;
                height: 46px;
            }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-side-bg"></div>

    <div class="auth-content">
        <div class="form-container">
            <h2>Enter OTP</h2>
            <p class="sub-title">
                A code has been sent to your email.<br>
                Enter it below.
            </p>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="msg error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="msg success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form method="POST" action="../controllers/AuthController.php?action=verify_reset_code" id="otpForm" autocomplete="off">
                <label class="otp-label">OTP Code (6 digits)</label>

                <div class="otp-wrap">
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                    <input type="text" class="otp-input" maxlength="1" inputmode="numeric" autocomplete="off" required>
                </div>

                <input type="hidden" name="otp_code" id="otp_code">

                <p class="helper">
                    Didn’t get a code?
                    <a href="../controllers/AuthController.php?action=resend_reset_code">Resend code</a>
                </p>

                <button type="submit" class="btn-submit">VERIFY CODE</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const otpInputs = document.querySelectorAll('.otp-input');
    const otpHidden = document.getElementById('otp_code');
    const otpForm = document.getElementById('otpForm');

    otpInputs.forEach(input => {
        input.value = '';
    });

    if (otpInputs.length > 0) {
        otpInputs[0].focus();
    }

    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.slice(-1);
            e.target.value = value;

            if (value !== '' && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                if (this.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();

            const pastedData = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);

            if (!pastedData) return;

            otpInputs.forEach(inp => inp.value = '');

            pastedData.split('').forEach((char, i) => {
                if (otpInputs[i]) {
                    otpInputs[i].value = char;
                }
            });

            otpInputs[Math.min(pastedData.length, 5)].focus();
        });
    });

    otpForm.addEventListener('submit', function (e) {
        let code = '';

        otpInputs.forEach(input => {
            code += input.value.trim();
        });

        if (code.length !== 6) {
            e.preventDefault();
            alert('Please enter the full 6-digit code.');
            return;
        }

        otpHidden.value = code;
    });
});
</script>

</body>
</html>