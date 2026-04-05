<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | 3legant</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --black: #141718;
            --gray-600: #343839;
            --gray-400: #6C7275;
            --gray-200: #E8ECEF;
            --white: #FFFFFF;
        }
        .navbar {
            width: 100%;
            height: 50px;
            background: white;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo { 
            font-size: 24px; 
            font-weight: 600; 
            color: var(--black); 
            text-decoration: none; 
        }

        .menu { 
            display: flex; 
            gap: 40px; 
        }
        .menu-header {
            display: none !important; 
        }

        .menu a { 
            font-size: 14px; 
            font-weight: 500; 
            color: var(--gray-400); 
            text-decoration: none; 
            padding: 8px 0;
            border-bottom: 2px solid transparent;
            transition: 0.2s; 
        }
        .menu a.active, .menu a:hover { 
            color: var(--black); 
        }
        
        .menu a.active { 
            color: var(--black); 
            border-bottom: 2px solid var(--black);
        }

        .icons { 
            display: flex;
            align-items: center; 
            gap: 16px; 
        }
        .icons a {
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        .icons img {
            width: 20px;
            height: 20px;
            object-fit: contain;
            cursor: pointer;
        }
        .icon-link, .cart-wrapper {
            display: flex;
            align-items: center;
            text-decoration: none;
            cursor: pointer;
        }
        .cart-wrapper {
            display: flex !important; 
            align-items: center;
            gap: 5px;
            position: relative;
        }

        .cart-badge {
            background-color: var(--black); 
            color: white;
            font-size: 12px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white; 
        }
        .cart-wrapper img {
            width: 24px;
            height: 24px;
        }
        .desktop {
            display: flex;
        }

        #menu-toggle, .menu-btn { 
            display: none; 
        }
        #menu-toggle:checked ~ .menu {
            left: 0;
        }
       
        .overlay {
            position: fixed;
            inset: 0;
            background: #1e2223;;
            display: none;
            z-index: 10000;
        } 
        #menu-toggle:checked ~ .overlay {
            display: block;
        }
        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .close-btn {
            font-size: 22px;
            cursor: pointer;
        }
      
        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            color: var(--black); 
            line-height: 1.5; 
            background-color: var(--white); 
            overflow-x: hidden;
            padding-top: 30px;
        }

        .container { 
            max-width: 1120px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }

        a { 
            text-decoration: none; 
            transition: 0.3s; 
        }
        
        .hero { 
            padding: 48px 0; 
        }
        .home { 
            font-size: 14px; 
            color: var(--gray-400); 
            margin-bottom: 16px; 
            display: block;
            font-weight: 500; 
        }
        .hero h1 { 
            font-size: 54px; 
            font-weight: 500; 
            margin: 0; 
            line-height: 1.1; 
        }
        .hero p { 
            color: var(--gray-600); 
            margin-top: 24px; 
            font-size: 16px; 
            max-width: 800px; 
        }

        .about-section { 
            display: flex; 
            background: #F3F5F7; 
            margin: 48px 0; 
        }
        .about-image { 
            flex: 1; 
            min-height: 450px; 
            background: url('../assets/image/contact.png') center/cover no-repeat; 
        }
        .about-content { 
            flex: 1; 
            padding: 72px; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
        }
        .about-content h2 { 
            font-size: 40px; 
            margin-bottom: 16px; 
            font-weight: 500; 
        }
        .about-content p { 
            color: var(--gray-600); 
            margin-bottom: 24px; 
        }
        .shop-now { 
            font-weight: 600; 
            border-bottom: 1px solid var(--black); 
            display: inline-block; 
            color: var(--black); 
            width: fit-content; 
        }

        .contact-methods { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 24px; 
            margin: 48px 0; 
            text-align: center; 
        }
        .card { 
            background: #F3F5F7; 
            padding: 30px; 
            border-radius: 4px; 
        }
        .card-icon { 
            font-size: 30px; 
            margin-bottom: 15px; 
        }
        .card h3 { 
            font-size: 16px; 
            color: var(--gray-400); 
            text-transform: uppercase; 
            margin-bottom: 8px; 
        }
        .card p { 
            font-weight: 600; 
            margin: 0; 
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .contact-us-title { 
            text-align: center; 
            font-size: 40px; 
            margin-top: 80px; 
            font-weight: 500; 
        }
        
        .form-map-grid { 
            display: flex; 
            gap: 48px; 
            margin: 48px 0 80px 0; 
            align-items: stretch; 
        }

        .contact-form-wrapper { 
            flex: 1; 
            order: 1; 
        }
        .map-container { 
            flex: 1; 
            order: 2; 
            min-height: 400px; 
            background: #fff; 
            border-radius: 4px; 
            overflow: hidden; 
        }
        .map-container iframe { 
            width: 100%; 
            height: 100%; 
            min-height: 400px; 
            display: block; 
        }

        .form-group { 
            margin-bottom: 24px; 
        }
        .form-group label { 
            display: block; 
            font-size: 12px; 
            font-weight: 700; 
            color: var(--gray-400); 
            text-transform: uppercase; 
            margin-bottom: 8px; 
        }
        .form-group input { 
            width: 100%; 
            border: 1px solid var(--gray-200); 
            border-radius: 6px; 
            padding: 12px 16px; 
            font-size: 16px; 
            outline: none; 
            font-family: inherit; 
            box-sizing: border-box; 
        } 
        .form-group textarea { 
            width: 100%; 
            border: 1px solid var(--gray-200); 
            border-radius: 6px; 
            padding: 12px 16px; 
            font-size: 16px; 
            outline: none; 
            font-family: inherit; 
            box-sizing: border-box; 
        }
        .form-group input:focus { 
            border-color: var(--black); 
        } 
        .form-group textarea:focus { 
            border-color: var(--black); 
        }
        .btn-submit { 
            background: var(--black); 
            color: white; border: none; 
            padding: 12px 40px; 
            border-radius: 40px; 
            font-weight: 500; 
            cursor: pointer; 
            }

        .container, .footercontainer { 
            max-width: 1120px; 
            margin: 0 auto; 
            padding: 0 20px; 
        }

        .services-section {
            padding: 48px 0;
            background: #F3F5F7; 
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .service-card {
            background: #F3F5F7;
            padding: 32px;
            border-radius: 12px;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .service-card img {
            font-size: 24px;
            margin-bottom: 16px;
        }

        .service-card h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .service-card p {
            font-size: 14px;
            color: #6C7275;
        }
        .service-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
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
            body { 
                padding-top: 20px; 
            }
           
            .desktop{
                display: none !important;
            }

            .navbar-container {
                padding: 0 28px;
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
                top: 0; left: 0; 
                width: 100%; 
                height: 100%;
                background: #1b1e1f9a;; 
                display: none; 
                z-index: 10000;
            }
            #menu-toggle:checked ~ .overlay {
                display: block;
            }
            
            .container {
                padding: 0 28px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .hero {
                padding: 32px 0 28px 0; 
                text-align: left;
            }
            .hero h1 { 
                font-size: 28px; 
                line-height: 34px;
                letter-spacing: -0.5px;
                margin-bottom: 16px;
            }
            .hero p {
                font-size: 14px;
                line-height: 22px;
                color: var(--gray-600);
            }
            .hero .home{
                display: flex;
                gap: 15px;
            } 
            .about-section { 
                flex-direction: column;
                width: 100%;
                margin: 16px 0;
                background: #F3F5F7 
            }
            .about-image {
                width: 100%;
                min-height: 311px;
            }
            .about-content {
                padding: 32px 16px;
                gap: 14px;
                box-sizing: border-box;
                height: auto;
            }
            .about-content h2 {
                font-size: 28px; 
                margin: 0;
            }

            .contact-us-title {
                width: 100%;
                font-family: sans-serif; 
                font-size: 28px; 
                line-height: 28px;
                text-align: center;
                margin: 1px 0 10px 0; 
            }
            .contact-methods { 
                grid-template-columns: 1fr; 
            } 
            .values-grid { 
                grid-template-columns: 1fr; 
            }

            .form-map-grid { 
                flex-direction: column;
                width: 100%;
                height: 715px;
                margin-top: 8px;
            }
            .map-container { 
                order: 1; 
                min-height: 300px; 
            }
            .contact-form-wrapper { 
                order: 2; 
            }
        
            .services-section {
                padding: 48px 0;
                background: #F3F5F7;
            }

            .services-grid {
                display: grid;
                grid-template-columns: 1fr 1fr; 
                gap: 16px;
            }
            .service-card {
                background: #F3F5F7; 
                padding: 16px;
                text-align: left; 
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            .service-card img, .service-card i {
                font-size: 32px;
                margin-bottom: 8px;
                width: 48px;
                
            }
            .service-card h4 {
                font-size: 14px;
                margin: 0;
                font-weight: 600;
            }
            .service-card p {
                font-size: 12px;
                color: var(--gray-400);
                margin: 0;
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
                gap:28px; 
            
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
</head>
<body>
<nav class="navbar">
        <div class="navbar-container">
            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-btn">
                <img src="../assets/image/menu.png" alt="menu">
            </label>
            <a href="index.php" class="logo">3legant.</a>

            <div class="menu">
                <div class="menu-header">
                    <span>3Elegant</span>
                    <label for="menu-toggle" class="close-btn">✕</label>
                </div>
                <a href="index.php" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a>
                <a href="shop.php" class="<?= ($current_page == 'shop.php') ? 'active' : '' ?>">Shop</a>
                <a href="product.php" class="<?= ($current_page == 'product.php') ? 'active' : '' ?>">Product</a>
                <a href="contact.php" class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>">Contact Us</a>
            </div>

            <div class="icons">
                <a href="shop.php" class="icon-link desktop">
                    <img src="../assets/image/search 02.png" alt="search">
                </a>

                <a href="my_account.php" class="icon-link desktop">
                    <img src="../assets/image/Vector1.png" alt="account">
                </a>

                <a href="cart.php" class="cart-wrapper">
                    <img src="../assets/image/shopping bag.png" alt="bag">
                    <span id="cart-count" class="cart-badge">
                        <?php 
                            $total = 0;
                            if(isset($_SESSION['cart'])) {
                                foreach($_SESSION['cart'] as $item) { $total += $item['quantity']; }
                            }
                            echo $total; 
                        ?>
                    </span>
                </a>
            </div>
            <label for="menu-toggle" class="overlay"></label>
        </div>
    </nav>

<div class="container">
    <section class="hero">
        <a href="index.php" class="home">Home ><span style="color:var(--black)">    Contact Us</span></a>
        <h1>We believe in sustainable decor. We're passionate about life at home.</h1>
        <p>Our features timeless furniture, with natural fabrics, curved lines, plenty of mirrors and classic design, which can be incorporated into any decor project. The pieces enchant for their sobriety, to last for generations, faithful to the shapes of each period, with a touch of the present</p>
    </section>

    <section class="about-section">
        <div class="about-image"></div>
        <div class="about-content">
            <h2>About Us</h2>
            <p>3legant is a gift & decorations store based in HCMC, Vietnam. Est since 2019. Our customer service is always prepared to support you 24/7.</p>
            <a href="shop.php" class="shop-now">Shop Now →</a>
        </div>
    </section>

    <h2 class="contact-us-title">Contact Us</h2>

    <section class="contact-methods">
        <div class="card">
            <div class="card-icon">
                <img src="../assets/image/store 01.png">
            </div>
            <h3>Address</h3>
            <p>02 Vo Oanh, Ho Chi Minh City, Viet Nam</p>
        </div>
        <div class="card">
            <div class="card-icon">
                <img src="../assets/image/call.png">
            </div>
            <h3>Contact Us</h3>
            <p>(+84) 234 567 890</p>
        </div>
        <div class="card">
            <div class="card-icon">
                <img src="../assets/image/mail.png">
            </div>
            <h3>Email</h3>
            <p>hotro@legant.com</p>
        </div>
    </section>

    <section class="form-map-grid">
        <div class="contact-form-wrapper">
            <form action="../controllers/ContactController.php" method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" placeholder="Your Name" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Your Email" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" rows="5" placeholder="Your message" required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.126422730336!2d106.7121283147491!3d10.80162799230438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528a459cb436b%3A0x70b53ad55e60630!2zMDIgVsO1IE9hbmgsIFBoxrDhu51uZyAyNSwgQsOsbmggVGjhuqFuaCwgVGjDoG5oIHBo4buRIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1690000000000!5m2!1svi!2s" 
                style="border: 0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>
</div>

<section class="services-section">
        <div class="footercontainer services-grid">
            <div class="service-card">
                <img src="../assets/image/Vector.png" alt="Free Shipping">
                <h4>Free Shipping</h4>
                <p>Order above $200</p>
            </div>

            <div class="service-card">
                <img src="../assets/image/money.png" alt="Money-back">
                <h4>Money-back</h4>
                <p>30 days guarantee</p>
            </div>

            <div class="service-card">
                <img src="../assets/image/lock 01.png" alt="Secure Payments">
                <h4>Secure Payments</h4>
                <p>Secured by Stripe</p>
            </div>

            <div class="service-card">
                <img src="../assets/image/call.png" alt="Support">
                <h4>24/7 Support</h4>
                <p>Phone and Email support</p>
            </div>
        </div>
    </section>

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
                <a href="Instagram"><img src="../assets/image/instagram.png" alt="Instagram"></a>  
                <a href="Facebook"><img src="../assets/image/Vector 2998.png" alt="Facebook"></a>
                <a href="Youtube"><img src="../assets/image/youtube.png" alt="Youtube"></a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>