<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

$currentPage = 'categories';
$pageTitle   = 'Add Category';
$breadcrumb  = 'Categories / Add New';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<div class="adm-page-header">
    <div>
        <h1>Add Category</h1>
        <p>Thêm danh mục sản phẩm mới</p>
    </div>
    <a href="index.php" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
</div>

<div class="adm-card" style="max-width: 640px; padding: 32px;">
    <form action="../../controllers/CategoryController.php" method="POST">
        <input type="hidden" name="action" value="create">

        <div class="adm-form-group">
            <label>Tên danh mục <span style="color: var(--red)">*</span></label>
            <input type="text" name="name" class="adm-input" required placeholder="VD: Phòng khách, Phòng ngủ...">
        </div>

        <div class="adm-form-group">
            <label>Slug <small>(Tự động tạo nếu để trống)</small></label>
            <input type="text" name="slug" class="adm-input" placeholder="VD: phong-khach">
            <p class="adm-form-hint">Dùng cho URL, chỉ bao gồm chữ thường, số và dấu gạch ngang.</p>
        </div>

        <div class="adm-form-group">
            <label>Thứ tự hiển thị</label>
            <input type="number" name="sort_order" class="adm-input" value="0" style="max-width: 160px;">
            <p class="adm-form-hint">Số nhỏ hơn sẽ được hiển thị trước.</p>
        </div>

        <div class="adm-form-group" style="display: flex; align-items: center; gap: 10px;">
            <input type="checkbox" name="is_active" id="is_active" checked
                   style="width: 18px; height: 18px; accent-color: var(--black); cursor: pointer;">
            <label for="is_active" style="margin: 0; cursor: pointer;">Cho phép hiển thị trên trang cửa hàng</label>
        </div>

        <div style="margin-top: 28px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-dark"><i class="fa-solid fa-check"></i> Thêm Danh Mục</button>
            <a href="index.php" class="btn btn-outline">Huỷ</a>
        </div>
    </form>
</div>

<?php include '../layouts/admin_footer.php'; ?>
