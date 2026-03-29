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

        .footer-dark { 
            background-color: var(--black); 
            color: #fff; padding: 80px 0 40px; 
            margin-top: 100px; 
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
        .footer-brand .slogan { 
            font-size: 14px; 
            color: var(--gray-200); 
        }
        .footer-nav a { 
            color: #fff; 
            text-decoration: none; 
            margin-left: 40px; 
            font-size: 14px; 
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
            align-items: center; 
        }
        .legal-links a { 
            color: #fff; 
            font-weight: 600; 
            text-decoration: none;
            margin-left: 28px; 
        }
        .footer-social { 
            display: flex; 
            gap: 24px; 
            font-size: 20px; 
        }
        .footer-social a { 
            color: #fff; 
            transition: 0.3s; 
        }
        .footer-social a:hover { 
            color: var(--gray-400); 
        }

        @media (max-width: 768px) {
            .account-layout { 
                flex-direction: column; 
            }
            .address-grid { 
                grid-template-columns: 1fr; 
            }
            .footer-top, .footer-bottom { 
                flex-direction: column; 
                text-align: center; 
                gap: 32px; 
            }
            .footer-nav a { 
                margin: 0 10px; 
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
                            <strong id="billing-name">Chưa nhập tên</strong>
                            <span id="billing-phone">Chưa nhập SĐT</span>
                            <p id="billing-address">Vui lòng cập nhật địa chỉ thanh toán</p>
                        </div>
                    </div>

                    <div class="address-card">
                        <div class="card-header">
                            <h3>Shipping Address</h3>
                            <button class="edit-btn" onclick="openModal('shipping')"><i class="fa-regular fa-pen-to-square"></i> Edit</button>
                        </div>
                        <div class="address-info">
                            <strong id="shipping-name">Chưa nhập tên</strong>
                            <span id="shipping-phone">Chưa nhập SĐT</span>
                            <p id="shipping-address">Vui lòng cập nhật địa chỉ giao hàng</p>
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

    <footer class="footer-dark">
        <div class="container" style="margin: 0 auto; min-height: auto;"> 
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
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms of Use</a>
                    </div>
                </div>
                <div class="footer-social">
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>  
                    <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i></a>
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
            
            const currentName = document.getElementById(prefix + "-name").innerText;
            const currentPhone = document.getElementById(prefix + "-phone").innerText;
            const currentAddr = document.getElementById(prefix + "-address").innerText;

            document.getElementById("inputName").value = currentName.includes("Chưa nhập") ? "" : currentName;
            document.getElementById("inputPhone").value = currentPhone.includes("Chưa nhập") ? "" : currentPhone;
            document.getElementById("inputAddress").value = currentAddr.includes("Vui lòng cập nhật") ? "" : currentAddr;
            
            modal.style.display = "flex";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) closeModal();
        }

        document.getElementById("editAddressForm").onsubmit = function(e) {
            e.preventDefault();
            const type = document.getElementById("addressType").value;
            
            document.getElementById(type + "-name").innerText = document.getElementById("inputName").value;
            document.getElementById(type + "-phone").innerText = document.getElementById("inputPhone").value;
            document.getElementById(type + "-address").innerText = document.getElementById("inputAddress").value;
            
            closeModal();
            alert("Address updated successfully!");
        };
    </script>
</body>
</html>