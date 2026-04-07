<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include "../includes/auth.php"; 
require_once "../config/database.php";

$user = $_SESSION['user'] ?? [];
$user_id = $user['id'] ?? null;
$user_name = $user['name'] ?? '';
$user_email = $user['email'] ?? '';
$user_avatar = $user['avatar'] ?? 'default.jpg';
$current_page = 'wishlist.php';

// Fetch wishlist items
$wishlist_items = [];
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT 
            w.id as wishlist_id, 
            p.id as product_id, 
            p.name, 
            p.price, 
            p.thumbnail,
            (SELECT color FROM product_variants WHERE product_id = p.id LIMIT 1) as color
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $wishlist_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<style>
/* Wishlist specific layout CSS */
.page-header { font-size: 54px; font-weight: 600; text-align: center; margin-bottom: 60px; letter-spacing: -1px; font-family: 'Poppins', sans-serif;}
.account-layout { display: flex; gap: 60px; margin-bottom: 60px; }
.account-main-content { flex: 1; }
.section-title { font-size: 20px; font-weight: 600; margin-bottom: 32px; font-family: 'Poppins', sans-serif;}

/* 4-column grid for precise matching */
.wishlist-header {
    display: grid;
    grid-template-columns: 30px 2fr 1fr 140px;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 1px solid #E8ECEF;
    color: var(--color-gray-400);
    font-size: 14px;
    font-weight: 500;
}
.wishlist-header .product-header {
    margin-left: 10px;
}

.wishlist-item {
    display: grid;
    grid-template-columns: 30px 2fr 1fr 140px;
    align-items: center;
    gap: 20px;
    padding: 24px 0;
    border-bottom: 1px solid #E8ECEF;
}

.btn-remove {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #6C7275;
    transition: 0.2s;
}
.btn-remove svg {
    width: 20px;
    height: 20px;
}
.btn-remove:hover {
    color: #141718;
}

.product-col {
    display: flex;
    align-items: center;
    gap: 16px;
}

.product-image {
    width: 72px;
    height: 72px;
    object-fit: cover;
    border-radius: 4px;
    background: #f3f5f7;
    border: 1px solid #F3F5F7;
}

.product-info {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.product-name {
    font-weight: 600;
    font-size: 16px;
    color: #141718;
}

.product-color {
    font-size: 14px;
    color: #6C7275;
}

.price-col {
    font-weight: 400; /* Normal weight as in image */
    font-size: 16px;
    color: #141718;
}

.action-col {
    width: 100%;
}

.btn-add-cart {
    background: #141718;
    color: #FFFFFF;
    border: none;
    width: 100%; 
    padding: 12px 0;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    font-size: 16px;
    transition: 0.2s;
    font-family: 'Inter', sans-serif;
}

.btn-add-cart:hover {
    background: #343839;
}

.empty-wishlist {
    text-align: center;
    padding: 60px 0;
    color: var(--color-gray-400);
    font-size: 16px;
    background: #fcfcfc;
    border-radius: 8px;
    border: 1px dashed #E8ECEF;
}

.mobile-only { display: none; }
.desktop-only { display: block; }

@media (max-width: 768px) {
    .container { margin-top: 30px; }
    .page-header { font-size: 40px; margin-bottom: 30px; }
    .account-layout { flex-direction: column; gap: 10px; align-items: center; }
    .account-main-content { width: 100%; }
    
    .wishlist-header { display: none; }
    .wishlist-item { 
        display: flex; 
        flex-direction: column; 
        align-items: stretch; 
        position: relative;
        padding: 24px 0;
        gap: 0;
    }
    .product-col { 
        align-items: flex-start; 
        gap: 16px;
    }
    .product-image { 
        width: 80px; 
        height: 80px; 
    }
    .btn-remove {
        position: absolute;
        top: 24px;
        left: 0;
        width: 24px;
        height: 24px;
    }
    .product-image {
        margin-left: 32px; 
    }
    .mobile-only { display: block; }
    .desktop-only { display: none; }
    
    .product-price.mobile-only {
        margin-top: 8px;
        font-weight: 400;
        font-size: 16px;
        color: #141718;
    }
    .action-col {
        text-align: center;
        margin-top: 16px;
    }
}
</style>

<div class="container" style="margin-top: 60px;">
    <h1 class="page-header">My Account</h1>

    <div class="account-layout">    
        <!-- Sidebar -->
        <?php include "../includes/account_sidebar.php"; ?>

        <!-- Main Content -->
        <div class="account-main-content">
            <h2 class="section-title">Your Wishlist</h2>

            <?php if(empty($wishlist_items)): ?>
                <div class="empty-wishlist">
                    Your wishlist is currently empty.<br><br>
                    <a href="shop.php" class="btn-add-cart" style="text-decoration:none; display:inline-block; padding:10px 30px; width: auto;">Go to Shop</a>
                </div>
            <?php else: ?>
                <div class="wishlist-header">
                    <div></div> <!-- Empty for X column -->
                    <div class="product-header">Product</div>
                    <div>Price</div>
                    <div>Action</div>
                </div>
                
                <div class="wishlist-list">
                    <?php foreach($wishlist_items as $item): ?>
                        <div class="wishlist-item">
                            <!-- X Button -->
                            <form action="../controllers/ProductController.php?action=remove_wishlist" method="POST" style="margin:0;">
                                <input type="hidden" name="wishlist_id" value="<?= $item['wishlist_id'] ?>">
                                <button type="submit" class="btn-remove">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </form>

                            <!-- Product details (Image + Text) -->
                            <div class="product-col">
                                <img src="<?= htmlspecialchars(strpos($item['thumbnail'], 'assets/product-images/') !== false || strpos($item['thumbnail'], 'http') === 0 ? $item['thumbnail'] : '../assets/product-images/' . $item['thumbnail']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-image">
                                
                                <div class="product-info">
                                    <div class="product-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <?php if($item['color']): ?>
                                        <div class="product-color">Color: <?= htmlspecialchars($item['color']) ?></div>
                                    <?php endif; ?>
                                    <div class="product-price mobile-only">$<?= number_format($item['price'], 2) ?></div>
                                </div>
                            </div>
                            
                            <!-- Price (Middle - Desktop) -->
                            <div class="price-col desktop-only">
                                $<?= number_format($item['price'], 2) ?>
                            </div>
                            
                            <!-- Action / Add to cart (Right) -->
                            <div class="action-col">
                                <form action="../controllers/CartController.php?action=add" method="POST" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-add-cart">Add to cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
