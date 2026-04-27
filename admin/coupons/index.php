<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: ../../user/index.php");
//     exit();
// }

require_once "../../config/database.php";
require_once "../../models/Coupon.php";

$couponModel = new Coupon($conn);
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $couponModel->create($_POST);
    }

    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['status'] ?? 0);
        $couponModel->updateStatus($id, $status);
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $couponModel->delete($id);
    }

    header("Location: index.php");
    exit();
}

$coupons = $couponModel->getAll($search);

$currentPage = 'coupons';
$pageTitle = 'Coupons Management';
$breadcrumb = 'System / Coupons';
$base_path = '../';
?>

<?php include '../layouts/admin_header.php'; ?>

<style>
/* Overall Layout */
.coupons-page {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

.coupons-header {
    margin-bottom: 24px;
}

.coupons-header h1 {
    margin: 0 0 6px;
    font-size: 28px;
    color: #111827;
}

.coupons-header p {
    margin: 0;
    color: #64748b;
    font-size: 15px;
}

/* 2-Column Grid Layout */
.coupon-layout {
    display: grid;
    grid-template-columns: 340px minmax(0, 1fr);
    gap: 20px;
    align-items: start;
    width: 100%;
}

.coupon-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    min-width: 0; 
}

.coupon-card h3 {
    margin: 0 0 18px;
    font-size: 20px;
    color: #111827;
}

/* Form Styling */
.form-group {
    margin-bottom: 14px;
}

.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 6px;
    color: #374151;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    height: 42px;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 13px;
    outline: none;
    box-sizing: border-box;
}

.form-group textarea {
    min-height: 70px;
    height: auto;
    resize: vertical;
}

/* Checkbox Alignment Fix */
.checkbox-row {
    display: flex !important;
    align-items: center !important;
    gap: 10px;
    margin: 15px 0;
}

.checkbox-row input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    margin: 0 !important;
    cursor: pointer;
    accent-color: #111827;
}

.checkbox-row label {
    margin: 0 !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    cursor: pointer;
}

.btn-primary {
    width: 100%;
    border: none;
    background: #111827;
    color: #fff;
    padding: 12px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
}

/* Search Box */
.search-box {
    margin-bottom: 16px;
}

.search-box form {
    display: flex;
    gap: 8px;
}

.search-box input {
    flex: 1;
    height: 40px;
    padding: 0 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
}

.search-box button {
    border: none;
    background: #111827;
    color: white;
    padding: 0 16px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
}

/* Table Styling */
.adm-table-wrap {
    width: 100%;
    overflow-x: auto;
    display: block;
}

.coupon-table {
    width: 100%;
    border-collapse: collapse;
}

.coupon-table th,
.coupon-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #f1f5f9;
    text-align: left;
    font-size: 13px;
    white-space: normal; 
    word-break: break-word;
}

.coupon-table th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
}

.code {
    font-weight: 700;
    color: #111827;
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-block;
    white-space: nowrap;
}

.badge {
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.active { background: #dcfce7; color: #16a34a; }
.inactive { background: #fee2e2; color: #ef4444; }

/* Actions & Icon Buttons (Đã sửa lại để đều tăm tắp) */
.actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* Ép form thành khối flex để các nút bên trong không bị lệch */
.actions form { 
    margin: 0; 
    display: flex; 
}

.btn-small {
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 32px; /* Cố định chiều cao cho tất cả các nút */
    box-sizing: border-box;
}

.btn-toggle { 
    background: #eff6ff; 
    color: #2563eb; 
    width: 75px; /* Ép cứng chiều rộng để nút Enable/Disable bằng nhau */
}

/* Trash Icon Button */
.btn-delete { 
    background: #fff1f2; 
    color: #e11d48; 
    width: 32px; /* Ép cứng chiều rộng bằng chiều cao để tạo hình vuông */
    padding: 0; /* Xóa padding để icon căn giữa tuyệt đối */
}

.btn-delete:hover {
    background: #ffe4e6;
}

@media (max-width: 1100px) {
    .coupon-layout {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="coupons-page">
    <div class="coupons-header">
        <h1>Coupons Management</h1>
        <p>Create, manage, and activate discount coupons for your store.</p>
    </div>

    <div class="coupon-layout">
        <div class="coupon-card">
            <h3>Create Coupon</h3>

            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Coupon Code</label>
                    <input type="text" name="code" placeholder="e.g. WELCOME10" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Brief description..."></textarea>
                </div>

                <div class="form-group">
                    <label>Discount Type</label>
                    <select name="type">
                        <option value="percent">Percent (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Value</label>
                    <input type="number" step="0.01" name="value" required>
                </div>

                <div class="form-group">
                    <label>Minimum Order</label>
                    <input type="number" step="0.01" name="min_order" value="0">
                </div>

                <div class="form-group">
                    <label>Starts At</label>
                    <input type="datetime-local" name="starts_at">
                </div>

                <div class="form-group">
                    <label>Expires At</label>
                    <input type="datetime-local" name="expires_at">
                </div>

                <div class="form-group checkbox-row">
                    <input type="checkbox" name="is_active" value="1" id="active_now" checked>
                    <label for="active_now">Active Now</label>
                </div>

                <button type="submit" class="btn-primary">Create Coupon</button>
            </form>
        </div>

        <div class="coupon-card">
            <h3>Coupon List</h3>

            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search code or description..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="adm-table-wrap">
                <table class="coupon-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Value</th>
                            <th>Min Order</th>
                            <th>Used</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (!empty($coupons)): ?>
                            <?php foreach ($coupons as $coupon): ?>
                                <tr>
                                    <td><span class="code"><?= htmlspecialchars($coupon['code']) ?></span></td>
                                    <td>
                                        <strong>
                                            <?= $coupon['type'] === 'percent'
                                                ? number_format($coupon['value'], 0) . '%'
                                                : '$' . number_format($coupon['value'], 2)
                                            ?>
                                        </strong>
                                    </td>
                                    <td>$<?= number_format($coupon['min_order'], 2) ?></td>
                                    <td><?= (int)$coupon['used_count'] ?> / <?= (int)$coupon['max_uses'] ?></td>
                                    <td>
                                        <small>
                                            <?= !empty($coupon['expires_at'])
                                                ? date('d/m/y', strtotime($coupon['expires_at']))
                                                : 'No expiry'
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $coupon['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $coupon['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="id" value="<?= (int)$coupon['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $coupon['is_active'] ? 0 : 1 ?>">
                                                <button class="btn-small btn-toggle" type="submit">
                                                    <?= $coupon['is_active'] ? 'Disable' : 'Enable' ?>
                                                </button>
                                            </form>

                                            <form method="POST" onsubmit="return confirm('Delete this coupon?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$coupon['id'] ?>">
                                                <button class="btn-small btn-delete" type="submit" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16">
                                                        <path d="M9 3v1H4v2h1v13c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V6h1V4h-5V3H9zM7 6h10v13H7V6zm2 2v9h2V8H9zm4 0v9h2V8h-2z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:30px; color:#94a3b8;">
                                    No coupons found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layouts/admin_footer.php'; ?>