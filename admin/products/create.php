<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../models/Category.php";

$categoryModel = new Category($conn);
$categories = $categoryModel->getAll();

$currentPage = 'products';
$pageTitle   = 'Create Product';
$breadcrumb  = 'Catalog / Products / Add New';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<div class="adm-page-header">
    <div>
        <h1>Add New Product</h1>
        <p>Add a new product to the store</p>
    </div>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<form action="../../controllers/ProductController.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_product">
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Left Column: Main Info -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Basic Information</h3>
                
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" required placeholder="E.g: Sofa Loveseat">
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Product Description</label>
                    <textarea name="description" class="form-control" rows="5" placeholder="Enter detailed product description..."></textarea>
                </div>
            </div>

            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Pricing &amp; Inventory</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label">Original Price (VND) *</label>
                        <input type="number" name="price" class="form-control" required min="0" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sale Price (VND)</label>
                        <input type="number" name="sale_price" class="form-control" min="0" placeholder="0 (Leave blank if no discount)">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">SKU Code</label>
                        <input type="text" name="sku" class="form-control" placeholder="E.g: SOFA-001">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control" required min="0" value="10">
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
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_active" checked style="width:16px; height:16px; accent-color: var(--black);">
                        <span style="font-size: 14px; font-weight: 500;">Show on website</span>
                    </label>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="is_featured" style="width:16px; height:16px; accent-color: var(--black);">
                        <span style="font-size: 14px; font-weight: 500;">Featured product</span>
                    </label>
                </div>
            </div>

            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Thumbnail Image</h3>
                
                <div class="form-group">
                    <div id="image-preview" style="display: none; margin-bottom: 16px;">
                        <img id="preview-img" src="" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);">
                        <p style="text-align:center; font-size: 12px; color: var(--gray-400); margin-top: 8px;">Image preview</p>
                    </div>
                    <div style="border: 2px dashed var(--gray-300); border-radius: 8px; padding: 24px; text-align: center; background: var(--gray-100);">
                        <i class="fa-regular fa-image" style="font-size: 24px; color: var(--gray-400); margin-bottom: 12px;"></i>
                        <div style="font-size: 12px; color: var(--gray-400); margin-bottom: 12px;">Click to select an image to upload</div>
                        <input type="file" name="thumbnail" id="thumbnail-input" accept="image/*" class="form-control" style="background: white; font-size: 13px;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark" style="width: 100%; padding: 14px;"><i class="fa-solid fa-check"></i> Save Product</button>
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
            }
            reader.readAsDataURL(this.files[0]);
        } else {
            document.getElementById('image-preview').style.display = 'none';
        }
    });
</script>

<?php include '../layouts/admin_footer.php'; ?>
