<?php
require_once '../config/config.php';
?>

<?php include '../includes/header.php'; ?>
<style>
.topbar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.topbar-icon {
    width: 18px;  
    height: 18px;
}
</style>
<div class="topbar" id="topbar">
    <img src="../assets/images/voucher.png" class="topbar-icon">

    <span class="topbar-text">
        30% off storewide — Limited time!
    </span>

    <a href="#" class="topbar-link">Shop Now →</a>

    <span class="topbar-close" onclick="closeTopbar()">✕</span>
</div>

<?php include '../includes/navbar.php'; ?>

<section class="hero-section">
    <div class="container">

        <div class="hero-slider">
            <img id="hero-img" src="../assets/images/banner.jpg" class="hero-img">

            <div class="hero-btn left">
                <i class="fa-solid fa-chevron-left"></i>
            </div>

            <div class="hero-btn right">
                <i class="fa-solid fa-chevron-right"></i>
            </div>
        </div>

        <div class="hero-content">
            <h1>
                Simply Unique/<br>Simply Better.
            </h1>

            <p>
                <strong>3legant</strong> là cửa hàng quà tặng và trang trí nội thất có trụ sở tại TP.HCM, Việt Nam. Thành lập từ năm 2019.
            </p>
        </div>

    </div>
</section>

<section class="category-section">
    <div class="container category-wrapper">
        
        <div class="category-left">
            <img src="../assets/images/livingroom.jpg" class="category-img">

            <div class="category-text">
                <h3>Living Room</h3>
                <a href="#" class="link-primary">Shop Now →</a>
            </div>
        </div>

         <div class="category-right">
            <div class="category-item">
                <img src="../assets/images/bedroom.jpg" class="category-img">
                
                <div class="category-text">
                    <h3>Bedroom</h3>
                    <a href="#" class="link-primary">Shop Now →</a>
                </div>
            </div>

            <div class="category-item">
                <img src="../assets/images/kitchen.jpg" class="category-img">

                <div class="category-text">
                    <h3>Kitchen</h3>
                    <a href="#" class="link-primary">Shop Now →</a>
                </div>
            </div>

        </div>

    </div>
</section>

<section style="padding:64px 0;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;margin-bottom:24px;">
            <h2>New Arrivals</h2>
            <a href="#" class="link-primary">More product →</a>
        </div>

    <div class="product-grid">
        <div style="border:1px solid #E8ECEF;border-radius:12px;padding:12px;">
            <div style="position:relative;">
                <img src="../assets/images/sofa.jpg" style="width:100%;height:160px;object-fit:cover;border-radius:8px;">
                    <span style="position:absolute;top:8px;left:8px;background:black;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">NEW</span>
                    <span style="position:absolute;top:8px;left:50px;background:#38CB89;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">-50%</span>
                    <span style="position:absolute;top:8px;right:8px;">♡</span>
            </div>

                <p style="margin-top:10px;">Loveseat Sofa</p>

                <p>
                    <b>$199.00</b>
                    <span style="text-decoration:line-through;color:#aaa;margin-left:6px;">$400.00</span>
                </p>

                <button style="width:100%;padding:10px;background:black;color:white;border:none;border-radius:6px;">
                    Add to cart
                </button>
            </div>

            <div style="border:1px solid #E8ECEF;border-radius:12px;padding:12px;">
                <div style="position:relative;">
                    <img src="../assets/images/lamp.jpg" style="width:100%;height:160px;object-fit:cover;border-radius:8px;">

                    <span style="position:absolute;top:8px;left:8px;background:black;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">NEW</span>
                    <span style="position:absolute;top:8px;left:50px;background:#38CB89;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">-50%</span>
                </div>

                <p style="margin-top:10px;">Table Lamp</p>

                <p><b>$24.99</b></p>

                <button style="width:100%;padding:10px;background:black;color:white;border:none;border-radius:6px;">
                    Add to cart
                </button>
            </div>

            <div style="border:1px solid #E8ECEF;border-radius:12px;padding:12px;">
                <div style="position:relative;">
                    <img src="../assets/images/lamp2.jpg" style="width:100%;height:160px;object-fit:cover;border-radius:8px;">

                    <span style="position:absolute;top:8px;left:8px;background:black;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">NEW</span>
                    <span style="position:absolute;top:8px;left:50px;background:#38CB89;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">-50%</span>
                </div>

                <p style="margin-top:10px;">Beige Table Lamp</p>

                <p><b>$24.99</b></p>

                <button style="width:100%;padding:10px;background:black;color:white;border:none;border-radius:6px;">
                    Add to cart
                </button>
            </div>

            <div style="border:1px solid #E8ECEF;border-radius:12px;padding:12px;">
                <div style="position:relative;">
                    <img src="../assets/images/basket.jpg" style="width:100%;height:160px;object-fit:cover;border-radius:8px;">

                    <span style="position:absolute;top:8px;left:8px;background:black;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">NEW</span>
                    <span style="position:absolute;top:8px;left:50px;background:#38CB89;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">-50%</span>
                </div>

                <p style="margin-top:10px;">Bamboo basket</p>

                <p><b>$24.99</b></p>

                <button style="width:100%;padding:10px;background:black;color:white;border:none;border-radius:6px;">
                    Add to cart
                </button>
            </div>

            <div style="border:1px solid #E8ECEF;border-radius:12px;padding:12px;">
                <div style="position:relative;">
                    <img src="../assets/images/kitchen.jpg" style="width:100%;height:160px;object-fit:cover;border-radius:8px;">

                    <span style="position:absolute;top:8px;left:8px;background:black;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">NEW</span>
                    <span style="position:absolute;top:8px;left:50px;background:#38CB89;color:white;padding:4px 6px;font-size:12px;border-radius:6px;">-50%</span>
                </div>

                <p style="margin-top:10px;">Toaster</p>

                <p><b>$24.99</b></p>

                <button style="width:100%;padding:10px;background:black;color:white;border:none;border-radius:6px;">
                    Add to cart
                </button>
            </div>

        </div>

    </div>
