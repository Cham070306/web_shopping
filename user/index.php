<?php
require_once '../config/config.php';
require_once '../config/database.php';

$user_id = $_SESSION['user']['id'] ?? null;

// Fetch 8 newest products for homepage
$newArrivals = [];
$naStmt = $conn->query("SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC LIMIT 4");
if ($naStmt) {
    $newArrivals = $naStmt->fetch_all(MYSQLI_ASSOC);
}

// Fetch 4 featured products for a separate "Featured" row
$featuredProducts = [];
$fpStmt = $conn->query("SELECT * FROM products WHERE is_active = 1 AND is_featured = 1 ORDER BY id DESC LIMIT 4");
if ($fpStmt) {
    $featuredProducts = $fpStmt->fetch_all(MYSQLI_ASSOC);
}

// User wishlist IDs
$wishedIds = [];
if ($user_id) {
    $ws = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws->bind_param("i", $user_id);
    $ws->execute();
    $wsRes = $ws->get_result();
    while ($row = $wsRes->fetch_assoc()) {
        $wishedIds[] = $row['product_id'];
    }
}

function formatVND($price) {
    if (!$price) return '0';
    return number_format((int)$price, 0, ',', '.');
}
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
.navbar {
    position: fixed;
    top: -10px;
    ...
}
.navbar.scrolled {
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 8px 24px rgba(20, 23, 24, 0.08);
    border-bottom: 1px solid rgba(232, 236, 239, 0.8);
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

<!-- ═══════════════════════════════════════════════
     HOME PAGE — PRODUCT CARD STYLES
═══════════════════════════════════════════════ -->
<style>
.home-products-section {
    padding: 72px 0;
    background: #fff;
}
.home-section-header {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 36px;
}
.home-section-header h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 32px;
    font-weight: 600;
    color: #141718;
    margin: 0;
    line-height: 1.2;
}
.home-section-header .section-sub {
    font-size: 14px;
    color: #6C7275;
    margin-top: 6px;
}
.home-section-header a.link-more {
    font-size: 14px;
    font-weight: 600;
    color: #141718;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 6px;
    border-bottom: 1.5px solid #141718;
    padding-bottom: 2px;
    transition: opacity .2s;
    white-space: nowrap;
}
.home-section-header a.link-more:hover { opacity: 0.65; }

/* Grid: 4 columns on desktop */
.home-product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}
@media (max-width: 1100px) {
    .home-product-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 760px) {
    .home-product-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
    .home-section-header h2 { font-size: 24px; }
}

/* Card */
.hp-card {
    display: flex;
    flex-direction: column;
    cursor: pointer;
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow .25s;
}
.hp-card:hover {
    box-shadow: 0 8px 28px rgba(20,23,24,.10);
}
.hp-img-box {
    position: relative;
    background: #F3F5F7;
    width: 100%;
    height: 240px;
    overflow: hidden;
    flex-shrink: 0;
}
.hp-img-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 14px;
    box-sizing: border-box;
    transition: transform .4s ease;
}
.hp-card:hover .hp-img-box img { transform: scale(1.05); }

/* Badges */
.hp-badges {
    position: absolute;
    top: 12px;
    left: 12px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 2;
}
.hp-badge-new {
    background: #fff;
    color: #141718;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    letter-spacing: .5px;
}
.hp-badge-sale {
    background: #38CB89;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    letter-spacing: .5px;
}

