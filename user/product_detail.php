<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../config/database.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: shop.php");
    exit;
}

// 1. Fetch Product
$stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? AND p.is_active = 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: shop.php");
    exit;
}

// 2. Fetch Images
$imgStmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
$imgStmt->bind_param("i", $id);
$imgStmt->execute();
$images = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$all_images = [];
if ($product['thumbnail']) { $all_images[] = $product['thumbnail']; }
foreach ($images as $img) {
    if (!in_array($img['image_url'], $all_images)) {
        $all_images[] = $img['image_url'];
    }
}

// Fallback image
if (empty($all_images)) {
    $all_images[] = 'placeholder.jpg'; // We'll manage frontend fallback
}

// 3. Fetch Variants (Colors)
$varStmt = $conn->prepare("SELECT * FROM product_variants WHERE product_id = ?");
$varStmt->bind_param("i", $id);
$varStmt->execute();
$variants = $varStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 4. Fetch Reviews
$revStmt = $conn->prepare("
    SELECT r.*, u.name as user_name, u.avatar as user_avatar 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ? AND r.is_approved = 1
    ORDER BY r.created_at DESC
");
$revStmt->bind_param("i", $id);
$revStmt->execute();
$reviews = $revStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalReviews = count($reviews);
$avgRating = 0;
if ($totalReviews > 0) {
    $sum = 0;
    foreach ($reviews as $rev) { $sum += (int)$rev['rating']; }
    $avgRating = round($sum / $totalReviews, 1);
}

// Format Helper
function formatVND($price) {
    if (!$price) return '0';
    return number_format((int)$price, 0, ',', '.');
}

// Image Resolver
function getRealImage($imgStr) {
    if (strpos($imgStr, 'http') === 0) return htmlspecialchars($imgStr);
    return '../assets/product-images/' . htmlspecialchars($imgStr);
}

// Wishlist tracking
$user_id = $_SESSION['user']['id'] ?? null;
$isWished = false;
if ($user_id) {
    $ws_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $ws_stmt->bind_param("ii", $user_id, $id);
    $ws_stmt->execute();
    $isWished = ($ws_stmt->get_result()->num_rows > 0);
}

$hasSale = $product['sale_price'] && $product['price'] > $product['sale_price'];
$discount = $hasSale ? round((($product['price'] - $product['sale_price']) / $product['price']) * 100) : 0;
$displayPrice = $hasSale ? $product['sale_price'] : $product['price'];

$current_page = 'shop.php';
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
/* ────────────────────────────────────────────────────────
   PRODUCT DETAIL PAGE STYLES - 3LEGANT DESIGN
──────────────────────────────────────────────────────── */

.pd-wrap *, .pd-wrap *::before, .pd-wrap *::after {
    box-sizing: border-box;
}

.pd-wrap {
    max-width: 1120px; /* Exact match for beautiful container width */
    margin: 40px auto 80px;
    padding: 0 24px;
    font-family: 'Inter', sans-serif;
    color: #141718;
}

/* ── Breadcrumb ── */
.pd-breadcrumb {
    font-size: 14px;
    color: #6C7275;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.pd-breadcrumb a {
    color: #6C7275;
    text-decoration: none;
    transition: color 0.2s;
}
.pd-breadcrumb a:hover { color: #141718; }
.pd-breadcrumb .current { color: #141718; font-weight: 500; }

/* ── Top Section Grid ── */
.pd-top {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 60px;
    margin-bottom: 60px;
    align-items: start;
}

/* Gallery */
.pd-gallery {
    min-width: 0; /* Prevents grid column blowout */
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.pd-main-img-box {
    position: relative;
    width: 100%;
    /* Force 1:1 square but padding shrinks the internal image */
    aspect-ratio: 1 / 1;
    background: #F3F5F7;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;
    box-sizing: border-box;
}
.pd-main-img-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain; /* Prevents cropping tall/wide furniture */
    mix-blend-mode: multiply; /* Helps white background merge cleanly */
    transition: 0.3s;
}
.pd-badges {
    position: absolute;
    top: 20px;
    left: 20px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    z-index: 10;
}
.badge-new { background: #fff; color: #141718; padding: 4px 12px; font-size: 12px; font-weight: 700; border-radius: 4px; border: 1px solid #E8ECEF;}
.badge-sale { background: #38CB89; color: #fff; padding: 4px 12px; font-size: 12px; font-weight: 700; border-radius: 4px;}

.pd-thumbs-wrap {
    position: relative;
    width: 100%;
}
.pd-thumbs {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE and Edge */
}
.pd-thumbs::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
}
.pd-thumb {
    width: 100px;
    height: 100px;
    border-radius: 6px;
    background: #F3F5F7;
    cursor: pointer;
    border: 2px solid transparent;
    transition: 0.2s;
    flex-shrink: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}
.pd-thumb img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    mix-blend-mode: multiply;
}
.pd-thumb.active { border-color: #141718; }
.pd-thumb:hover { border-color: #6C7275; }

.thumb-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 32px;
    height: 32px;
    background: #fff;
    border: 1px solid #E8ECEF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transition: 0.2s;
    font-size: 14px;
    color: #141718;
    z-index: 5;
    margin-top: -6px; /* Offset for scrollbar height */
}
.thumb-nav:hover {
    background: #141718;
    color: #fff;
}
.thumb-nav.left { left: 8px; }
.thumb-nav.right { right: 8px; }

/* Info Column */
.pd-info {
    min-width: 0; /* Prevents grid column blowout */
    display: flex;
    flex-direction: column;
}
.pd-stars {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.stars-icons { color: #141718; font-size: 14px; letter-spacing: 2px;}
.stars-text { font-size: 14px; color: #6C7275; }

.pd-title {
    font-size: 40px;
    font-weight: 600;
    margin: 0 0 16px;
    line-height: 1.2;
    font-family: 'Poppins', sans-serif;
    letter-spacing: -0.5px;
    /* Limit title length to 2 lines */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.pd-desc {
    font-size: 16px;
    color: #6C7275;
    line-height: 1.6;
    margin-bottom: 24px;
    /* Limit description to 3 lines */
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    overflow-wrap: break-word;
    white-space: normal;
}

.pd-price-row {
    display: flex;
    align-items: baseline;
    gap: 14px;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #E8ECEF;
}
.pd-price-main { font-size: 28px; font-weight: 600; color: #141718; }
.pd-price-old { font-size: 20px; font-weight: 400; color: #6C7275; text-decoration: line-through; }

/* Countdown */
.pd-timer-box { margin-bottom: 32px; }
.timer-label { font-size: 16px; font-weight: 500; color: #141718; margin-bottom: 12px; display:block; }
.timer-blocks { display: flex; gap: 16px; }
.t-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 70px;
    height: 70px;
    background: #F3F5F7;
    border-radius: 8px;
}
.t-num { font-size: 24px; font-weight: 600; color: #141718; line-height: 1;}
.t-txt { font-size: 12px; color: #6C7275; margin-top: 4px; }

/* Meta Info */
.pd-meta-row { margin-bottom: 24px; font-size: 16px; color: #6C7275;}
.pd-meta-row strong { font-weight: 500; color: #141718; margin-right: 8px; }

/* Colors */
.pd-color-select { margin-bottom: 32px; }
.color-label { display: block; font-size: 16px; font-weight: 500; margin-bottom: 12px; color: #6C7275; }
.color-label span { color: #141718; font-weight: 600; }
.color-options { display: flex; gap: 16px; flex-wrap: wrap; }
.c-option {
    width: 50px;
    height: 50px;
    background: #F3F5F7;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid #E8ECEF;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.c-option img { width: 100%; height: 100%; object-fit: contain; }
.c-option.active { border: 2px solid #141718; }
.c-option:hover { border-color: #6C7275; }

/* Actions */
.pd-actions { display: grid; grid-template-columns: 140px 1fr; gap: 24px; margin-bottom: 32px; }
.pd-qty {
    display: flex;
    align-items: center;
    background: #F3F5F7;
    border-radius: 8px;
    height: 52px;
}
.qty-btn {
    width: 44px;
    height: 100%;
    background: none;
    border: none;
    font-size: 18px;
    font-weight: 500;
    cursor: pointer;
    color: #141718;
}
.qty-input {
    flex: 1;
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    border: none;
    background: none;
    outline: none;
}
.pd-wishlist-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 52px;
    background: #fff;
    border: 1.5px solid #141718;
    color: #141718;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
    width: 100%;
}
.pd-wishlist-btn:hover { background: #141718; color: #fff; }
.pd-wishlist-btn svg { transition: stroke 0.2s, fill 0.2s; }
.pd-wishlist-btn:hover svg { stroke: #fff; }

.pd-add-btn {
    grid-column: 1 / -1;
    height: 52px;
    background: #141718;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
}
.pd-add-btn:hover { background: #343839; }

.pd-meta-links {
    display: flex;
    flex-direction: column;
    gap: 12px;
    border-top: 1px solid #E8ECEF;
    padding-top: 24px;
    color: #6C7275;
    font-size: 15px;
}
.pd-meta-links strong { color: #141718; font-weight: 500; width: 80px; display: inline-block;}

/* ── Tabs / Accordions ── */
.pd-tabs-section {
    border-top: 1px solid #E8ECEF;
    margin-top: 60px;
    padding-top: 40px;
}
.tabs-nav {
    display: flex;
    gap: 40px;
    margin-bottom: 40px;
    border-bottom: 1px solid #E8ECEF;
}
.tab-link {
    font-size: 20px;
    font-weight: 500;
    color: #6C7275;
    cursor: pointer;
    padding-bottom: 16px;
    border-bottom: 2px solid transparent;
    transition: 0.2s;
    margin-bottom: -1px;
}
.tab-link.active {
    color: #141718;
    border-color: #141718;
}

/* Reviews List */
.reviews-wrap { display: block; }
.rev-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}
.rev-title { font-size: 24px; font-weight: 600; color: #141718;}
.rev-sort {
    border: 1px solid #E8ECEF;
    padding: 8px 16px;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
    color: #141718;
    outline: none;
    cursor: pointer;
}
.review-item {
    display: flex;
    gap: 24px;
    padding-bottom: 32px;
    margin-bottom: 32px;
    border-bottom: 1px solid #E8ECEF;
}
.rev-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    background: #e8ecef;
}
.rev-content { flex: 1; }
.rev-c-hdr { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.rev-name { font-size: 18px; font-weight: 600; color: #141718; }
.rev-stars { color: #141718; letter-spacing: 2px; font-size: 14px;}
.rev-txt { font-size: 16px; color: #6C7275; line-height: 1.6; margin-bottom: 16px; }
.rev-actions { display: flex; gap: 16px; font-size: 14px; font-weight: 500; color: #6C7275; }
.rev-act-btn { cursor: pointer; transition: color 0.1s; }
.rev-act-btn:hover { color: #141718; }

.load-more-wrap { text-align: center; margin-top: 40px; }
.btn-load-more {
    background: none;
    border: 1.5px solid #141718;
    color: #141718;
    padding: 10px 40px;
    border-radius: 40px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
}
.btn-load-more:hover { background: #141718; color: #fff; }

/* Tab contents to hide by default */
.tab-pane { display: none; }
.tab-pane.active { display: block; animation: fadeIn 0.4s ease; }
@keyframes fadeIn { from {opacity: 0; transform: translateY(10px);} to {opacity: 1; transform: translateY(0);} }

/* Newsletter */
.newsletter {
    background: #F3F5F7;
    text-align: center;
    padding: 80px 24px;
    margin-top: 80px;
}
.newsletter-inner { max-width: 440px; margin: 0 auto; }
.newsletter h2 { font-size: 36px; font-weight: 500; margin: 0 0 10px; font-family: 'Poppins', sans-serif;}
.newsletter p { font-size: 15px; color: #141718; margin: 0 0 32px; }
.newsletter-form {
    display: flex;
    align-items: center;
    border-bottom: 1.5px solid #141718;
    padding-bottom: 8px;
    gap: 8px;
}
.newsletter-form input {
    flex: 1; border: none; outline: none; background: transparent; font-size: 15px; color: #141718;
}
.newsletter-form button { background: none; border: none; font-size: 15px; font-weight: 600; cursor: pointer; }

/* Responsive Mobile */
@media (max-width: 900px) {
    .pd-top { grid-template-columns: 1fr; gap: 40px; }
    .pd-gallery { max-width: 600px; margin: 0 auto; }
    .pd-title { font-size: 32px; }
    .tabs-nav { gap: 20px; overflow-x: auto; white-space: nowrap; padding-bottom: 4px; margin-bottom: 30px;}
    .tab-link { font-size: 16px; }
    .pd-actions { grid-template-columns: 1fr 1fr; }
    .pd-add-btn { grid-column: 1 / -1; }
}
@media (max-width: 600px) {
    .timer-blocks { gap: 10px; }
    .t-block { width: 60px; height: 60px; }
    .t-num { font-size: 20px; }
    .pd-actions { grid-template-columns: 120px 1fr; }
    .pd-wishlist-btn { padding: 0 12px; font-size: 14px;}
    .review-item { flex-direction: column; gap: 16px; }
}
</style>

<div class="pd-wrap">
    
    <!-- Breadcrumb -->
    <nav class="pd-breadcrumb">
        <a href="index.php">Home</a> > 
        <a href="shop.php">Shop</a> > 
        <a href="shop.php?cat=<?= htmlspecialchars($product['category_slug']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> > 
        <span class="current">Product</span>
    </nav>

    <!-- Top Section (Gallery + Info) -->
    <div class="pd-top">
        
        <!-- Left: Gallery -->
        <div class="pd-gallery">
            <div class="pd-main-img-box">
                <!-- Badges -->
                <div class="pd-badges">
                    <?php if ($product['is_featured']): ?><span class="badge-new">NEW</span><?php endif; ?>
                    <?php if ($discount > 0): ?><span class="badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                </div>
                <!-- Main Image -->
                <?php $mainImg = getRealImage($all_images[0] ?? 'placeholder.jpg'); ?>
                <img id="mainGalleryImage" src="<?= $mainImg ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>

            <div class="pd-thumbs-wrap">
                <button class="thumb-nav left" onclick="scrollThumbs(-1)">&#10094;</button>
                <div class="pd-thumbs" id="pdThumbs">
                    <?php foreach($all_images as $index => $imgName): 
                        $thumbSrc = getRealImage($imgName);
                    ?>
                    <div class="pd-thumb <?= $index === 0 ? 'active' : '' ?>" onclick="switchGalleryImage(this, '<?= $thumbSrc ?>')">
                        <img src="<?= $thumbSrc ?>" alt="thumb">
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="thumb-nav right" onclick="scrollThumbs(1)">&#10095;</button>
            </div>
        </div>

        <!-- Right: Info Panel -->
        <div class="pd-info">
            <!-- Stars -->
            <div class="pd-stars">
                <div class="stars-icons">
                    <?php 
                    $fullStars = floor($avgRating);
                    for($i=0; $i<5; $i++){
                        echo $i < $fullStars ? '★' : '☆';
                    }
                    ?>
                </div>
                <div class="stars-text"><?= $totalReviews ?> Reviews</div>
            </div>

            <!-- Title & Desc -->
            <h1 class="pd-title"><?= htmlspecialchars($product['name']) ?></h1>
            <?php
            $shortDesc = !empty($product['short_desc']) ? $product['short_desc'] : 'Buy one or buy a few and make every space where you sit more convenient. Light and easy to move around with removable tray top, handy for serving snacks.';
            
            /* Magic AI Text Formatter to fix broken crawler data */
            // 1. Lowercase or Number followed by Uppercase -> Add ". " (e.g., sản phẩmMàu -> sản phẩm. Màu)
            $shortDesc = preg_replace('/(\p{Ll}|\p{N})(\p{Lu})/u', '$1. $2', $shortDesc);
            
            // 2. Acronyms followed by Capitalized Word -> Add Space (e.g., PPĐặc -> PP Đặc)
            $shortDesc = preg_replace('/(\p{Lu})(\p{Lu}\p{Ll})/u', '$1 $2', $shortDesc);
            ?>
            <p class="pd-desc"><?= htmlspecialchars($shortDesc) ?></p>

            <!-- Price -->
            <div class="pd-price-row">
                <span class="pd-price-main"><?= formatVND($displayPrice) ?>₫</span>
                <?php if ($hasSale): ?>
                    <span class="pd-price-old"><?= formatVND($product['price']) ?>₫</span>
                <?php endif; ?>
            </div>

            <!-- Fake Countdown Timer (Static visual as requested) -->
            <div class="pd-timer-box">
                <span class="timer-label">Offer expires in:</span>
                <div class="timer-blocks">
                    <div class="t-block"><span class="t-num">02</span><span class="t-txt">Days</span></div>
                    <div class="t-block"><span class="t-num">12</span><span class="t-txt">Hours</span></div>
                    <div class="t-block"><span class="t-num">45</span><span class="t-txt">Minutes</span></div>
                    <div class="t-block"><span class="t-num">05</span><span class="t-txt">Seconds</span></div>
                </div>
            </div>

            <!-- Specs -->
            <?php if (!empty($product['size']) || !empty($product['measurements'])): ?>
            <div class="pd-meta-row">
                <strong>Measurements:</strong> <?= htmlspecialchars($product['size'] ?? ($product['measurements'] ?? '17 1/2*20 5/8 "')) ?>
            </div>
            <?php else: ?>
            <div class="pd-meta-row">
                <strong>Measurements:</strong> 17 1/2*20 5/8 "
            </div>
            <?php endif; ?>

            <!-- Authentic Options from DB Variants -->
            <?php if (!empty($variants)): ?>
            <div class="pd-color-select">
                <?php 
                    $firstVarTitle = !empty($variants[0]['color']) ? $variants[0]['color'] : (!empty($variants[0]['size']) ? $variants[0]['size'] : 'Standard');
                ?>
                <label class="color-label">Choose Option > <span id="colorNameLabel"><?= htmlspecialchars($firstVarTitle) ?></span></label>
                <div class="color-options">
                    <?php foreach($variants as $index => $var): 
                        $varImg = !empty($var['image']) ? $var['image'] : (!empty($all_images[0]) ? $all_images[0] : 'placeholder.jpg');
                        $varTitle = !empty($var['color']) ? $var['color'] : (!empty($var['size']) ? $var['size'] : 'Standard');
                    ?>
                    <div class="c-option <?= $index === 0 ? 'active' : '' ?>" title="<?= htmlspecialchars($varTitle) ?>" onclick="selectColor(this, '<?= htmlspecialchars($varTitle) ?>')">
                        <img src="<?= htmlspecialchars(getRealImage($varImg)) ?>" alt="<?= htmlspecialchars($varTitle) ?>" onerror="this.src='<?= htmlspecialchars(getRealImage($mainImg)) ?>'">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Actions Form -->
            <form action="../controllers/CartController.php?action=add" method="POST" id="addToCartForm">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="pd-actions">
                    <!-- Qty Spinner -->
                    <div class="pd-qty">
                        <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                        <input type="number" id="qtyInput" name="quantity" class="qty-input" value="1" min="1" max="<?= $product['stock'] > 0 ? $product['stock'] : 99 ?>" readonly>
                        <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                    </div>

                    <!-- Wishlist Toggle Button -->
                    <button type="button" class="pd-wishlist-btn" id="wishlistToggleBtn" onclick="toggleDetailWishlist(<?= $product['id'] ?>)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="<?= $isWished ? '#FF3333' : 'none' ?>" stroke="<?= $isWished ? '#FF3333' : '#141718' ?>" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span id="wishBtnText">Wishlist</span>
                    </button>

                    <!-- Add to Cart -->
                    <button type="submit" class="pd-add-btn">Add to Cart</button>
                </div>
            </form>

            <div class="pd-meta-links">
                <div><strong>SKU</strong> : <?= htmlspecialchars(!empty($product['sku']) ? $product['sku'] : 'N/A') ?></div>
                <div><strong>Category</strong> : <?= htmlspecialchars($product['category_name']) ?></div>
            </div>
        </div>
    </div> <!-- /pd-top -->

    <!-- Tabs Area -->
    <div class="pd-tabs-section">
        <div class="tabs-nav">
            <div class="tab-link active" onclick="openTab('tab-additional')">Additional Info</div>
            <div class="tab-link" onclick="openTab('tab-questions')">Questions</div>
            <div class="tab-link" onclick="openTab('tab-reviews')">Reviews</div>
        </div>

        <div class="tab-contents">
            <!-- TAB: Additional Info -->
            <div id="tab-additional" class="tab-pane active">
                <div style="font-size: 16px; color: #6C7275; line-height: 1.8;">
                    <h3 style="color:#141718; font-weight:600; margin-top:0;">Details</h3>
                    <p><?= nl2br(htmlspecialchars(!empty($product['description']) ? $product['description'] : 'You can use the removable tray for serving. The design makes it easy to put the tray back after use since you place it directly on the table frame without having to fit it into any holes.')) ?></p>
                    <br>
                    <h3 style="color:#141718; font-weight:600; margin-top:0;">Material & Care</h3>
                    <ul>
                        <li>Material: <?= htmlspecialchars(!empty($product['material']) ? $product['material'] : 'Powder-coated steel') ?></li>
                        <li>Wipe clean with a damp cloth</li>
                        <li>Check regularly that all assembly fastenings are properly tightened</li>
                    </ul>
                </div>
            </div>

            <!-- TAB: Questions -->
            <div id="tab-questions" class="tab-pane">
                <div style="text-align:center; padding: 40px; color:#6C7275;">
                    <p>No questions yet. Be the first to ask a question!</p>
                </div>
            </div>

            <!-- TAB: Reviews -->
            <div id="tab-reviews" class="tab-pane">
                <div class="reviews-wrap">
                    <div class="rev-header-bar">
                        <div class="rev-title">Customer Reviews</div>
                        <select class="rev-sort">
                            <option value="newest">Newest</option>
                            <option value="highest">Highest Rating</option>
                        </select>
                    </div>

                    <?php if (empty($reviews)): ?>
                        <div style="text-align:center; padding: 40px; color:#6C7275; border: 1px dashed #E8ECEF; border-radius: 8px;">
                            <p>No reviews yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($reviews as $rev): 
                            $avatar = strpos(!empty($rev['user_avatar']) ? $rev['user_avatar'] : '', 'http') === 0 ? $rev['user_avatar'] : '../assets/images/' . (!empty($rev['user_avatar']) ? $rev['user_avatar'] : 'default.jpg');
                        ?>
                        <div class="review-item">
                            <img src="<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($rev['user_name']) ?>" class="rev-avatar" onerror="this.src='../assets/images/default.jpg'">
                            <div class="rev-content">
                                <div class="rev-name"><?= htmlspecialchars($rev['user_name']) ?></div>
                                <div class="rev-c-hdr">
                                    <div class="rev-stars">
                                        <?php 
                                        $rStars = (int)$rev['rating'];
                                        for($i=0; $i<5; $i++) echo $i < $rStars ? '★' : '☆';
                                        ?>
                                    </div>
                                </div>
                                <div class="rev-txt"><?= nl2br(htmlspecialchars(!empty($rev['comment']) ? $rev['comment'] : 'Great product!')) ?></div>
                                <div class="rev-actions">
                                    <span class="rev-act-btn">Like</span>
                                    <span class="rev-act-btn">Reply</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if($totalReviews > 3): ?>
                        <div class="load-more-wrap">
                            <button class="btn-load-more">Load more</button>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div> <!-- /tabs -->

</div> <!-- /pd-wrap -->

<!-- Newsletter -->
<section class="newsletter">
    <div class="newsletter-inner">
        <h2>Join Our Newsletter</h2>
        <p>Sign up for deals, new products and promotions</p>
        <form class="newsletter-form" onsubmit="return false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9BA3AF" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <input type="email" placeholder="Email address" required>
            <button type="submit">Signup</button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
// 1. Gallery Image Switching
function switchGalleryImage(el, src) {
    document.getElementById('mainGalleryImage').src = src;
    
    // Remove active from all thumbs
    document.querySelectorAll('.pd-thumb').forEach(thumb => {
        thumb.classList.remove('active');
    });
    // Add active state to thumbnail
    el.classList.add('active');
}

function scrollThumbs(direction) {
    const container = document.getElementById('pdThumbs');
    // Scroll amount is thumbnail width (100) + gap (16)
    const scrollAmount = 116 * 2; 
    container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
}

// 2. Select Color Option
function selectColor(el, colorName) {
    document.getElementById('colorNameLabel').innerText = colorName;
    document.querySelectorAll('.c-option').forEach(opt => {
        opt.classList.remove('active');
    });
    el.classList.add('active');
    
    // Switch main image to this color's image
    const imgSrc = el.querySelector('img').src;
    document.getElementById('mainGalleryImage').src = imgSrc;
}

// 3. Tab Navigation
function openTab(tabId) {
    // Hide all tabs
    document.querySelectorAll('.tab-pane').forEach(pane => {
        pane.classList.remove('active');
    });
    // Reset nav links
    document.querySelectorAll('.tab-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Activate target
    document.getElementById(tabId).classList.add('active');
    // Activate link (find the one that was clicked)
    event.target.classList.add('active');
}

// 4. Quantity Spinner
function updateQty(delta) {
    const input = document.getElementById('qtyInput');
    let current = parseInt(input.value) || 1;
    let max = parseInt(input.max) || 99;
    
    let nextVal = current + delta;
    if (nextVal < 1) nextVal = 1;
    if (nextVal > max) nextVal = max;
    
    input.value = nextVal;
}

// 5. AJAX Wishlist Toggle
async function toggleDetailWishlist(productId) {
    // Ensure we send post data
    try {
        const fd = new FormData();
        fd.append('product_id', productId);
        
        const btn = document.getElementById('wishlistToggleBtn');
        const svg = btn.querySelector('svg');
        
        const res = await fetch('../controllers/ProductController.php?action=ajax_toggle_wishlist', {
            method: 'POST',
            body: fd
        });
        const data = await res.json();
        
        if (data.need_login) {
            window.location.href = 'login.php';
            return;
        }
        
        if (data.success) {
            if (data.is_wished) {   
                svg.setAttribute('fill', '#FF3333');
                svg.setAttribute('stroke', '#FF3333');
            } else {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', '#141718');
            }
        }
    } catch(e) {
        console.error('Failed to toggle wishlist', e);
    }
}
</script>
</body>
</html>
