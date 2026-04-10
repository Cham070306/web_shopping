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

$currentPage = 'categories';
$pageTitle   = 'Categories';
$breadcrumb  = 'Catalog / Categories';
$base_path   = '../';

include '../layouts/admin_header.php';
?>

<!-- Page Header -->
<div class="adm-page-header">
    <div>
        <h1>Categories</h1>
        <p>Quản lý danh mục sản phẩm</p>
    </div>
    <a href="create.php" class="btn btn-dark"><i class="fa-solid fa-plus"></i> Add Category</a>
</div>

<!-- Alerts -->
<?php if (!empty($_SESSION['success'])): ?>
    <div class="adm-alert adm-alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
    <div class="adm-alert adm-alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<!-- Table -->
<div class="adm-card">
    <table class="adm-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên danh mục</th>
                <th>Slug</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 48px; color: var(--gray-400);">
                        Chưa có danh mục nào. <a href="create.php" style="color: var(--black); font-weight: 600;">Thêm ngay →</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="color: var(--gray-400); font-size: 13px;">#<?= $cat['id'] ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($cat['name']) ?></td>
                    <td style="color: var(--gray-400); font-size: 13px;"><?= htmlspecialchars($cat['slug']) ?></td>
                    <td><?= $cat['sort_order'] ?></td>
                    <td>
                        <?php if ($cat['is_active']): ?>
                            <span class="badge badge-green">Hiển thị</span>
                        <?php else: ?>
                            <span class="badge badge-red">Đã ẩn</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $cat['id'] ?>" class="btn btn-outline btn-sm">
                            <i class="fa-solid fa-pen"></i> Edit
                        </a>
                        <form action="../../controllers/CategoryController.php" method="POST" style="display:inline-block; margin-left: 6px;"
                              onsubmit="return confirm('Bạn có chắc muốn xoá danh mục này không?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../layouts/admin_footer.php'; ?>
