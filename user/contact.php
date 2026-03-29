<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | 3legant</title>
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

        body { font-family: 'Inter', sans-serif; 
            margin: 0; color: var(--black); 
            line-height: 1.5; 
            background-color: var(--white); 
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

        /* --- HERO SECTION --- */
        .hero { 
            padding: 48px 0; 
        }
        .breadcrumb { 
            font-size: 14px; 
            color: var(--gray-400); 
            margin-bottom: 16px; 
            font-weight: 500; 
        }
        .hero h1 { 
            font-size: 54px; 
            font-weight: 500; 
            margin: 0; 
            max-width: 800px; 
            line-height: 1.1; 
        }
        .hero p { 
            color: var(--gray-600); 
            max-width: 800px; 
            margin-top: 24px; 
            font-size: 16px; 
        }

        .about-section { 
            display: flex; 
            background: #F3F5F7; 
            align-items: center; 
            margin: 48px 0; 
        }
        .about-image { 
            flex: 1; 
            min-height: 450px; 
            background: url('../image/contact.png') center/cover; }
        .about-content { 
            flex: 1; 
            padding: 72px; 
        }
        .about-content h2 { 
            font-size: 40px; 
            margin-bottom: 16px; 
            font-weight: 500; 
        }
        .about-content p { 
            color: var(--gray-600); 
            margin-bottom: 24px; 
            font-size: 16px; 
        }
        .shop-now { 
            font-weight: 600; 
            border-bottom: 1px solid var(--black); 
            cursor: pointer; display: inline-block; 
            padding-bottom: 4px; 
            color: var(--black); 
        }

        .contact-methods { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 24px; margin: 48px 0; 
            text-align: center; 
        }
        .card { 
            background: #F3F5F7; 
            padding: 32px; 
            border-radius: 4px; 
        }
        .card-icon { 
            font-size: 32px; 
            margin-bottom: 16px;
        }
        .card h3 { 
            font-size: 16px; 
            color: var(--gray-400); 
            text-transform: uppercase; 
            margin-bottom: 8px; 
            letter-spacing: 1px; 
        }
        .card p { 
            font-weight: 600; margin: 0; font-size: 16px; }

        /* --- FORM & MAP --- */
        .contact-us-title { 
            text-align: center; 
            font-size: 40px; 
            margin-top: 80px; 
            font-weight: 500; 
        }
        .form-map-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr;
            gap: 48px; 
            margin: 48px 0 80px 0; 
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
        .form-group input, .form-group textarea { 
            width: 100%; 
            border: 1px solid var(--gray-200); 
            border-radius: 6px; 
            padding: 12px 16px; 
            font-size: 16px; 
            outline: none; 
            font-family: inherit; 
            box-sizing: border-box; 
        }
        .form-group input:focus, .form-group textarea:focus { 
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
            font-size: 16px;
        }
        
        .map-container { 
            width: 100%; 
            height: 100%; 
            min-height: 400px; 
            background: #eee; 
            border-radius: 4px; 
            overflow: hidden; 
        }

        /* --- VALUES SECTION --- */
        .values-section { 
            padding: 80px 0; 
        background: #fff; 
    }
        .values-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 24px; 
        }
        .value-item { 
            background: #F3F5F7; 
            padding: 48px 32px; 
            border: 1px dashed var(--gray-400); 
            text-align: left; 
        }
        .value-icon { 
            font-size: 32px; 
            margin-bottom: 16px; 
        }
        .value-item h3 { 
            font-size: 18px; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: var(--black); 
        }
        .value-item p { 
            font-size: 14px; 
            color: var(--gray-400); 
            margin: 0; 
        }

        /* --- FOOTER DARK --- */
        .footer-dark { 
            background: var(--black); 
            color: #fff; 
            padding: 80px 0 40px 0; 
            margin-top: 20px; 
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
            margin-left: 28px; 
        }
        .footer-social { 
            display: flex; 
            gap: 24px; 
            align-items: center;
        }
        .footer-social a { 
            color: var(--white); 
            font-size: 18px; 
            text-decoration: none; 
            opacity: 0.8; 
            transition: 0.3s; 
        }
        .footer-social a:hover {
            color: var(--gray-400); 
        }
        @media (max-width: 992px) {
            .values-grid { 
                grid-template-columns: repeat(2, 1fr); 
            }
            .about-content { 
                padding: 40px; }
        }
        @media (max-width: 768px) {
            .hero h1 { 
                font-size: 36px; 
        }
            .about-section, .form-map-grid { 
                display: block; 
        }
            .about-image { 
                min-height: 300px; 
            }
            .contact-methods { 
                grid-template-columns: 1fr; 
            }
            .values-grid { 
                grid-template-columns: 1fr; 
            }
            .footer-top, .footer-bottom { 
                flex-direction: column; 
                text-align: center; 
                gap: 32px; 
            }
            .footer-nav a { 
                margin: 0 10px; }
            .footer-legal { 
                flex-direction: column; 
                gap: 12px; 
            }
            .legal-links a { 
                margin: 0 14px; 
            }
        }
    </style>
</head>
<body>

<div class="container">
    <section class="hero">
        <div class="breadcrumb">Home > <span style="color:var(--black)">Contact Us</span></div>
        <h1>We believe in sustainable decor. We're passionate about life at home.</h1>
        <p>Our features timeless furniture, with natural fabrics, curved lines, plenty of mirrors and classic design, which can be incorporated into any decor project. The pieces enchant for their sobriety, to last for generations, faithful to the shapes of each period, with a touch of the present.</p>
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
            <div class="card-icon">🏠</div>
            <h3>Address</h3>
            <p>02 Vo Oanh, Ho Chi Minh City, Viet Nam</p>
        </div>
        <div class="card">
            <div class="card-icon">📞</div>
            <h3>Contact Us</h3>
            <p>(+84) 234 567 890</p>
        </div>
        <div class="card">
            <div class="card-icon">✉️</div>
            <h3>Email</h3>
            <p>test@legant.com</p>
        </div>
    </section>

    <section class="form-map-grid">
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
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.5201952558837!2d106.701812!3d10.771348!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a430d417%3A0x67341851e36f1d!2zMjM0IEjhuqNpIFRyaeG7gXUsIELhurNuIE5naMOpLCBRdeG6rW4gMSwgSOG7kyBDaMOtIE1pbmgsIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1710000000000!5m2!1svi!2s" 
                width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </section>
</div>

<section class="values-section">
    <div class="container values-grid">
        <div class="value-item">
            <div class="value-icon">🚚</div>
            <h3>Free Shipping</h3>
            <p>Order above $200</p>
        </div>
        <div class="value-item">
            <div class="value-icon">💵</div>
            <h3>Money-back</h3>
            <p>30 days guarantee</p>
        </div>
        <div class="value-item">
            <div class="value-icon">🔒</div>
            <h3>Secure Payments</h3>
            <p>Secured by Stripe</p>
        </div>
        <div class="value-item">
            <div class="value-icon">📞</div>
            <h3>24/7 Support</h3>
            <p>Phone and Email support</p>
        </div>
    </div>
</section>

<footer class="footer-dark">
    <div class="container">
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

</body>
</html>