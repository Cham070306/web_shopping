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
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Thumbnail Image *</h3>
                
                <div class="form-group">
                    <div id="image-preview" style="display: none; margin-bottom: 16px;">
                        <img id="preview-img" src="" style="width: 100%; aspect-ratio: 1; object-fit: cover; border-radius: 8px; border: 1px solid var(--gray-300);">
                        <p style="text-align:center; font-size: 12px; color: var(--gray-400); margin-top: 8px;">Thumbnail preview</p>
                    </div>
                    <div style="border: 2px dashed var(--gray-300); border-radius: 8px; padding: 24px; text-align: center; background: var(--gray-100);">
                        <i class="fa-regular fa-image" style="font-size: 24px; color: var(--gray-400); margin-bottom: 12px;"></i>
                        <div style="font-size: 12px; color: var(--gray-400); margin-bottom: 12px;">Click to select thumbnail</div>
                        <input type="file" name="thumbnail" id="thumbnail-input" accept="image/*" class="form-control" style="background: white; font-size: 13px;" required>
                    </div>
                </div>
            </div>

            <div class="adm-card" style="padding: 24px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 20px;">Gallery Images</h3>
                
                <div class="form-group">
                    <div id="gallery-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
                        <!-- Gallery previews will be appended here -->
                    </div>
                    <div style="border: 2px dashed var(--gray-300); border-radius: 8px; padding: 24px; text-align: center; background: var(--gray-100);">
                        <i class="fa-solid fa-images" style="font-size: 24px; color: var(--gray-400); margin-bottom: 12px;"></i>
                        <div style="font-size: 12px; color: var(--gray-400); margin-bottom: 12px;">Click to select multiple gallery images</div>
                        <input type="file" name="images[]" id="gallery-input" accept="image/*" multiple class="form-control" style="background: white; font-size: 13px;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-dark" style="width: 100%; padding: 14px;"><i class="fa-solid fa-check"></i> Save Product</button>
        </div>

    </div>
</form>

<script>
    // Thumbnail Preview
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

    // Gallery Preview & Remove
    const galleryInput = document.getElementById('gallery-input');
    const galleryPreview = document.getElementById('gallery-preview');
    let dataTransfer = new DataTransfer();

    galleryInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        files.forEach((file) => {
            dataTransfer.items.add(file);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.position = 'relative';
                div.style.width = '80px';
                div.style.height = '80px';
                div.style.borderRadius = '6px';
                div.style.overflow = 'hidden';
                div.style.border = '1px solid var(--gray-300)';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.width = '100%';
                img.style.height = '100%';
                img.style.objectFit = 'cover';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
                removeBtn.style.position = 'absolute';
                removeBtn.style.top = '2px';
                removeBtn.style.right = '2px';
                removeBtn.style.background = 'rgba(0,0,0,0.6)';
                removeBtn.style.color = 'white';
                removeBtn.style.border = 'none';
                removeBtn.style.borderRadius = '50%';
                removeBtn.style.width = '20px';
                removeBtn.style.height = '20px';
                removeBtn.style.fontSize = '12px';
                removeBtn.style.cursor = 'pointer';
                removeBtn.style.display = 'flex';
                removeBtn.style.alignItems = 'center';
                removeBtn.style.justifyContent = 'center';

                removeBtn.onclick = function() {
                    const fileName = file.name;
                    for(let i=0; i<dataTransfer.items.length; i++) {
                        if(dataTransfer.items[i].getAsFile().name === fileName) {
                            dataTransfer.items.remove(i);
                            break;
                        }
                    }
                    galleryInput.files = dataTransfer.files;
                    div.remove();
                };
                
                div.appendChild(img);
                div.appendChild(removeBtn);
                galleryPreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
        
        // Update input files
        galleryInput.files = dataTransfer.files;
    });
</script>

<?php include '../layouts/admin_footer.php'; ?>
