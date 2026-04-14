<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "../includes/auth.php";
?>
<?php include '../includes/header.php'; ?>
<?php include "../includes/navbar.php"; ?>
<style>
        :root {
            --black: #141718;
            --gray-600: #343839;
            --gray-400: #6C7275;
            --gray-200: #E8ECEF;
            --white: #FFFFFF;
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
            padding-bottom: 80px;
        }

        .address-content {
            flex: 1;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .address-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .address-card {
            border: 1px solid var(--gray-400);
            border-radius: 8px;
            padding: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .edit-btn {
            color: var(--gray-400);
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .edit-btn:hover {
            color: var(--black);
        }

        .address-info strong {
            display: block;
            font-size: 16px;
            margin-bottom: 4px;
            min-height: 20px;
        }

        .address-info span {
            display: block;
            font-size: 14px;
            color: var(--gray-400);
            min-height: 20px;
        }

        .address-info p {
            margin: 12px 0 0;
            color: var(--gray-400);
            font-size: 14px;
            line-height: 22px;
            min-height: 44px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 32px;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            position: relative;
        }

        .modal-header {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: var(--gray-400);
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 700;
            color: var(--gray-400);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .form-group input {
            padding: 10px 14px;
            border: 1px solid var(--gray-200);
            border-radius: 6px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--black);
        }

        .btn-save {
            background: var(--black);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 40px;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            margin-top: 10px;
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
            .container {
                width: 100%;
                max-width: 100%;
                margin: 24px auto 40px;
                padding: 0 24px;
                box-sizing: border-box;
            }

            .breadcrumb {
                display: flex !important;
                margin: 0 0 20px 0;
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
                gap: 24px;
                align-items: stretch;
            }

            body {
                padding-top: 20px;
            }

            .desktop {
                display: none !important;
            }

            .navbar-container {
                padding: 0 20px;
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
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: #1b1e1f9a;
                display: none;
                z-index: 10000;
            }

            #menu-toggle:checked~.overlay {
                display: block;
            }

            .page-header {
                font-size: 36px;
                margin-bottom: 36px;
                text-align: center;
            }

            .address-content {
                width: 100%;
            }

            .address-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .address-card {
                width: 100%;
                box-sizing: border-box;
            }

            .section-title {
                font-size: 20px;
                margin-top: 0;
                margin-bottom: 16px;
            }

            .address-info strong {
                font-size: 14px;
            }

            .address-info span,
            .address-info p {
                font-size: 13px;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                margin: 0 0 16px 0;
            }

            .modal-content {
                width: calc(100% - 32px);
                max-width: 500px;
                padding: 24px;
                box-sizing: border-box;
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
                gap: 28px;
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

    <div class="container" style="margin-top: 60px; min-height: 70vh;">
        <div class="breadcrumb">
            <a href="javascript:history.back()" class="back-link">
                <i class="fa-solid fa-chevron-left"></i> back
            </a>
        </div>
        <h1 class="page-header">My Account</h1>
        <div id="notification-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert-box success-alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?php echo $_SESSION['success'];
                    unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert-box error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $_SESSION['error'];
                    unset($_SESSION['error']); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert-box error-alert">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $_SESSION['error'];
                unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <div class="account-layout">
            <?php include "../includes/account_sidebar.php"; ?>

            <div class="address-content">
                <h2 class="section-title">Address</h2>
                <div class="address-grid">
                    <div class="address-card">
                        <div class="card-header">
                            <h3>Billing Address</h3>
                            <button class="edit-btn" onclick="openModal('billing')"><i
                                    class="fa-regular fa-pen-to-square"></i> Edit</button>
                        </div>
                        <div class="address-info">
                            <strong
                                id="billing-name"><?= htmlspecialchars($_SESSION['user']['billing_name'] ?? 'Not entered') ?></strong>
                            <span
                                id="billing-phone"><?= htmlspecialchars($_SESSION['user']['billing_phone'] ?? 'Not entered') ?></span>
                            <p id="billing-address">
                                <?= htmlspecialchars($_SESSION['user']['billing_address'] ?? 'Please update your address') ?>
                            </p>
                        </div>
                    </div>

                    <div class="address-card">
                        <div class="card-header">
                            <h3>Shipping Address</h3>
                            <button class="edit-btn" onclick="openModal('shipping')"><i
                                    class="fa-regular fa-pen-to-square"></i> Edit</button>
                        </div>
                        <div class="address-info">
                            <strong
                                id="shipping-name"><?= htmlspecialchars($_SESSION['user']['shipping_name'] ?? 'Not entered') ?></strong>
                            <span
                                id="shipping-phone"><?= htmlspecialchars($_SESSION['user']['shipping_phone'] ?? 'Not entered') ?></span>
                            <p id="shipping-address">
                                <?= htmlspecialchars($_SESSION['user']['shipping_address'] ?? 'Please update your address') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="addressModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal()">&times;</span>
            <div class="modal-header" id="modalTitle">Edit Address</div>
            <form id="editAddressForm">
                <input type="hidden" id="addressType">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="inputName" placeholder="Enter full name..." required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="inputPhone" placeholder="Enter phone number..." required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="inputAddress" placeholder="Enter full address..." required>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script>
        const modal = document.getElementById("addressModal");
        function openModal(type) {
            const prefix = type.toLowerCase();
            document.getElementById("modalTitle").innerText = "Edit " + (prefix === 'billing' ? 'Billing' : 'Shipping') + " Address";
            document.getElementById("addressType").value = prefix;

            const currentName = document.getElementById(prefix + "-name").innerText.trim();
            const currentPhone = document.getElementById(prefix + "-phone").innerText.trim();
            const currentAddr = document.getElementById(prefix + "-address").innerText.trim();

            document.getElementById("inputName").value = (currentName === "Not entered") ? "" : currentName;
            document.getElementById("inputPhone").value = (currentPhone === "Not entered") ? "" : currentPhone;
            document.getElementById("inputAddress").value = (currentAddr === "Please update your address") ? "" : currentAddr;

            modal.style.display = "flex";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        document.getElementById("editAddressForm").onsubmit = function (e) {
            e.preventDefault();
            const type = document.getElementById("addressType").value;
            const name = document.getElementById("inputName").value;
            const phone = document.getElementById("inputPhone").value;
            const address = document.getElementById("inputAddress").value;

            const formData = new FormData();
            formData.append('type', type);
            formData.append('full_name', name);
            formData.append('phone', phone);
            formData.append('address', address);
            formData.append('ajax', '1');

            fetch('../controllers/AuthController.php?action=save_address', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    alert("Updated successfully!");
                    window.location.reload();
                })
                .catch(error => {
                    alert("Connection error!");
                });
        };
        window.onclick = function (event) {
            if (event.target == modal) closeModal();
        }
        //upload avt 
        document.getElementById('avatar-upload').addEventListener('change', function (e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    document.getElementById('avatar-preview').src = event.target.result;
                };
                reader.readAsDataURL(this.files[0]);
                document.getElementById('avatar-form').submit();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-box');
            alerts.forEach(function (alert) {
                setTimeout(function () {
                    alert.style.opacity = '0';
                    setTimeout(function () {
                        alert.remove();
                    }, 500);
                }, 1000);
            });
        });
        //giỏ hàng
        function updateCartNumber(newCount) {
            const badge = document.getElementById('cart-count');
            if (newCount > 0) {
                badge.innerText = newCount;
                badge.style.display = 'flex';
            } else {
                badge.innerText = '';
                badge.style.display = 'none';
            }
        }
    </script>
</body>

</html>