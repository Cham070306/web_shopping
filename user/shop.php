<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once "../config/database.php";

$current_page = 'shop.php';

$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 9;
$offset = ($page - 1) * $limit;

// Category filter
$catSlug    = isset($_GET['cat']) ? trim($_GET['cat']) : '';
$catId      = 0;
$catLabel   = 'All Rooms';

if ($catSlug) {
    $stmtCat = $conn->prepare("SELECT id, name FROM categories WHERE slug = ? LIMIT 1");
    $stmtCat->bind_param("s", $catSlug);
    $stmtCat->execute();
    $catRow = $stmtCat->get_result()->fetch_assoc();
    if ($catRow) {
        $catId    = (int)$catRow['id'];
        $catLabel = $catRow['name'];
    }
}

// Fetch categories for sidebar
$allCategories = $conn->query("SELECT id, name, slug FROM categories ORDER BY id")->fetch_all(MYSQLI_ASSOC);

// Price filter
$priceFilter = isset($_GET['price']) ? trim($_GET['price']) : '';
$minPrice = null;
$maxPrice = null;

if ($priceFilter) {
    $parts = explode('-', $priceFilter);
    if (count($parts) === 2) {
        if ($parts[0] !== '') $minPrice = (int)$parts[0];
        if ($parts[1] !== '') $maxPrice = (int)$parts[1];
    }
}

// Build query conditions
$conditions = ["is_active = 1"];
$params = [];
$types = "";

if ($catId) {
    $conditions[] = "category_id = ?";
    $params[] = $catId;
    $types .= "i";
}

if ($minPrice !== null) {
    $conditions[] = "(COALESCE(sale_price, price) >= ?)";
    $params[] = $minPrice;
    $types .= "d";
}
if ($maxPrice !== null) {
    $conditions[] = "(COALESCE(sale_price, price) <= ?)";
    $params[] = $maxPrice;
    $types .= "d";
}

$whereSql = implode(" AND ", $conditions);

// Count query
$cntSql = "SELECT COUNT(*) as total FROM products WHERE $whereSql";
$cntQ = $conn->prepare($cntSql);
if ($types) {
    $cntQ->bind_param($types, ...$params);
}
$cntQ->execute();
$totalProducts = $cntQ->get_result()->fetch_assoc()['total'];
$totalPages    = ceil($totalProducts / $limit) ?: 1;

// Sort param
$sortCode = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
$orderBy = "ORDER BY id DESC";
if ($sortCode === 'price_asc') {
    $orderBy = "ORDER BY COALESCE(sale_price, price) ASC";
} elseif ($sortCode === 'price_desc') {
    $orderBy = "ORDER BY COALESCE(sale_price, price) DESC";
}

// Fetch query
$sql = "SELECT p.*, (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = p.id AND is_approved = 1) as avg_rating 
        FROM products p 
        WHERE $whereSql $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);

$fetchParams = $params;
$fetchParams[] = $limit;
$fetchParams[] = $offset;
$fetchTypes = $types . "ii";

$stmt->bind_param($fetchTypes, ...$fetchParams);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch user wishlist
$user_id = $_SESSION['user']['id'] ?? null;
$wishedIds = [];
if ($user_id) {
    $ws_stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $ws_stmt->bind_param("i", $user_id);
    $ws_stmt->execute();
    $ws_res = $ws_stmt->get_result();
    while ($row = $ws_res->fetch_assoc()) {
        $wishedIds[] = $row['product_id'];
    }
}


function formatVND($price) {
    if (!$price) return '0';
    return number_format((int)$price, 0, ',', '.');
}

function getLinkUrl($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') unset($params[$k]);
        else $params[$k] = $v;
    }
    return $params ? 'shop.php?' . http_build_query($params) : 'shop.php';
}
?>
<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
/* ══════════════════════════════════════════
   SHOP PAGE — 3legant Design
   ══════════════════════════════════════════ */

