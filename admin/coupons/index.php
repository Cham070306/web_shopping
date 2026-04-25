<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// comment nếu auth chưa fix
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
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Coupons Management</title>
    <style>
        .admin-page {
        margin-left: 260px;
        min-height: 100vh;
        padding: 28px;
        background: #f4f6f8;
    }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f6f8fb;
            color: #222;
        }

        .page {
            max-width: 1250px;
            margin: auto;
            padding: 24px;
        }

        .topbar {
            margin-bottom: 20px;
        }

        .topbar h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .topbar p {
            margin: 0;
            color: #666;
        }

        .grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 22px;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        .card h3 {
            margin-top: 0;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 7px;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #ddd;
            border-radius: 9px;
            box-sizing: border-box;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .checkbox-row {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .checkbox-row input {
            width: auto;
        }

        .btn-primary {
            width: 100%;
            border: none;
            background: #111;
            color: #fff;
            padding: 12px;
            border-radius: 9px;
            font-weight: 700;
            cursor: pointer;
        }

        .search-box {
            margin-bottom: 16px;
        }

        .search-box form {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
        }

        .search-box button {
            border: none;
            background: #111;
            color: white;
            padding: 0 18px;
            border-radius: 9px;
            cursor: pointer;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #fafafa;
            color: #666;
            font-size: 13px;
        }

        .code {
            font-weight: 700;
            color: #111;
            background: #f3f4f6;
            padding: 5px 8px;
            border-radius: 6px;
            display: inline-block;
        }

        .badge {
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .active {
            background: #e9f9ef;
            color: #1f7a3f;
        }

        .inactive {
            background: #ffeaea;
            color: #d93025;
        }

        .actions {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
        }

        .btn-small {
            border: none;
            padding: 7px 10px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .btn-toggle {
            background: #edf2ff;
            color: #2f5bea;
        }

        .btn-delete {
            background: #ffeaea;
            color: #d93025;
        }

        @media (max-width: 1000px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
<div class="page">
    <div class="topbar">
        <h1>Coupons Management</h1>
        <p>Create, manage and activate discount coupons.</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Create Coupon</h3>

            <form method="POST">
                <input type="hidden" name="action" value="create">

                <div class="form-group">
                    <label>Coupon Code</label>
                    <input type="text" name="code" placeholder="WELCOME10" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Short description"></textarea>
                </div>

                <div class="form-group">
                    <label>Discount Type</label>
                    <select name="type">
                        <option value="percent">Percent</option>
                        <option value="fixed">Fixed Amount</option>
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
                    <label>Max Discount</label>
                    <input type="number" step="0.01" name="max_discount" placeholder="Optional">
                </div>

                <div class="form-group">
                    <label>Max Uses</label>
                    <input type="number" name="max_uses" value="0">
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
                    <input type="checkbox" name="is_active" value="1" checked>
                    <label style="margin:0;">Active</label>
                </div>

                <button type="submit" class="btn-primary">Create Coupon</button>
            </form>
        </div>

        <div class="card">
            <h3>Coupon List</h3>

            <div class="search-box">
                <form method="GET">
                    <input type="text" name="search" placeholder="Search code or description..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Type</th>
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
                                    <td><?= htmlspecialchars($coupon['type']) ?></td>
                                    <td>
                                        <?= $coupon['type'] === 'percent'
                                            ? number_format($coupon['value'], 0) . '%'
                                            : '$' . number_format($coupon['value'], 2)
                                        ?>
                                    </td>
                                    <td>$<?= number_format($coupon['min_order'], 2) ?></td>
                                    <td><?= (int)$coupon['used_count'] ?> / <?= (int)$coupon['max_uses'] ?></td>
                                    <td><?= !empty($coupon['expires_at']) ? date('d/m/Y', strtotime($coupon['expires_at'])) : '-' ?></td>
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
                                                <button class="btn-small btn-delete" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No coupons found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include '../layouts/admin_footer.php'; ?>