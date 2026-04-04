<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../includes/auth.php"; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address | 3legant</title>
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
            line-height: 1.5; 
        }
        .container { 
            max-width: 1120px; 
            margin: 60px auto; 
            padding: 0 20px; 
            min-height: 70vh; 
        }
        .page-header { 
            font-size: 54px; 
            font-weight: 600; 
            text-align: center; 
            margin-bottom: 60px; 
            letter-spacing: -1px; 
        }

        .account-layout { 
            display: flex; 
            gap: 64px; 
        }
        .address-content { 
            flex: 1; }
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
            background: rgba(0,0,0,0.5); 
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

        @media (max-width: 768px) {
            .account-layout { flex-direction: column; }
            .address-grid { grid-template-columns: 1fr; }
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

            <div class="address-content">
                <h2 class="section-title">Address</h2>
                <div class="address-grid">
                    <div class="address-card">
                        <div class="card-header">
                            <h3>Billing Address</h3>
                            <button class="edit-btn" onclick="openModal('billing')"><i class="fa-regular fa-pen-to-square"></i> Edit</button>
                        </div>
                        <div class="address-info">
                            <strong id="billing-name"><?= htmlspecialchars($_SESSION['user']['billing_name'] ?? 'Chưa nhập tên') ?></strong>
                            <span id="billing-phone"><?= htmlspecialchars($_SESSION['user']['billing_phone'] ?? 'Chưa nhập SĐT') ?></span>
                            <p id="billing-address"><?= htmlspecialchars($_SESSION['user']['billing_address'] ?? 'Vui lòng cập nhật địa chỉ') ?></p>
                        </div>
                    </div>

                    <div class="address-card">
                        <div class="card-header">
                            <h3>Shipping Address</h3>
                            <button class="edit-btn" onclick="openModal('shipping')"><i class="fa-regular fa-pen-to-square"></i> Edit</button>
                        </div>
                        <div class="address-info">
                            <strong id="shipping-name"><?= htmlspecialchars($_SESSION['user']['shipping_name'] ?? 'Chưa nhập tên') ?></strong>
                            <span id="shipping-phone"><?= htmlspecialchars($_SESSION['user']['shipping_phone'] ?? 'Chưa nhập SĐT') ?></span>
                            <p id="shipping-address"><?= htmlspecialchars($_SESSION['user']['shipping_address'] ?? 'Vui lòng cập nhật địa chỉ') ?></p>
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
                    <input type="text" id="inputName" placeholder="Nhập họ và tên..." required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="inputPhone" placeholder="Nhập số điện thoại..." required>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" id="inputAddress" placeholder="Nhập địa chỉ đầy đủ..." required>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
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
                    <a href="instagram"><i class="fa-brands fa-instagram"></i></a>  
                    <a href="facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="youtube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const modal = document.getElementById("addressModal");
        function openModal(type) {
            const prefix = type.toLowerCase();
            document.getElementById("modalTitle").innerText = "Edit " + (prefix === 'billing' ? 'Billing' : 'Shipping') + " Address";
            document.getElementById("addressType").value = prefix;
            
            const currentName = document.getElementById(prefix + "-name").innerText.trim();
            const currentPhone = document.getElementById(prefix + "-phone").innerText.trim();
            const currentAddr = document.getElementById(prefix + "-address").innerText.trim();

            document.getElementById("inputName").value = (currentName === "Chưa nhập tên") ? "" : currentName;
            document.getElementById("inputPhone").value = (currentPhone === "Chưa nhập SĐT") ? "" : currentPhone;
            document.getElementById("inputAddress").value = (currentAddr === "Vui lòng cập nhật địa chỉ") ? "" : currentAddr;
            
            modal.style.display = "flex";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        document.getElementById("editAddressForm").onsubmit = function(e) {
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
                alert("Cập nhật thành công!");
                window.location.reload(); 
            })
            .catch(error => {
                alert("Lỗi kết nối!");
            });
        };

        window.onclick = function(event) {
            if (event.target == modal) closeModal();
        }
    </script>
</body>
</html>