/* ── Banner ── */
.shop-hero {
    position: relative;
    height: 280px;
    background: url('../assets/images/banner.jpg') center/cover no-repeat;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    margin-bottom: 0;
}
.shop-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.55);
}
.shop-hero-inner { position: relative; z-index: 1; }
.shop-breadcrumb {
    font-size: 13px;
    color: #6C7275;
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
}
.shop-breadcrumb a { color: #6C7275; text-decoration: none; }
.shop-breadcrumb a:hover { color: #141718; }
.shop-hero h1 {
    font-family: 'Poppins', sans-serif;
    font-size: 48px;
    font-weight: 500;
    color: #141718;
    margin: 0 0 10px;
}
.shop-hero p { font-size: 18px; color: #141718; margin: 0; }

/* ── Layout ── */
.shop-wrap {
    max-width: 1120px;
    margin: 0 auto;
    padding: 48px 24px 80px;
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 40px;
    align-items: start;
}

/* ── Sidebar ── */
.shop-sidebar {}
.sidebar-filter-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 20px;
    font-weight: 600;
    color: #141718;
    margin-bottom: 32px;
    padding-bottom: 16px;
    border-bottom: 1px solid #E8ECEF;
}
.sb-section { margin-bottom: 36px; }
.sb-section h4 {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #141718;
    margin: 0 0 16px;
}
.category-list { list-style: none; margin: 0; padding: 0; }
.category-list li { margin-bottom: 10px; }
.category-list a {
    font-size: 14px;
    color: #6C7275;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: color .18s;
}
.category-list a:hover, .category-list a.active { color: #141718; font-weight: 600; }
.category-list a.active { border-left: 2px solid #141718; padding-left: 8px; margin-left: -2px; }

.price-list { list-style: none; margin: 0; padding: 0; }
.price-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.price-list label {
    font-size: 14px;
    color: #6C7275;
    cursor: pointer;
    flex: 1;
}
.price-list input[type="checkbox"] {
    width: 18px;
    height: 18px;
    border-radius: 3px;
    accent-color: #141718;
    cursor: pointer;
    flex-shrink: 0;
}

/* ── Main content ── */
.shop-main {}

/* ── Toolbar ── */
.shop-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 16px;
    border-bottom: 1px solid #E8ECEF;
}
.toolbar-cat { font-size: 20px; font-weight: 600; color: #141718; }
.toolbar-right { display: flex; align-items: center; gap: 16px; }
.sort-select-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #141718;
}
.sort-select-wrap select {
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    background: transparent;
    cursor: pointer;
    color: #141718;
}
.view-icons { display: flex; gap: 6px; }
.view-icon-btn {
    width: 34px;
    height: 34px;
    border: 1px solid #E8ECEF;
    border-radius: 4px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: border-color .18s, background .18s;
}
.view-icon-btn.active, .view-icon-btn:hover {
    border-color: #141718;
    background: #F3F5F7;
}

/* ── Product Grid ── */
.product-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    align-items: stretch; /* all cards in a row are equal height */
}
.product-grid.list-view {
    grid-template-columns: 1fr;
}
.product-grid.list-view .product-card {
    flex-direction: row;
    align-items: center;
    gap: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid #E8ECEF;
}
.product-grid.list-view .product-img-box {
    width: 260px;
    height: 260px;
    margin-bottom: 0;
}
.product-grid.list-view .card-info {
    justify-content: center;
}
.product-grid.list-view .card-name {
    font-size: 18px;
    min-height: auto;
}
.product-grid.list-view .card-price {
    font-size: 16px;
}

/* ── Product Card ── */
.product-card {
    display: flex;
    flex-direction: column;
    cursor: pointer;
    height: 100%;
}
.product-img-box {
    position: relative;
    background: #F3F5F7;
    border-radius: 6px;
    overflow: hidden;
    width: 100%;
    /* Fixed height so every card image is exactly the same size */
    height: 260px;
    flex-shrink: 0;
    margin-bottom: 14px;
}
.product-img-box img {
    width: 100%;
    height: 100%;
    /* contain keeps full image visible without cropping */
    object-fit: contain;
    padding: 12px;
    transition: transform .4s ease;
    box-sizing: border-box;
}
.product-card:hover .product-img-box img { transform: scale(1.04); }

/* Card info grows to fill remaining space so price always at bottom */
.card-info {
    display: flex;
    flex-direction: column;
    flex: 1;
}
.card-name {
    flex: 1;
}

/* badges */
.card-badges {
    position: absolute;
    top: 14px;
    left: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    z-index: 2;
}
.badge-new {
    background: #fff;
    color: #141718;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 4px;
    letter-spacing: .5px;
    display: inline-block;
}
.badge-sale {
    background: #38CB89;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 4px;
    letter-spacing: .5px;
    display: inline-block;
}

/* wishlist heart */
.card-wish-btn {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 2;
    background: #fff;
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,.10);
    cursor: pointer;
    transition: background .18s;
}
.card-wish-btn:hover { background: #F3F5F7; }

/* add to cart overlay */
.card-cart-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 14px;
    opacity: 0;
    transform: translateY(12px);
    transition: all .28s ease;
    z-index: 2;
}
.product-img-box:hover .card-cart-overlay {
    opacity: 1;
    transform: translateY(0);
}
.btn-cart {
    width: 100%;
    background: #141718;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    padding: 8px;
    cursor: pointer;
    transition: background .18s;
}
.btn-cart:hover { background: #343839; }

/* card info */
.card-stars { color: #141718; font-size: 11px; margin-bottom: 6px; letter-spacing: 1px; }
.card-name {
    font-size: 15px;
    font-weight: 600;
    color: #141718;
    margin-bottom: 8px;
    /* Allow 2 lines, then ellipsis */
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.45;
    min-height: 44px; /* reserve space for 2 lines always */
}
.card-price { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 600; color: #141718; margin-top: auto; padding-top: 4px; }
.card-price-old { color: #6C7275; text-decoration: line-through; font-weight: 400; font-size: 13px; }

/* ── Show More ── */
.show-more-row { text-align: center; margin-top: 48px; }
.btn-show-more {
    background: #fff;
    color: #141718;
    border: 1.5px solid #141718;
    border-radius: 40px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 500;
    padding: 10px 44px;
    cursor: pointer;
    transition: background .2s, color .2s;
}
.btn-show-more:hover { background: #141718; color: #fff; }

/* ── Newsletter ── */
.newsletter {
    background: #F3F5F7;
    text-align: center;
    padding: 80px 24px;
}
.newsletter-inner { max-width: 440px; margin: 0 auto; }
.newsletter h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 36px;
    font-weight: 500;
    color: #141718;
    margin: 0 0 10px;
}
.newsletter p { font-size: 15px; color: #141718; margin: 0 0 32px; }
.newsletter-form {
    display: flex;
    align-items: center;
    border-bottom: 1.5px solid #141718;
    padding-bottom: 8px;
    gap: 8px;
}
.newsletter-form svg { flex-shrink: 0; }
.newsletter-form input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-family: inherit;
    font-size: 15px;
    color: #141718;
}
.newsletter-form input::placeholder { color: #9BA3AF; }
.newsletter-form button {
    background: none;
    border: none;
    font-family: inherit;
    font-size: 15px;
    font-weight: 600;
    color: #141718;
    cursor: pointer;
    white-space: nowrap;
}

/* ── Pagination ── */
.pagination-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 48px;
}
.page-btn {
    min-width: 36px;
    height: 36px;
    border: 1.5px solid #E8ECEF;
    border-radius: 6px;
    background: #fff;
    font-family: inherit;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .18s;
    color: #141718;
    text-decoration: none;
    padding: 0 10px;
}
.page-btn:hover, .page-btn.active { background: #141718; color: #fff; border-color: #141718; }

/* ── No Products ── */
.empty-state {
    grid-column: 1/-1;
    text-align: center;
    padding: 60px 20px;
    color: #6C7275;
    font-size: 16px;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
    .shop-wrap { grid-template-columns: 190px 1fr; gap: 28px; }
    .product-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
}
@media (max-width: 700px) {
    .shop-wrap { grid-template-columns: 1fr; }
    .shop-sidebar { display: none; }
    .shop-hero h1 { font-size: 36px; }
    .shop-hero { height: 220px; }
    .product-grid { grid-template-columns: repeat(2, 1fr); gap: 14px; }
}
</style>

<!-- ── Hero Banner ── -->
<section class="shop-hero">
    <div class="shop-hero-inner">
        <nav class="shop-breadcrumb" aria-label="breadcrumb">
            <a href="index.php">Home</a>
            <span>›</span>
            <span>Shop</span>
        </nav>
        <h1>Shop Page</h1>
        <p>Let's design the place you always imagined.</p>
    </div>
</section>

<!-- ── Main Content ── -->
<div class="shop-wrap">

    <!-- Sidebar -->
    <aside class="shop-sidebar">
        <div class="sidebar-filter-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
            </svg>
            Filter
        </div>

        <div class="sb-section">
            <h4>Categories</h4>
            <ul class="category-list">
                <li><a href="<?= getLinkUrl(['cat' => null, 'page' => null]) ?>"<?= !$catSlug ? ' class="active"' : '' ?>>All Rooms</a></li>
                <?php foreach ($allCategories as $cat): ?>
                    <li><a href="<?= getLinkUrl(['cat' => $cat['slug'], 'page' => null]) ?>"<?= $catSlug === $cat['slug'] ? ' class="active"' : '' ?>><?= htmlspecialchars($cat['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="sb-section">
            <h4>Price</h4>
            <ul class="price-list">
                <?php 
                $prices = [
                    '' => 'All Price',
                    '0-500000' => 'Under 500K',
                    '500000-1000000' => '500K – 1 triệu',
                    '1000000-3000000' => '1 – 3 triệu',
                    '3000000-5000000' => '3 – 5 triệu',
                    '5000000-' => 'Trên 5 triệu'
                ];
                $i = 1;
                foreach ($prices as $val => $label): 
                    $isChecked = ($priceFilter === $val);
                    $link = getLinkUrl(['price' => $val, 'page' => null]);
                    if ($isChecked && $val !== '') {
                        $link = getLinkUrl(['price' => null, 'page' => null]);
                    }
                ?>
                <li>
                    <label for="p_<?= $i ?>" style="color: <?= $isChecked ? '#141718' : '#6C7275' ?>; font-weight: <?= $isChecked ? '600' : '400' ?>; cursor: pointer;"><?= $label ?></label>
                    <input type="checkbox" id="p_<?= $i ?>" <?= $isChecked ? 'checked' : '' ?> onchange="applyFilter('<?= $link ?>')">
                </li>
                <?php 
                $i++;
                endforeach; 
                ?>
            </ul>
        </div>
    </aside>

    <!-- Products area -->
    <main class="shop-main">

        <!-- Toolbar -->
        <div class="shop-toolbar">
            <span class="toolbar-cat"><?= htmlspecialchars($catLabel) ?> (<?= $totalProducts ?>)</span>
            <div class="toolbar-right">
                <div class="sort-select-wrap">
                    Sort by
                    <select id="sort-select" onchange="applySort(this.value)">
                        <option value="newest" <?= $sortCode === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="price_asc" <?= $sortCode === 'price_asc' ? 'selected' : '' ?>>Price ↑</option>
                        <option value="price_desc" <?= $sortCode === 'price_desc' ? 'selected' : '' ?>>Price ↓</option>
                    </select>
                </div>
                <div class="view-icons">
                    <div class="view-icon-btn active" data-view="grid" title="Grid view" onclick="toggleView('grid')">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" style="pointer-events:none;">
                            <rect x="0" y="0" width="7" height="7" rx="1"/><rect x="9" y="0" width="7" height="7" rx="1"/>
                            <rect x="0" y="9" width="7" height="7" rx="1"/><rect x="9" y="9" width="7" height="7" rx="1"/>
                        </svg>
                    </div>
                    <div class="view-icon-btn" data-view="list" title="List view" onclick="toggleView('list')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="pointer-events:none;">
                            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                            <line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1.5" fill="currentColor"/>
                            <circle cx="3" cy="12" r="1.5" fill="currentColor"/><circle cx="3" cy="18" r="1.5" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="product-grid" id="productGrid">
            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" style="margin-bottom:16px"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <p>Chưa có sản phẩm nào. Vui lòng chạy crawler để import dữ liệu.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $p):
                    $thumb = trim($p['thumbnail'] ?? '');
                    // Use direct URL if it starts with http, else fallback to local asset
                    if (strpos($thumb, 'http') === 0) {
                        $img = htmlspecialchars($thumb);
                    } elseif ($thumb) {
                        $img = '../assets/product-images/' . htmlspecialchars($thumb);
                    } else {
                        $img = '../assets/images/sofa.jpg';
                    }
                    $hasSale = $p['sale_price'] && $p['price'] > $p['sale_price'];
                    $discount = $hasSale ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
                    $displayPrice = $hasSale ? $p['sale_price'] : $p['price'];

                    $rating    = !empty($p['avg_rating']) ? (float)$p['avg_rating'] : 0;
                    $fullStars = min(5, (int)round($rating));
                    $starsHtml = str_repeat('★', $fullStars) . str_repeat('☆', 5 - $fullStars);
                ?>
                <div class="product-card">
                    <div class="product-img-box">
                        <a href="product_detail.php?id=<?= $p['id'] ?>" style="display:block; width:100%; height:100%;">
                            <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy" onerror="this.src='../assets/images/sofa.jpg'">
                        </a>

                        <!-- Badges -->
                        <div class="card-badges">
                            <?php if ($p['is_featured']): ?><span class="badge-new">NEW</span><?php endif; ?>
                            <?php if ($hasSale): ?><span class="badge-sale">-<?= $discount ?>%</span><?php endif; ?>
                        </div>

                        <!-- Wishlist btn -->
                        <?php $isWished = in_array($p['id'], $wishedIds); ?>
                        <button type="button" class="card-wish-btn" title="Toggle wishlist" onclick="toggleWishlist(this, <?= $p['id'] ?>)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="<?= $isWished ? '#FF3333' : 'none' ?>" stroke="<?= $isWished ? '#FF3333' : '#141718' ?>" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                            </svg>
                        </button>

                        <!-- Add to cart overlay -->
                        <div class="card-cart-overlay">
                            <form action="../controllers/CartController.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button class="btn-cart" type="submit">Add to cart</button>
                            </form>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="card-info">
                        <div class="card-stars">
                            <?php if ($rating > 0): ?>
                                <?= $starsHtml ?> <span style="color:#6C7275;font-size:11px;">(<?= number_format($rating, 1) ?>)</span>
                            <?php else: ?>
                                <span style="color:#6C7275;font-size:11px;">Chưa có đánh giá</span>
                            <?php endif; ?>
                        </div>
                        <a href="product_detail.php?id=<?= $p['id'] ?>" style="text-decoration:none; color:inherit;">
                            <div class="card-name" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></div>
                        </a>
                        <div class="card-price">
                            <span><?= formatVND($displayPrice) ?>₫</span>
                            <?php if ($hasSale): ?>
                                <span class="card-price-old"><?= formatVND($p['price']) ?>₫</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-row">
            <?php if ($page > 1): ?>
                <a class="page-btn" href="<?= getLinkUrl(['page' => $page - 1]) ?>">‹</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="page-btn <?= $i === $page ? 'active' : '' ?>" href="<?= getLinkUrl(['page' => $i]) ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a class="page-btn" href="<?= getLinkUrl(['page' => $page + 1]) ?>">›</a>
            <?php endif; ?>
        </div>
        <?php elseif (count($products) >= $limit): ?>
        <div class="show-more-row">
            <a class="btn-show-more" href="<?= getLinkUrl(['page' => $page + 1]) ?>">Show more</a>
        </div>
        <?php endif; ?>

    </main>
</div>

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
            <input type="email" placeholder="Email address" aria-label="Email address">
            <button type="submit">Signup</button>
        </form>
    </div>
</section>

<?php include '../includes/footer.php'; ?>

<script>
async function applyFilter(url) {
    try {
        const shopWrap = document.querySelector('.shop-wrap');
        shopWrap.style.opacity = '0.5';
        shopWrap.style.pointerEvents = 'none';

        const res = await fetch(url);
        const html = await res.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        
        // Update URL dynamically
        history.pushState(null, '', url);
        
        // Extract and inject targeted layout
        const newWrap = doc.querySelector('.shop-wrap');
        if (newWrap) {
            shopWrap.innerHTML = newWrap.innerHTML;
        }
        
        shopWrap.style.opacity = '1';
        shopWrap.style.pointerEvents = 'auto';
        
        // Re-apply view settings after layout is overwritten
        applyViewSetting();

        // Autoscroll upwards if user scrolled down far inside pagination
        const rect = shopWrap.getBoundingClientRect();
        if (rect.top < 0) {
            window.scrollTo({ top: window.scrollY + rect.top - 100, behavior: 'smooth' });
        }
    } catch (e) {
        console.error('AJAX filtering failed:', e);
        window.location.href = url; // Fallback 
    }
}

function applySort(val) {
    let url = new URL(window.location.href);
    url.searchParams.set('sort', val);
    url.searchParams.delete('page'); // Reset to page 1
    applyFilter(url.toString());
}

function toggleView(viewType) {
    localStorage.setItem('shop_view', viewType);
    applyViewSetting();
}

function applyViewSetting() {
    const viewType = localStorage.getItem('shop_view') || 'grid';
    const grid = document.getElementById('productGrid');
    const btns = document.querySelectorAll('.view-icon-btn');
    
    if (grid) {
        if (viewType === 'list') grid.classList.add('list-view');
        else grid.classList.remove('list-view');
    }
    
    btns.forEach(btn => {
        if (btn.dataset.view === viewType) btn.classList.add('active');
        else btn.classList.remove('active');
    });
}

document.addEventListener('click', function(e) {
    const link = e.target.closest('a');
    if (!link) return;
    
    // Intercept clicks on Category, Pagination and Show More Links
    if (link.closest('.category-list') || link.closest('.pagination-row') || link.closest('.show-more-row')) {
        e.preventDefault();
        applyFilter(link.href);
    }
});

// Sync layout with Browser Back/Forward buttons 
window.addEventListener('popstate', function() {
    applyFilter(window.location.href);
});

// Restore Grid/List view on refresh
document.addEventListener('DOMContentLoaded', applyViewSetting);

async function toggleWishlist(btn, productId) {
    try {
        const fd = new FormData();
        fd.append('product_id', productId);
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
            const svg = btn.querySelector('svg');
            if (data.is_wished) {
                svg.setAttribute('fill', '#FF3333');
                svg.setAttribute('stroke', '#FF3333');
            } else {
                svg.setAttribute('fill', 'none');
                svg.setAttribute('stroke', '#141718');
            }
        } else {
            console.error('Wishlist toggle error:', data.message);
        }
    } catch (e) {
        console.error('Failed to toggle wishlist:', e);
    }
}
</script>
</body>
</html>
