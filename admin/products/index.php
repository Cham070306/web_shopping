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
$pageTitle = 'Products';
$breadcrumb = 'Catalog / Products';
$base_path = '../';

include '../layouts/admin_header.php';
?>

<style>
* {
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
}

/* container */
.adm-page-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    overflow: hidden;
}

/* header */
.adm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}

.adm-page-title h1 {
    margin: 0;
    font-size: 26px;
}

.adm-page-title p {
    margin-top: 5px;
    color: #6C7275;
    font-size: 14px;
}

.add-product-btn {
    background: #141718;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    white-space: nowrap;
}

/* card */
.adm-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #E8ECEF;
    width: 100%;
    overflow: hidden;
}

/* table wrapper */
.adm-table-wrap {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
}

/* table */
.adm-table {
    width: 100%;
    border-collapse: collapse;
}

/* table cell */
.adm-table th,
.adm-table td {
    padding: 12px 10px;
    text-align: left;
    border-bottom: 1px solid #E8ECEF;
    font-size: 13px;
    vertical-align: middle;
}

.adm-table th {
    background: #F3F5F7;
    font-size: 11px;
    text-transform: uppercase;
    color: #6C7275;
    font-weight: 700;
}

/* image */
.prod-img {
    width: 48px;
    height: 48px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #eee;
}

/* product name */
.name-wrapper {
    max-width: 220px;
}

.product-name {
    font-weight: 700;
    white-space: normal;
    word-break: break-word;
    line-height: 1.4;
}

.product-sku {
    font-size: 12px;
    color: #6C7275;
}

/* status */
.badge-status {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
}

.status-visible {
    background: #E8F9EE;
    color: #38CB89;
}

.status-hidden {
    background: #F3F5F7;
    color: #6C7275;
}

/* actions */
.action-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border: 1px solid #E8ECEF;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    text-decoration: none;
    background: white;
    color: #141718;
    flex-shrink: 0;
}

.btn-icon:hover {
    background: #F3F5F7;
}

/* ======================
   RESPONSIVE
====================== */

/* tablet */
@media (max-width: 992px) {
    .adm-page-container {
        padding: 14px;
    }

    .adm-table th,
    .adm-table td {
        padding: 10px 8px;
        font-size: 12px;
    }

    .prod-img {
        width: 42px;
        height: 42px;
    }

    .product-name {
        font-size: 13px;
    }

    .btn-icon {
        width: 28px;
        height: 28px;
        font-size: 12px;
    }
}

/* mobile */
@media (max-width: 768px) {
    .adm-page-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .add-product-btn {
        width: 100%;
        text-align: center;
    }

    .adm-page-title h1 {
        font-size: 22px;
    }

    .adm-table {
        transform: scale(0.95);
        transform-origin: top left;
        width: 105%;
    }

    .prod-img {
        width: 38px;
        height: 38px;
    }

    .product-name {
        font-size: 12px;
    }

    .product-sku {
        font-size: 10px;
    }
}

/* small mobile */
@media (max-width: 576px) {
    .adm-page-container {
        padding: 10px;
    }

    .adm-page-title h1 {
        font-size: 18px;
    }

    .adm-page-title p {
        font-size: 12px;
    }

    .adm-table {
        transform: scale(0.88);
        transform-origin: top left;
        width: 114%;
    }

    .adm-table th,
    .adm-table td {
        padding: 8px 6px;
        font-size: 11px;
    }

    .prod-img {
        width: 34px;
        height: 34px;
    }

    .btn-icon {
        width: 24px;
        height: 24px;
    }
}

/* extra small device */
@media (max-width: 420px) {
    .adm-table {
        transform: scale(0.80);
        transform-origin: top left;
        width: 125%;
    }

    .adm-page-container {
        padding: 8px;
    }
}
</style>

<div class="adm-page-container">
    <div class="adm-page-header">
        <div class="adm-page-title">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <p><?= htmlspecialchars($breadcrumb) ?></p>
        </div>

        <a href="create.php" class="add-product-btn">
            + Add Product
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="adm-alert-success">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="adm-card">
        <div class="adm-table-wrap">
            <table class="adm-table">
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
                            <td colspan="7" class="empty-row">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php
                                    $img = $p['thumbnail'] ?? '';

                                    if (!empty($img) && strpos($img, 'http') !== 0) {
                                        $img = '../../assets/product-images/' . $img;
                                    }

                                    if (empty($img)) {
                                        $img = '../../assets/images/sofa.jpg';
                                    }

                                    $img = htmlspecialchars($img);
                                    ?>

                                    <img src="<?= $img ?>" class="prod-img" alt="thumb">
                                </td>

                                <td>
                                    <div class="name-wrapper">
                                        <div class="product-name" title="<?= htmlspecialchars($p['name'] ?? '') ?>">
                                            <?= htmlspecialchars($p['name'] ?? 'No name') ?>
                                        </div>

                                        <div class="product-sku">
                                            SKU: <?= htmlspecialchars($p['sku'] ?? 'N/A') ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($p['category_name'] ?? 'None') ?>
                                </td>

                                <td>
                                    <?php if (!empty($p['sale_price'])): ?>
                                        <div class="price-sale">
                                            <?= number_format($p['sale_price'], 0, ',', '.') ?>đ
                                        </div>
                                        <div class="price-old">
                                            <?= number_format($p['price'], 0, ',', '.') ?>đ
                                        </div>
                                    <?php else: ?>
                                        <div class="price-normal">
                                            <?= number_format($p['price'], 0, ',', '.') ?>đ
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="stock-box">
                                        <span style="font-weight: 700; color: <?= ($p['stock'] ?? 0) < 10 ? '#FF5630' : '#141718' ?>">
                                            <?= (int)($p['stock'] ?? 0) ?>
                                        </span>
                                        <span class="stock-sold">
                                            (Sold: <?= (int)($p['sold'] ?? 0) ?>)
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($p['is_active'])): ?>
                                        <span class="badge-status status-visible">Visible</span>
                                    <?php else: ?>
                                        <span class="badge-status status-hidden">Hidden</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <div class="action-btns">
                                        <a href="detail.php?id=<?= (int)$p['id'] ?>" class="btn-icon" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="edit.php?id=<?= (int)$p['id'] ?>" class="btn-icon" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>

                                        <form action="../../controllers/ProductController.php" method="POST" style="margin:0;" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">

                                            <button type="submit" class="btn-icon btn-delete" title="Delete">
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