<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tạm comment nếu auth admin chưa fix
// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: ../../user/index.php");
//     exit();
// }

require_once "../../config/database.php";

$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.phone,
        u.avatar,
        u.is_active,
        COUNT(DISTINCT o.id) as total_orders,
        COALESCE(SUM(o.total),0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = 'user'
";

$params = [];
$types = '';

if ($search !== '') {
    $sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $types .= 'ss';
}

$sql .= "
    GROUP BY u.id
    ORDER BY u.id DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$currentPage = 'customers';
$pageTitle = 'Customers Management';
$breadcrumb = 'System / Customers';
$base_path = '../';
?>
<?php include '../layouts/admin_header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers Management</title>
    
    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f6f8fb;
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
        }

        .topbar p {
            margin: 0;
            color: #666;
        }

        .search-box {
            background: white;
            padding: 18px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .search-box form {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .search-box button {
            background: black;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #fafafa;
        }

        .avatar {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            background: #eee;
        }

        .status-active {
            color: green;
            font-weight: bold;
        }

        .status-inactive {
            color: red;
            font-weight: bold;
        }

        .view-btn {
            background: #edf2ff;
            color: #2f5bea;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="page">
    <div class="topbar">
        <h1>Customers Management</h1>
        <p>Manage customer accounts and purchase history.</p>
    </div>

    <div class="search-box">
        <form method="GET">
            <input 
                type="text" 
                name="search"
                placeholder="Search customer by name or email..."
                value="<?= htmlspecialchars($search) ?>"
            >
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Avatar</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Total Orders</th>
                    <th>Total Spent</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <?php
                        $avatar = !empty($customer['avatar'])
                            ? "../../assets/avatar/" . $customer['avatar']
                            : "../../assets/avatar/default.png";
                        ?>
                        <tr>
                            <td>
                                <img src="<?= $avatar ?>" class="avatar">
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($customer['name']) ?></strong><br>
                                <small><?= htmlspecialchars($customer['email']) ?></small>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['phone'] ?? '-') ?>
                            </td>

                            <td>
                                <?= (int)$customer['total_orders'] ?>
                            </td>

                            <td>
                                $<?= number_format($customer['total_spent'], 2) ?>
                            </td>

                            <td>
                                <?php if ($customer['is_active'] == 1): ?>
                                    <span class="status-active">Active</span>
                                <?php else: ?>
                                    <span class="status-inactive">Inactive</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <a href="detail.php?id=<?= $customer['id'] ?>" class="view-btn">
                                    View Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">
                            No customers found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../layouts/admin_footer.php'; ?>
