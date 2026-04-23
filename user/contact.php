<?php
require_once '../config/config.php';
include '../includes/header.php';
include '../includes/navbar.php';

?>
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
            line-height: 1.5; 
            background-color: var(--white); 
            overflow-x: hidden;
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

        .form-group input,
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

        .form-group input:focus,
        .form-group textarea:focus { 
            border-color: var(--black); 
        }

        .btn-submit { 
            background: var(--black); 
            color: white;
            border: none; 
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
            justify-content: center;
            align-items: center;
            text-align: center;
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

        @media (max-width: 768px) {
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

            .hero .home {
                display: flex;
                gap: 15px;
            }

            .about-section { 
                flex-direction: column;
                width: 100%;
                margin: 16px 0;
                background: #F3F5F7;
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
                font-size: 28px; 
                line-height: 28px;
                text-align: center;
                margin: 1px 0 10px 0; 
            }

            .contact-methods { 
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

            .services-grid {
                grid-template-columns: 1fr 1fr; 
                gap: 16px;
            }

            .service-card {
                padding: 16px;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .service-card img {
                margin-bottom: 8px;
                width: 48px;
            }

            .service-card h4 {
                font-size: 14px;
                margin: 0;
            }

            .service-card p {
                font-size: 12px;
                margin: 0;
            }
        }
    </style>

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

<?php include '../includes/footer.php'; ?>