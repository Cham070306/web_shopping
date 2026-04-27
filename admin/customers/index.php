<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        COUNT(DISTINCT o.id) AS total_orders,
        COALESCE(SUM(o.total), 0) AS total_spent
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

if ($stmt === false) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$currentPage = 'customers';
$pageTitle = 'Customers Management';
$breadcrumb = 'System / Customers';
$base_path = '../';

include '../layouts/admin_header.php';
?>

<style>
    * {
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .customers-page {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        overflow: hidden;
    }

    /* header */
    .customers-header h1 {
        margin: 0 0 8px;
        font-size: 28px;
        color: #111827;
    }

    .customers-header p {
        margin: 0 0 24px;
        color: #64748b;
        font-size: 14px;
    }

    /* search */
    .customer-search-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
    }

    .customer-search-card form {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .customer-search-card input {
        flex: 1;
        min-width: 220px;
        height: 44px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        padding: 0 16px;
        font-size: 14px;
    }

    .customer-search-card button {
        height: 44px;
        background: #111827;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0 20px;
        font-weight: 600;
        cursor: pointer;
    }

    /* table */
    .customer-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .05);
        width: 100%;
        overflow: hidden;
    }

    .adm-table-wrap {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .customer-table {
        width: 100%;
        border-collapse: collapse;
    }

    .customer-table th,
    .customer-table td {
        padding: 12px 10px;
        border-bottom: 1px solid #f1f5f9;
        text-align: left;
        font-size: 13px;
        vertical-align: middle;
    }

    .customer-table th {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        white-space: nowrap;
    }

    /* avatar */
    .customer-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
    }

    /* customer info */
    .customer-info {
        max-width: 240px;
    }

    .customer-name {
        font-weight: 700;
        color: #111827;
        white-space: normal;
        word-break: break-word;
    }

    .customer-email {
        color: #64748b;
        font-size: 12px;
        white-space: normal;
        word-break: break-word;
    }

    /* status */
    .status-badge {
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-active {
        background: #dcfce7;
        color: #16a34a;
    }

    .status-inactive {
        background: #fee2e2;
        color: #ef4444;
    }

    /* button */
    .view-btn {
        display: inline-block;
        background: #eef2ff;
        color: #2563eb;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .empty {
        text-align: center;
        padding: 40px;
        color: #64748b;
    }

    /* tablet */
    @media(max-width:992px) {
        .customers-page {
            padding: 14px;
        }

        .customer-table th,
        .customer-table td {
            padding: 10px 8px;
            font-size: 12px;
        }

        .customer-avatar {
            width: 36px;
            height: 36px;
        }

        .customer-name {
            font-size: 12px;
        }

        .customer-email {
            font-size: 11px;
        }

        .view-btn {
            padding: 5px 10px;
            font-size: 11px;
        }
    }

    /* mobile */
    @media(max-width:768px) {
        .customer-search-card form {
            flex-direction: column;
        }

        .customer-search-card input,
        .customer-search-card button {
            width: 100%;
        }

        .customers-header h1 {
            font-size: 22px;
        }

        /* tự co bảng */
        .customer-table {
            transform: scale(0.92);
            transform-origin: top left;
            width: 108%;
        }

        .customer-table th,
        .customer-table td {
            padding: 8px 6px;
            font-size: 11px;
        }

        .customer-avatar {
            width: 32px;
            height: 32px;
        }
    }

    /* small mobile */
    @media(max-width:576px) {
        .customers-page {
            padding: 10px;
        }

        .customer-table {
            transform: scale(0.84);
            transform-origin: top left;
            width: 120%;
        }

        .customers-header h1 {
            font-size: 18px;
        }

        .customers-header p {
            font-size: 12px;
        }

        .view-btn {
            font-size: 10px;
            padding: 4px 8px;
        }
    }

    /* extra small */
    @media(max-width:420px) {
        .customer-table {
            transform: scale(0.76);
            transform-origin: top left;
            width: 132%;
        }

        .customers-page {
            padding: 8px;
        }

        .customer-avatar {
            width: 28px;
            height: 28px;
        }
    }
</style>

<div class="customers-page">

    <div class="customers-header">
        <h1>Customers Management</h1>
        <p>Manage customer accounts and purchase history.</p>
    </div>

    <div class="customer-search-card">
        <form method="GET">
            <input type="text" name="search" placeholder="Search customer by name or email..."
                value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="customer-card">
        <div class="adm-table-wrap">
            <table class="customer-table">
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Spent</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($customers)): ?>
                        <?php foreach ($customers as $customer): ?>
                            <?php
                            $rawAvatar = trim($customer['avatar'] ?? '');

                            if ($rawAvatar === '') {
                                $avatar = "../../assets/avatar/default.png";
                            } elseif (str_contains($rawAvatar, 'assets/') || str_contains($rawAvatar, '/')) {
                                $avatar = "../../" . ltrim($rawAvatar, '/');
                            } else {
                                $avatar = "../../assets/avatar/" . $rawAvatar;
                            }
                            ?>

                            <tr>
                                <td>
                                    <img src="<?= htmlspecialchars($avatar) ?>" class="customer-avatar"
                                        onerror="this.src='../../assets/avatar/default.png'">
                                </td>

                                <td>
                                    <div class="customer-info">
                                        <div class="customer-name">
                                            <?= htmlspecialchars($customer['name']) ?>
                                        </div>

                                        <div class="customer-email">
                                            <?= htmlspecialchars($customer['email']) ?>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars($customer['phone'] ?? '-') ?>
                                </td>

                                <td>
                                    <?= (int) $customer['total_orders'] ?>
                                </td>

                                <td>
                                    <strong>
                                        $<?= number_format((float) $customer['total_spent'], 2) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php if ((int) $customer['is_active'] === 1): ?>
                                        <span class="status-badge status-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a href="detail.php?id=<?= (int) $customer['id'] ?>" class="view-btn">
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

</div>

<?php include '../layouts/admin_footer.php'; ?>