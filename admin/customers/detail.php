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

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    echo "Invalid customer ID.";
    exit;
}

// Customer info
$stmt = $conn->prepare("
    SELECT 
        id, name, email, phone, avatar, gender, date_of_birth,
        is_active, last_login, created_at
    FROM users
    WHERE id = ? AND role = 'user'
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    echo "Customer not found.";
    exit;
}

// Addresses
$stmt = $conn->prepare("
    SELECT *
    FROM user_addresses
    WHERE user_id = ?
    ORDER BY is_default DESC, id DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$addresses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Orders
$stmt = $conn->prepare("
    SELECT id, order_code, total, payment_method, payment_status, status, created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Notes
$stmt = $conn->prepare("
    SELECT cn.*, u.name AS admin_name
    FROM customer_notes cn
    LEFT JOIN users u ON cn.admin_id = u.id
    WHERE cn.user_id = ?
    ORDER BY cn.created_at DESC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$totalOrders = count($orders);
$totalSpent = array_sum(array_column($orders, 'total'));

$avatar = !empty($customer['avatar'])
    ? "../../assets/avatar/" . $customer['avatar']
    : "../../assets/avatar/default.png";
$currentPage = 'customers';
$pageTitle = 'Customer Detail';
$breadcrumb = 'System / Customers / Detail';
$base_path = '../';
?>
<?php include '../layouts/admin_header.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Customer Detail</title>
    <style>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .topbar h1 {
            margin: 0 0 6px;
            font-size: 28px;
        }

        .topbar p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .back-link {
            color: #111;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .profile-card {
            background: #fff;
            border-radius: 16px;
            padding: 22px;
            display: grid;
            grid-template-columns: 120px 1fr 280px;
            gap: 22px;
            align-items: center;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
            margin-bottom: 22px;
        }

        .avatar {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            background: #eee;
        }

        .profile-info h2 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .profile-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        .active {
            background: #e9f9ef;
            color: #1f7a3f;
        }

        .inactive {
            background: #ffeaea;
            color: #d93025;
        }

        .stats {
            display: grid;
            gap: 12px;
        }

        .stat-box {
            background: #f6f8fb;
            border-radius: 12px;
            padding: 14px;
        }

        .stat-box strong {
            display: block;
            font-size: 22px;
            margin-bottom: 4px;
        }

        .stat-box span {
            color: #666;
            font-size: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
            margin-bottom: 22px;
        }

        .card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        .card h3 {
            margin: 0 0 16px;
            font-size: 18px;
        }

        .address-item,
        .note-item {
            border-bottom: 1px solid #eee;
            padding: 12px 0;
        }

        .address-item:last-child,
        .note-item:last-child {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            background: #edf2ff;
            color: #2f5bea;
            font-size: 12px;
            margin-left: 6px;
        }

        .muted {
            color: #777;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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

        .status-order {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #eef1f6;
            color: #333;
        }

        .empty {
            padding: 20px 0;
            color: #777;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .profile-card {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .avatar {
                margin: auto;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .table-wrap {
                overflow-x: auto;
            }

            table {
                min-width: 760px;
            }
        }
    </style>
</head>

<body>
<div class="page">
    <div class="topbar">
        <div>
            <h1>Customer Detail</h1>
            <p>View customer profile, addresses, notes and order history.</p>
        </div>
        <a href="index.php" class="back-link">← Back to Customers</a>
    </div>

    <div class="profile-card">
        <img src="<?= htmlspecialchars($avatar) ?>" class="avatar" alt="Avatar">

        <div class="profile-info">
            <h2><?= htmlspecialchars($customer['name']) ?></h2>
            <p>Email: <?= htmlspecialchars($customer['email']) ?></p>
            <p>Phone: <?= htmlspecialchars($customer['phone'] ?? '-') ?></p>
            <p>Gender: <?= htmlspecialchars($customer['gender'] ?? '-') ?></p>
            <p>Date of birth: <?= !empty($customer['date_of_birth']) ? date('d/m/Y', strtotime($customer['date_of_birth'])) : '-' ?></p>
            <p>Joined: <?= date('d/m/Y', strtotime($customer['created_at'])) ?></p>
            <p>Last login: <?= !empty($customer['last_login']) ? date('d/m/Y H:i', strtotime($customer['last_login'])) : '-' ?></p>

            <span class="status <?= $customer['is_active'] ? 'active' : 'inactive' ?>">
                <?= $customer['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
        </div>

        <div class="stats">
            <div class="stat-box">
                <strong><?= (int)$totalOrders ?></strong>
                <span>Total Orders</span>
            </div>
            <div class="stat-box">
                <strong>$<?= number_format($totalSpent, 2) ?></strong>
                <span>Total Spent</span>
            </div>
        </div>
    </div>

    <div class="grid">
        <div class="card">
            <h3>Addresses</h3>

            <?php if (!empty($addresses)): ?>
                <?php foreach ($addresses as $address): ?>
                    <div class="address-item">
                        <strong><?= htmlspecialchars($address['full_name']) ?></strong>
                        <?php if (!empty($address['is_default'])): ?>
                            <span class="badge">Default</span>
                        <?php endif; ?>
                        <p class="muted">
                            <?= htmlspecialchars($address['phone']) ?><br>
                            <?= htmlspecialchars($address['address']) ?><br>
                            <?= htmlspecialchars($address['ward'] ?? '') ?>
                            <?= htmlspecialchars($address['district'] ?? '') ?>
                            <?= htmlspecialchars($address['city'] ?? '') ?>
                            <?= htmlspecialchars($address['province'] ?? '') ?><br>
                            <?= htmlspecialchars($address['country'] ?? '') ?>
                            <?= htmlspecialchars($address['zip_code'] ?? '') ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">No addresses found.</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3>Customer Notes</h3>

            <?php if (!empty($notes)): ?>
                <?php foreach ($notes as $note): ?>
                    <div class="note-item">
                        <p><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                        <div class="muted">
                            By <?= htmlspecialchars($note['admin_name'] ?? 'Admin') ?>
                            • <?= date('d/m/Y H:i', strtotime($note['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty">No notes found.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <h3>Order History</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Payment Status</th>
                        <th>Order Status</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_code']) ?></td>
                                <td>$<?= number_format($order['total'], 2) ?></td>
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                <td><span class="status-order"><?= htmlspecialchars($order['payment_status']) ?></span></td>
                                <td><span class="status-order"><?= htmlspecialchars($order['status']) ?></span></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../layouts/admin_footer.php'; ?>