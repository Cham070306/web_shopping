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

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            color: var(--black); 
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
        
        .hero { 
            padding: 48px 0; 
        }
        .home { 
            font-size: 14px; 
            color: var(--gray-400); 
            margin-bottom: 16px; 
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
        }
        .card p { 
            font-weight: 600; 
            margin: 0; 
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
            background: #eee; 
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
        }
        .value-icon { 
            font-size: 32px; 
            margin-bottom: 16px; 
        }
        .value-item h3 { 
            font-size: 18px; 
            font-weight: 600; 
            margin-bottom: 8px; 
        }
        .value-item p { 
            font-size: 14px; 
            color: var(--gray-400); 
            margin: 0; 
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
            .hero h1 { 
                font-size: 34px; 
            }
            .about-section { 
                flex-direction: column; 
            }
            .about-content {
                padding: 40px 20px; 
            }
            .contact-methods { 
                grid-template-columns: 1fr; 
            } 
            .values-grid { 
                grid-template-columns: 1fr; 
            }

            .form-map-grid { 
                flex-direction: column; 
            }
            .map-container { 
                order: 1; 
                min-height: 300px; 
            }
            .contact-form-wrapper { 
                order: 2; 
            }

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
    <section class="hero">
        <a href="index.php" class="home">Home ><span style="color:var(--black)">Contact Us</span></a>
        <h1>We believe in sustainable decor. We're passionate about life at home.</h1>
        <p>Our features timeless furniture, with natural fabrics, curved lines, plenty of mirrors and classic design, which can be incorporated into any decor project.</p>
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

</body>
</html>