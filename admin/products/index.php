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

<style>
.products-page {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

.products-page .adm-page-header {
    margin-bottom: 20px;
}

.products-page .adm-page-header h1 {
    font-size: 26px;
}

.products-page .adm-page-header p {
    font-size: 14px;
}

.products-page .btn-dark {
    padding: 10px 18px;
    white-space: nowrap;
}

.products-card {
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    border-radius: 14px;
}

.products-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
}

.products-table {
    width: 100%;
    min-width: 0 !important;
    table-layout: fixed;
    border-collapse: collapse;
}

.products-table th,
.products-table td {
    padding: 13px 12px;
    vertical-align: middle;
    font-size: 13px;
    word-break: break-word;
}

.products-table th {
    font-size: 11px;
    letter-spacing: .5px;
    white-space: nowrap;
}

.products-table th:nth-child(1),
.products-table td:nth-child(1) {
    width: 9%;
}

.products-table th:nth-child(2),
.products-table td:nth-child(2) {
    width: 28%;
}

.products-table th:nth-child(3),
.products-table td:nth-child(3) {
    width: 13%;
}

.products-table th:nth-child(4),
.products-table td:nth-child(4) {
    width: 13%;
}

.products-table th:nth-child(5),
.products-table td:nth-child(5) {
    width: 12%;
}

.products-table th:nth-child(6),
.products-table td:nth-child(6) {
    width: 11%;
}

.products-table th:nth-child(7),
.products-table td:nth-child(7) {
    width: 14%;
}

.product-thumb {
    width: 46px;
    height: 46px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid var(--gray-300);
}

.product-title {
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-sku {
    font-size: 12px;
    color: var(--gray-400);
    margin-top: 4px;
    white-space: nowrap;
}

.product-category {
    color: var(--gray-400);
    font-size: 13px;
    line-height: 1.35;
}

.product-price {
    white-space: nowrap;
}

.product-price-sale {
    font-weight: 600;
    color: var(--red);
}

.product-price-old {
    text-decoration: line-through;
    font-size: 12px;
    color: var(--gray-400);
}

.product-stock {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
    white-space: nowrap;
}

.product-stock-main {
    font-weight: 600;
    font-size: 13px;
}

.product-stock-sold {
    font-size: 12px;
    color: var(--gray-400);
}

.product-actions {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: nowrap;
}

.product-actions .btn {
    width: 34px;
    height: 34px;
    padding: 0;
    justify-content: center;
}

.product-actions form {
    margin: 0;
    display: inline-flex;
}

@media (max-width: 1200px) {
    .products-table th,
    .products-table td {
        padding: 11px 8px;
        font-size: 12px;
    }

    .product-thumb {
        width: 42px;
        height: 42px;
    }

    .product-actions .btn {
        width: 32px;
        height: 32px;
    }
}

@media (max-width: 900px) {
    .products-table {
        min-width: 850px !important;
    }
}
</style>

<div class="products-page">

    <div class="adm-page-header">
        <div>
            <h1>Products</h1>
            <p>Product catalog &amp; inventory</p>
        </div>
        <a href="create.php" class="btn btn-dark">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="adm-alert adm-alert-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="adm-alert adm-alert-error">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="adm-card products-card">
        <div class="products-table-wrap">
            <table class="adm-table products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Name</th>
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
                                No products yet.
                                <a href="create.php" style="color: var(--black); font-weight: 600;">Add one →</a>
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
                                        if (!$img) {
                                            $img = '../../assets/images/sofa.jpg';
                                        }
                                    ?>

                                    <img src="<?= $img ?>" alt="thumb" class="product-thumb">
                                </td>

                                <td>
                                    <div class="product-title" title="<?= htmlspecialchars($p['name']) ?>">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </div>
                                    <div class="product-sku">
                                        SKU: <?= htmlspecialchars($p['sku'] ?? 'N/A') ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="product-category">
                                        <?= htmlspecialchars($p['category_name'] ?? 'None') ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="product-price">
                                        <?php if (!empty($p['sale_price'])): ?>
                                            <div class="product-price-sale">
                                                <?= number_format($p['sale_price'], 0, ',', '.') ?>đ
                                            </div>
                                            <div class="product-price-old">
                                                <?= number_format($p['price'], 0, ',', '.') ?>đ
                                            </div>
                                        <?php else: ?>
                                            <div style="font-weight: 600;">
                                                <?= number_format($p['price'], 0, ',', '.') ?>đ
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <td>
                                    <div class="product-stock">
                                        <span class="product-stock-main" style="color: <?= $p['stock'] < 10 ? 'var(--red)' : 'var(--black)' ?>">
                                            <?= $p['stock'] ?>
                                        </span>
                                        <span class="product-stock-sold">
                                            (Sold: <?= $p['sold'] ?>)
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <?php if ($p['is_active']): ?>
                                        <span class="badge badge-green">Visible</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray" style="text-decoration: line-through;">Hidden</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="product-actions">
                                        <a href="detail.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="View Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form action="../../controllers/ProductController.php" method="POST"
                                              class="delete-form"
                                              data-msg="Are you sure you want to delete this product? This action cannot be undone.">
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
    </div>

</div>

<?php include '../layouts/admin_footer.php'; ?>