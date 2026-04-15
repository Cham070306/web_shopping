<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../models/Product.php";
require_once "../../models/Category.php";

$id = $_GET['id'] ?? 0;
$productModel = new Product($conn);
$product = $productModel->getById($id);

if (!$product) {
    $_SESSION['error'] = 'Product not found.';
    header('Location: index.php');
    exit;
}

$categoryModel = new Category($conn);
$categories = $categoryModel->getAll();

$currentPage = 'products';
$pageTitle   = 'Edit Product';
$breadcrumb  = 'Catalog / Products / Edit';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<div class="adm-page-header">
    <div>
        <h1>Edit Product</h1>
        <p style="max-width: 600px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Updating product: <strong><?= htmlspecialchars($product['name']) ?></strong></p>
    </div>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<form action="../../controllers/ProductController.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_product"> <!-- We will need to implement update_product later -->
    <input type="hidden" name="id" value="<?= $product['id'] ?>">
    <input type="hidden" name="current_thumbnail" value="<?= htmlspecialchars($product['thumbnail']) ?>">
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Left Column: Main Info -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Basic Information</h3>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name']) ?>">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Pricing &amp; Inventory</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label">Original Price (VND) *</label>
                        <input type="number" name="price" class="form-control" required min="0" value="<?= htmlspecialchars($product['price']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sale Price (VND)</label>
                        <input type="number" name="sale_price" class="form-control" min="0" value="<?= htmlspecialchars($product['sale_price'] ?? '') ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">SKU Code</label>
                        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control" required min="0" value="<?= htmlspecialchars($product['stock']) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Meta Info -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Organization</h3>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Category *</label>
                    <select name="category_id" class="form-control" required>
                        <option value="">-- Select category --</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($product['category_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?> style="width:16px; height:16px; accent-color: var(--black);">
                        <span style="font-size: 14px; font-weight: 500;">Show on website</span>
                    </label>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_featured" <?= $product['is_featured'] ? 'checked' : '' ?> style="width:16px; height:16px; accent-color: var(--black);">
                        <span style="font-size: 14px; font-weight: 500;">Featured product</span>
                    </label>
                </div>
            </div>

            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Thumbnail Image</h3>
                
                <?php if($product['thumbnail']): ?>
                    <?php 
                        $img = htmlspecialchars($product['thumbnail']);
                        if (strpos($img, 'http') !== 0 && $img) {
                            $img = '../../assets/product-images/' . $img;
                        }
                    ?>
                    <div id="image-preview" style="margin-bottom: 16px;">
                        <img id="preview-img" src="<?= $img ?>" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);">
                        <p id="preview-text" style="text-align:center; font-size: 12px; color: var(--gray-400); margin-top: 8px;">Current image</p>
                    </div>
                <?php else: ?>
                    <div id="image-preview" style="display: none; margin-bottom: 16px;">
                        <img id="preview-img" src="" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);">
                        <p id="preview-text" style="text-align:center; font-size: 12px; color: var(--gray-400); margin-top: 8px;">Image preview</p>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <div style="border: 2px dashed var(--gray-300); border-radius: 8px; padding: 24px; text-align: center; background: var(--gray-100);">
                        <i class="fa-regular fa-image" style="font-size: 24px; color: var(--gray-400); margin-bottom: 12px;"></i>
                        <div style="font-size: 12px; color: var(--gray-400); margin-bottom: 12px;">Select a new image to replace (optional)</div>
                        <input type="file" name="thumbnail" id="thumbnail-input" accept="image/*" class="form-control" style="background: white; font-size: 13px;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark" style="width: 100%; padding: 14px;"><i class="fa-solid fa-floppy-disk"></i> Update Product</button>
        </div>

    </div>
</form>

<script>
    document.getElementById('thumbnail-input').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').style.display = 'block';
                var pText = document.getElementById('preview-text');
                if(pText) pText.innerText = 'New image ready to replace';
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>

<?php include '../layouts/admin_footer.php'; ?>
