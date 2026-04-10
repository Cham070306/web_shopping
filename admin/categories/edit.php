<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/database.php";
require_once "../../models/Category.php";

$id = $_GET['id'] ?? 0;
$categoryModel = new Category($conn);
$category = $categoryModel->getById($id);

if (!$category) {
    $_SESSION['error'] = 'Không tìm thấy danh mục.';
    header("Location: index.php");
    exit;
}

$currentPage = 'categories';
$pageTitle   = 'Edit Category';
$breadcrumb  = 'Categories / Edit';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<div class="adm-page-header">
    <div>
        <h1>Edit Category</h1>
        <p>Chỉnh sửa: <strong><?= htmlspecialchars($category['name']) ?></strong></p>
    </div>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="adm-card" style="max-width: 640px; padding: 32px;">
    <form action="../../controllers/CategoryController.php" method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" value="<?= $category['id'] ?>">

        <div class="adm-form-group">
            <label>Tên danh mục <span style="color: var(--red)">*</span></label>
            <input type="text" name="name" class="adm-input" required
                   value="<?= htmlspecialchars($category['name']) ?>">
        </div>

        <div class="adm-form-group">
            <label>Slug</label>
            <input type="text" name="slug" class="adm-input"
                   value="<?= htmlspecialchars($category['slug']) ?>">
            <p class="adm-form-hint">Dùng cho URL, chỉ bao gồm chữ thường, số và dấu gạch ngang.</p>
        </div>

        <div class="adm-form-group">
            <label>Thứ tự hiển thị</label>
            <input type="number" name="sort_order" class="adm-input"
                   value="<?= $category['sort_order'] ?>" style="max-width: 160px;">
        </div>

        <div class="adm-form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_active" id="is_active"
                   <?= $category['is_active'] ? 'checked' : '' ?>
                   style="width: 18px; height: 18px; accent-color: var(--black); cursor: pointer;">
            <label for="is_active" style="margin: 0; cursor: pointer;">Cho phép hiển thị trên trang cửa hàng</label>
        </div>

        <div style="margin-top: 28px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-dark"><i class="fa-solid fa-check"></i> Lưu Thay Đổi</button>
            <a href="index.php" class="btn btn-outline">Huỷ</a>
        </div>
    </form>
</div>

<?php include '../layouts/admin_footer.php'; ?>
