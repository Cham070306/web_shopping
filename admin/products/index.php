<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../models/Product.php";

$productModel = new Product($conn);
$products = $productModel->getAll();

$currentPage = 'products';
$pageTitle   = 'Products';
$breadcrumb  = 'Catalog / Products';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<!-- Page Header -->
<div class="adm-page-header">
    <div>
        <h1>Products</h1>
        <p>Product catalog &amp; inventory</p>
    </div>
    <a href="create.php" class="btn btn-dark"><i class="fa-solid fa-plus"></i> Add Product</a>
</div>

<!-- Alerts -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="adm-alert adm-alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="adm-alert adm-alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Products Table -->
<div class="adm-card">
    <table class="adm-table">
        <thead>
            <tr>
                <th style="width: 80px;">Image</th>
                <th style="max-width: 260px; width: 260px;">Product Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding: 48px; color: var(--gray-400);">
                        No products yet. <a href="create.php" style="color: var(--black); font-weight: 600;">Add one →</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td>
                        <?php 
                            $img = htmlspecialchars($p['thumbnail']);
                            if (strpos($img, 'http') !== 0 && $img) {
                                $img = '../../assets/product-images/' . $img;
                            }
                            if (!$img) $img = '../../assets/images/sofa.jpg';
                        ?>
                        <img src="<?= $img ?>" alt="thumb" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);">
                    </td>
                    <td style="max-width: 260px; width: 260px;">
                        <div style="font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px;" title="<?= htmlspecialchars($p['name']) ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </div>
                        <div style="font-size: 12px; color: var(--gray-400); margin-top: 4px;">SKU: <?= htmlspecialchars($p['sku'] ?? 'N/A') ?></div>
                    </td>
                    <td style="color: var(--gray-400); font-size: 13px;"><?= htmlspecialchars($p['category_name'] ?? 'None') ?></td>
                    <td style="white-space: nowrap;">
                        <?php if(!empty($p['sale_price'])): ?>
                            <div style="font-weight: 600; color: var(--red);"><?= number_format($p['sale_price'], 0, ',', '.') ?>đ</div>
                            <div style="text-decoration: line-through; font-size: 12px; color: var(--gray-400);"><?= number_format($p['price'], 0, ',', '.') ?>đ</div>
                        <?php else: ?>
                            <div style="font-weight: 600;"><?= number_format($p['price'], 0, ',', '.') ?>đ</div>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-weight: 600; font-size: 14px; color: <?= $p['stock'] < 10 ? 'var(--red)' : 'var(--black)' ?>"><?= $p['stock'] ?></span>
                            <span style="font-size: 12px; color: var(--gray-400);">(Sold: <?= $p['sold'] ?>)</span>
                        </div>
                    </td>
                    <td style="white-space: nowrap;">
                        <?php if ($p['is_active']): ?>
                            <span class="badge badge-green">Visible</span>
                        <?php else: ?>
                            <span class="badge badge-gray" style="text-decoration: line-through;">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="display: flex; gap: 8px;">
                            <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="View Detail"><i class="fa-solid fa-eye"></i></a>
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            <form action="../../controllers/ProductController.php" method="POST" style="display:inline-block;"
                                  class="delete-form" data-msg="Are you sure you want to delete this product? This action cannot be undone.">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../layouts/admin_footer.php'; ?>