/* Wishlist btn */
.hp-wish-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 2;
    background: #fff;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.10);
    cursor: pointer;
    transition: background .18s;
}
.hp-wish-btn:hover { background: #F3F5F7; }

/* Cart overlay */
.hp-cart-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 12px;
    opacity: 0;
    transform: translateY(10px);
    transition: all .26s ease;
    z-index: 2;
}
.hp-img-box:hover .hp-cart-overlay {
    opacity: 1;
    transform: translateY(0);
}
.hp-btn-cart {
    width: 100%;
    background: #141718;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    padding: 10px;
    cursor: pointer;
    transition: background .18s;
}
.hp-btn-cart:hover { background: #343839; }

/* Info */
.hp-info {
    padding: 14px 10px 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.hp-stars { color: #F7A928; font-size: 11px; margin-bottom: 5px; letter-spacing: 1px; }
.hp-name {
    font-size: 14px;
    font-weight: 600;
    color: #141718;
    margin-bottom: 8px;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 40px;
    flex: 1;
}
.hp-price {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #141718;
    margin-top: auto;
}
.hp-price-old {
    color: #9BA3AF;
    text-decoration: line-through;
    font-weight: 400;
    font-size: 12px;
}

/* Empty state */
.hp-empty {
    grid-column: 1 / -1;
    text-align: center;
    padding: 60px 20px;
    color: #9BA3AF;
    font-size: 15px;
}
.hp-empty svg { margin-bottom: 16px; display: block; margin-left: auto; margin-right: auto; }
</style>

<section class="home-products-section">
    <div class="container">

        <!-- New Arrivals -->
        <div class="home-section-header">
            <div>
                <h2>New Arrivals</h2>
                <p class="section-sub">Sản phẩm mới nhất từ cửa hàng</p>
            </div>
            <a href="shop.php" class="link-more">Xem tất cả →</a>
        </div>

        <div class="home-product-grid">
            <?php if (empty($newArrivals)): ?>
                <div class="hp-empty">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <p>Chưa có sản phẩm nào. Vui lòng import dữ liệu vào DB.</p>
                </div>
            <?php else: ?>
                <?php foreach ($newArrivals as $p):
                    $thumb = trim($p['thumbnail'] ?? '');
                    if (strpos($thumb, 'http') === 0) {
                        $img = htmlspecialchars($thumb);
                    } elseif ($thumb) {
                        $img = '../assets/product-images/' . htmlspecialchars($thumb);
                    } else {
                        $img = '../assets/images/sofa.jpg';
                    }
                    $hasSale   = !empty($p['sale_price']) && $p['price'] > $p['sale_price'];
                    $discount  = $hasSale ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
                    $dispPrice = $hasSale ? $p['sale_price'] : $p['price'];
                    $isWished  = in_array($p['id'], $wishedIds);
                    $rating    = !empty($p['rating']) ? (float)$p['rating'] : 4.0;
                    $fullStars = min(5, (int)round($rating));
                    $starsHtml = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                ?>
                <div class="hp-card">
                    <div class="hp-img-box">
                        <a href="product_detail.php?id=<?= $p['id'] ?>" style="display:block;width:100%;height:100%;">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"
                                 onerror="this.src='../assets/images/sofa.jpg'">
                        </a>

                        <!-- Badges -->
                        <div class="hp-badges">
                            <?php if (!empty($p['is_featured'])): ?>
                                <span class="hp-badge-new">NEW</span>
                            <?php endif; ?>
                            <?php if ($hasSale): ?>
                                <span class="hp-badge-sale">-<?= $discount ?>%</span>
                            <?php endif; ?>
                        </div>

                        <!-- Wishlist -->
                        <button type="button" class="hp-wish-btn" title="Thêm vào yêu thích"
                                onclick="toggleWishlistHome(this, <?= $p['id'] ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24"
                                 fill="<?= $isWished ? '#FF3333' : 'none' ?>"
                                 stroke="<?= $isWished ? '#FF3333' : '#141718' ?>" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        <!-- Add to cart overlay -->
                        <div class="hp-cart-overlay">
                            <form action="../controllers/CartController.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button class="hp-btn-cart" type="submit">Add to cart</button>
                            </form>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="hp-info">
                        <div class="hp-stars"><?= $starsHtml ?> <span style="color:#6C7275;font-size:11px;">(<?= $rating ?>)</span></div>
                        <a href="product_detail.php?id=<?= $p['id'] ?>" style="text-decoration:none;color:inherit;">
                            <div class="hp-name" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></div>
                        </a>
                        <div class="hp-price">
                            <span><?= formatVND($dispPrice) ?>₫</span>
                            <?php if ($hasSale): ?>
                                <span class="hp-price-old"><?= formatVND($p['price']) ?>₫</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

async function toggleWishlistHome(btn, productId) {
    const svgEl = btn.querySelector('svg');
    try {
        const formData = new FormData();
        formData.append('action', 'ajax_toggle_wishlist');
        formData.append('product_id', productId);
        const res = await fetch('../controllers/ProductController.php?action=ajax_toggle_wishlist', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.need_login) {
            window.location.href = 'login.php';
            return;
        }
        if (data.success) {
            const color = data.is_wished ? '#FF3333' : 'none';
            const stroke = data.is_wished ? '#FF3333' : '#141718';
            svgEl.setAttribute('fill', color);
            svgEl.setAttribute('stroke', stroke);
            // Micro bounce animation
            btn.style.transform = 'scale(1.35)';
            setTimeout(() => btn.style.transform = 'scale(1)', 220);
        }
    } catch (e) {
        console.error('Wishlist error:', e);
    }
}
</script>
<?php include '../includes/footer.php'; ?>