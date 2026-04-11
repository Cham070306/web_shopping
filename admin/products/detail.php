<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../models/Product.php";

$id = $_GET['id'] ?? 0;
$productModel = new Product($conn);
$product = $productModel->getById($id);

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    header('Location: index.php');
    exit;
}

$currentPage = 'products';
$pageTitle   = 'Product Details';
$breadcrumb  = 'Catalog / Products / View Detail';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<div class="adm-page-header">
    <div>
        <h1>Chi tiết sản phẩm</h1>
        <p>Xem thông tin chi tiết</p>
    </div>
    <div style="display:flex; gap: 8px;">
        <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Danh sách</a>
        <a href="edit.php?id=<?= $product['id'] ?>" class="btn btn-dark"><i class="fa-solid fa-pen"></i> Chỉnh sửa</a>
    </div>
</div>

<div class="adm-card" style="padding: 32px;">
    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 48px; align-items: start;">
        
        <!-- Left Image -->
        <div>
            <?php 
                $img = htmlspecialchars($product['thumbnail']);
                if (strpos($img, 'http') !== 0 && $img) {
                    $img = '../../assets/product-images/' . $img;
                }
                if (!$img) $img = '../../assets/images/sofa.jpg';
            ?>
            <img src="<?= $img ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 12px; border: 1px solid var(--gray-300); background: var(--gray-100);">
        </div>

        <!-- Right Info -->
        <div>
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <?php if ($product['is_active']): ?>
                    <span class="badge badge-green">Đang hiển thị</span>
                <?php else: ?>
                    <span class="badge badge-gray">Đã ẩn</span>
                <?php endif; ?>
                
                <?php if ($product['is_featured']): ?>
                    <span class="badge" style="background:var(--black); color:white;">Nổi bật</span>
                <?php endif; ?>
                
                <span class="badge badge-blue">Kho: <?= $product['stock'] ?></span>
                <span class="badge badge-gray">Đã bán: <?= $product['sold'] ?></span>
            </div>

            <h2 style="font-size: 28px; font-weight: 600; margin-bottom: 8px; font-family:'Poppins', sans-serif;"><?= htmlspecialchars($product['name']) ?></h2>
            <p style="color: var(--gray-400); font-size: 14px; margin-bottom: 24px;">SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></p>

            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid var(--gray-300);">
                <?php if(!empty($product['sale_price'])): ?>
                    <span style="font-size: 24px; font-weight: 600; font-family:'Poppins', sans-serif; color: var(--red);"><?= number_format($product['sale_price'], 0, ',', '.') ?>đ</span>
                    <span style="font-size: 16px; font-weight: 500; text-decoration: line-through; color: var(--gray-400);"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                <?php else: ?>
                    <span style="font-size: 24px; font-weight: 600; font-family:'Poppins', sans-serif;"><?= number_format($product['price'], 0, ',', '.') ?>đ</span>
                <?php endif; ?>
            </div>

            <h4 style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">Mô tả chi tiết</h4>
            <div style="line-height: 1.6; color: var(--gray-400); white-space: pre-line;">
                <?= htmlspecialchars($product['description'] ?? 'Chưa có cập nhật mô tả.') ?>
            </div>
            
        </div>
    </div>
</div>

<?php include '../layouts/admin_footer.php'; ?>