</section>

<section style="padding:48px 0;">
    <div class="container" style="
        display:grid;
        grid-template-columns:repeat(4,1fr);
        gap:24px;
    ">

        <div class="service-card">
            <img src="../assets/images/Vector.png" alt="Free Shipping">
            <h4>Free Shipping</h4>
            <p>Order above $200</p>
        </div>

        <div class="service-card">
            <img src="../assets/images/money.png" alt="Money-back">
            <h4>Money-back</h4>
            <p>30 days guarantee</p>
        </div>

        <div class="service-card">
            <img src="../assets/images/lock 01.png" alt="Secure Payments">
            <h4>Secure Payments</h4>
            <p>Secured by Stripe</p>
        </div>

        <div class="service-card">
            <img src="../assets/images/call.png" alt="Support">
            <h4>24/7 Support</h4>
            <p>Phone and Email support</p>
        </div>

    </div>
</section>

<section class="promo-section">

    <div class="promo-container">

        <img src="../assets/images/promo.jpg" class="promo-img">

        <div class="promo-content">

            <h5 class="promo-tag">
                SALE UP TO 35% OFF
            </h5>

            <h2 class="promo-title">
                HUNDREDS of<br>New lower prices!
            </h2>

            <p class="promo-desc">
                It’s more affordable than ever to give every room in your home a stylish makeover
            </p>

            <a href="#" class="promo-link">
                Shop Now →
            </a>

        </div>

    </div>

</section>

<section style="padding:64px 0;">
    <div class="container">
        <div style="display:flex;justify-content:space-between;">
            <h2>Articles</h2>
            <a href="#" class="link-primary">More Articles →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:20px;">
            <div>
                <img src="../assets/images/a1.png" style="width:100%;border-radius:12px;">
                <p style="margin-top:16px;margin-bottom:6px;">
                <p>7 ways to decor your home</p>
                <a href="#" class="link-primary link-animate" style="display:inline-block;margin-top:6px;">Read more →</a>
            </div>

            <div>
                <img src="../assets/images/a2.png" style="width:100%;border-radius:12px;">
                <p style="margin-top:16px;margin-bottom:6px;">
                <p>Kitchen organization</p>
                <a href="#" class="link-primary link-animate" style="display:inline-block;margin-top:6px;">Read more →</a>
            </div>

            <div>
                <img src="../assets/images/a3.png" style="width:100%;border-radius:12px;">
                <p style="margin-top:16px;margin-bottom:6px;">
                <p>Decor your bedroom</p>
                <a href="#" class="link-primary link-animate" style="display:inline-block;margin-top:6px;">Read more →</a>
        </div>
    </div>
</section>

<section style="
    position:relative;
    background:#F3F5F7;
    padding:80px 0;
    overflow:hidden;
">

    <img src="../assets/images/image.png" style="
       position:absolute;
       top:0;
       left:50%;
       transform:translateX(-50%);   
       height:100%;
       width:auto;
       min-width:100%; ">

        <div style="max-width:500px;text-align:center;margin:auto;position:relative;z-index:2;">
            
            <h2 style="font-size:32px;font-weight:600;">
                Join Our Newsletter
            </h2>

            <p style="color:#6C7275;margin:10px 0 30px;">
                Sign up for deals, new products and promotions
            </p>

            <div style="
                display:flex;
                justify-content:center;
                align-items:center;
                gap:10px;
            ">
            <div style="
                display:flex;
                align-items:center;
                border-bottom:1px solid #6C7275;
                padding:8px 0;
                width:280px;
                ">
                    <i class="fa-regular fa-envelope" style="margin-right:10px;"></i>
                    <input placeholder="Email address" style="border:none;outline:none;background:none;width:100%;">
                </div>

                <button style="border:none;background:none;font-weight:600;">
                    Signup
                </button>
            </div>

        </div>

    </div>
</section>
<script>
const images = [
    "../assets/images/banner.jpg",
    "../assets/images/Image(1).jpg",
    "../assets/images/img(2).jpg"
];

let index = 0;

const banner = document.getElementById("hero-img");
const nextBtn = document.querySelector(".hero-btn.right");
const prevBtn = document.querySelector(".hero-btn.left");

nextBtn.addEventListener("click", () => {
    index++;
    if (index >= images.length) index = 0;
    banner.src = images[index];
});

prevBtn.addEventListener("click", () => {
    index--;
    if (index < 0) index = images.length - 1;
    banner.src = images[index];
});
</script>
<script>
function closeTopbar() {
    document.getElementById("topbar").style.display = "none";
}
</script>
<?php include '../includes/footer.php'; ?>