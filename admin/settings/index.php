<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
//     header("Location: ../../user/index.php");
//     exit();
// }

require_once "../../config/database.php";

// ==========================
// TEAM MANAGEMENT ACTIONS
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['team_action'])) {

    $teamAction = $_POST['team_action'];

    if ($teamAction === 'add') {
        $name = trim($_POST['member_name'] ?? '');
        $email = trim($_POST['member_email'] ?? '');
        $role = trim($_POST['member_role'] ?? 'Support');

        if ($name !== '' && $email !== '') {
            $stmt = $conn->prepare("
                INSERT INTO team_members (name, email, role, status)
                VALUES (?, ?, ?, 'pending')
                ON DUPLICATE KEY UPDATE 
                    name = VALUES(name),
                    role = VALUES(role)
            ");
            $stmt->bind_param("sss", $name, $email, $role);
            $stmt->execute();
        }

        header("Location: index.php?tab=team");
        exit;
    }

    if ($teamAction === 'toggle_status') {
        $id = (int) ($_POST['member_id'] ?? 0);

        $stmt = $conn->prepare("
            UPDATE team_members
            SET status = IF(status = 'active', 'pending', 'active')
            WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: index.php?tab=team");
        exit;
    }

    if ($teamAction === 'delete') {
        $id = (int) ($_POST['member_id'] ?? 0);

        $stmt = $conn->prepare("DELETE FROM team_members WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: index.php?tab=team");
        exit;
    }
}

// ==========================
// ACCOUNT & BILLING ACTIONS
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account_action'])) {
    $accountAction = $_POST['account_action'];

    // 1. Chức năng Xóa tài khoản
    if ($accountAction === 'delete_account') {
        $userId = $_SESSION['user']['id'] ?? 0;
        if ($userId) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                session_destroy();
                header("Location: ../../user/login.php?msg=account_deleted");
                exit;
            }
        }
    }

    // 2. Chức năng Nâng cấp gói (Demo)
    if ($accountAction === 'upgrade_plan') {
        $newPlan = 'Premium Plan'; // Gói mới
        $stmt = $conn->prepare("INSERT INTO settings (`key`, `value`) VALUES ('current_plan', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
        $stmt->bind_param("s", $newPlan);
        $stmt->execute();

        header("Location: index.php?tab=account&success=upgraded");
        exit;
    }

    // 3. Tải hóa đơn (Demo Redirect)
    if ($accountAction === 'download_invoice') {
        $invoiceId = (int) ($_POST['invoice_id'] ?? 0);
        // Ở thực tế bạn sẽ xử lý xuất file PDF ở file này
        header("Location: print_invoice.php?id=" . $invoiceId);
        exit;
    }
}

// ==========================
// SETTINGS UPDATE ACTIONS
// ==========================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['team_action']) && !isset($_POST['account_action'])) {

    $allowed_settings = [
        'site_name',
        'support_email',
        'support_phone',
        'facebook_link',
        'instagram_link',
        'shipping_fee',
        'refund_policy',
        'return_policy',
        'shipping_policy'
    ];

    foreach ($allowed_settings as $key) {
        if (isset($_POST[$key])) {
            $value = $_POST[$key];
            $stmt = $conn->prepare("
                INSERT INTO settings (`key`, `value`)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ");
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
    }

    $success = "Settings updated successfully!";
}

if (isset($_GET['success']) && $_GET['success'] === 'upgraded') {
    $success = "Plan upgraded successfully!";
}

// Fetch current settings
$result = $conn->query("SELECT * FROM settings");
$currentSettings = [];
while ($row = $result->fetch_assoc()) {
    $currentSettings[$row['key']] = $row['value'];
}

// Fetch team members
$teamResult = $conn->query("SELECT * FROM team_members ORDER BY id ASC");
$teamMembers = $teamResult ? $teamResult->fetch_all(MYSQLI_ASSOC) : [];

$activeTab = $_GET['tab'] ?? 'general';
$currentPlan = $currentSettings['current_plan'] ?? 'Professional Plan';

$billingHistory = [
    [
        'date' => '2026-04-01',
        'plan' => 'Professional Plan',
        'amount' => '$150',
        'status' => 'Paid'
    ],
    [
        'date' => '2026-03-01',
        'plan' => 'Professional Plan',
        'amount' => '$150',
        'status' => 'Paid'
    ],
    [
        'date' => '2026-02-01',
        'plan' => 'Starter Plan',
        'amount' => '$49',
        'status' => 'Paid'
    ]
];

$currentPage = 'settings';
$pageTitle = 'Website Settings';
$breadcrumb = 'System / Settings';
$base_path = '../';
?>

<?php include '../layouts/admin_header.php'; ?>

<style>
    .settings-page {
        max-width: 980px;
        margin: 0 auto;
    }

    .settings-hero {
        text-align: center;
        margin-bottom: 28px;
    }

    .settings-hero h1 {
        margin: 0;
        font-size: 34px;
        color: #111827;
    }

    .settings-hero p {
        margin-top: 8px;
        color: #64748b;
    }

    .settings-tabs {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        margin-bottom: 24px;
    }

    .tab-btn {
        padding: 14px;
        background: #fff;
        border: none;
        border-right: 1px solid #e5e7eb;
        color: #64748b;
        cursor: pointer;
        font-weight: 600;
    }

    .tab-btn:last-child {
        border-right: none;
    }

    .tab-btn.active {
        background: #111827;
        color: #fff;
    }

    .settings-section {
        display: none;
    }

    .settings-section.active {
        display: block;
    }

    .setting-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 28px;
        margin-bottom: 22px;
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
    }

    .setting-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .setting-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #111827;
        font-size: 18px;
    }

    .setting-card-header h2 {
        margin: 0;
        font-size: 22px;
        color: #111827;
    }

    .setting-row {
        border-top: 1px solid #eef2f7;
        padding-top: 18px;
        margin-top: 18px;
    }

    .setting-row:first-of-type {
        border-top: none;
        padding-top: 0;
    }

    .setting-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        color: #111827;
    }

    .setting-help {
        margin: -4px 0 12px;
        color: #64748b;
        font-size: 13px;
    }

    .setting-input {
        width: 100%;
        height: 48px;
        border: 1px solid #d1d5db;
        border-radius: 14px;
        padding: 0 16px;
        outline: none;
        font-size: 14px;
    }

    .policy-textarea {
        height: auto !important;
        min-height: 140px;
        padding: 16px;
        resize: vertical;
        line-height: 1.6;
    }

    .setting-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 9px 14px;
        border-radius: 999px;
        background: #dcfce7;
        color: #16a34a;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .setting-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .btn-primary {
        background: #111827;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 13px 22px;
        font-weight: 700;
        cursor: pointer;
    }

    .btn-secondary {
        background: #fff;
        color: #111827;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 13px 22px;
        font-weight: 700;
        cursor: pointer;
    }

    .team-list {
        margin-top: 18px;
    }

    .team-member {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0;
        border-top: 1px solid #eef2f7;
    }

    .team-user {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .team-avatar {
        width: 42px;
        height: 42px;
        border-radius: 999px;
        background: #111827;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
    }

    .team-user p {
        margin: 4px 0 0;
        color: #64748b;
        font-size: 13px;
    }

    .team-tags {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .role-badge,
    .active-badge,
    .pending-badge {
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
    }

    .role-badge {
        background: #f3f4f6;
        color: #111827;
    }

    .active-badge {
        background: #dcfce7;
        color: #16a34a;
    }

    .pending-badge {
        background: #fef3c7;
        color: #d97706;
    }

    .danger-card {
        border-color: #fecaca;
    }

    .danger-icon {
        background: #fef2f2;
        color: #ef4444;
    }

    .danger-text {
        color: #ef4444;
    }

    .delete-btn {
        background: #ef4444;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 13px 22px;
        font-weight: 700;
        cursor: pointer;
    }

    @media(max-width: 900px) {
        .settings-tabs {
            grid-template-columns: repeat(2, 1fr);
        }

        .setting-grid {
            grid-template-columns: 1fr;
        }

        .invite-form-db {
            display: grid;
            grid-template-columns: 1fr 1fr 150px 170px;
            gap: 10px;
            margin: 16px 0 20px;
        }

        .invite-form-db input,
        .invite-form-db select {
            height: 44px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 0 12px;
        }

        .invite-form-db button {
            background: #111827;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .mini-action-btn,
        .mini-delete-btn {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
        }

        .mini-action-btn {
            background: #eef2ff;
            color: #4f46e5;
        }

        .mini-delete-btn {
            background: #fee2e2;
            color: #ef4444;
        }

        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .billing-table th,
        .billing-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
            font-size: 14px;
        }

        .billing-table th {
            color: #64748b;
            background: #f8fafc;
        }
    }
</style>

<div class="settings-page">

    <div class="settings-hero">
        <h1>Settings</h1>
        <p>Manage your website configuration and store information.</p>
    </div>

    <div class="settings-tabs">
        <button type="button" class="tab-btn <?= $activeTab === 'general' ? 'active' : '' ?>"
            data-tab="general">General</button>
        <button type="button" class="tab-btn <?= $activeTab === 'policies' ? 'active' : '' ?>"
            data-tab="policies">Policies</button>
        <button type="button" class="tab-btn <?= $activeTab === 'social' ? 'active' : '' ?>"
            data-tab="social">Social</button>
        <button type="button" class="tab-btn <?= $activeTab === 'shipping' ? 'active' : '' ?>"
            data-tab="shipping">Shipping</button>
        <button type="button" class="tab-btn <?= $activeTab === 'team' ? 'active' : '' ?>" data-tab="team">Team</button>
        <button type="button" class="tab-btn <?= $activeTab === 'account' ? 'active' : '' ?>"
            data-tab="account">Account</button>
    </div>

    <?php if (!empty($success)): ?>
        <div class="status-badge">
            <i class="fa-solid fa-circle-check"></i>&nbsp;
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="settings-section <?= $activeTab === 'general' ? 'active' : '' ?>" id="tab-general">
        <form method="POST" action="?tab=general">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-icon">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <h2>Store Information</h2>
                </div>

                <div class="setting-row">
                    <label class="setting-label">Site Name</label>
                    <input class="setting-input" type="text" name="site_name"
                        value="<?= htmlspecialchars($currentSettings['site_name'] ?? '') ?>">
                </div>

                <div class="setting-grid setting-row">
                    <div>
                        <label class="setting-label">Support Email</label>
                        <p class="setting-help">Customer support email address.</p>
                        <input class="setting-input" type="email" name="support_email"
                            value="<?= htmlspecialchars($currentSettings['support_email'] ?? '') ?>">
                    </div>

                    <div>
                        <label class="setting-label">Support Phone</label>
                        <p class="setting-help">Phone number displayed on website.</p>
                        <input class="setting-input" type="text" name="support_phone"
                            value="<?= htmlspecialchars($currentSettings['support_phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="setting-actions">
                    <button type="submit" class="btn-primary">Save Settings</button>
                    <button type="reset" class="btn-secondary">Reset</button>
                </div>
            </div>
        </form>
    </div>

    <div class="settings-section <?= $activeTab === 'policies' ? 'active' : '' ?>" id="tab-policies">
        <form method="POST" action="?tab=policies">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-icon">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                    <h2>Business Policies</h2>
                </div>

                <div class="setting-row">
                    <label class="setting-label">Refund Policy</label>
                    <textarea class="setting-input policy-textarea" name="refund_policy"
                        rows="5"><?= htmlspecialchars($currentSettings['refund_policy'] ?? "We provide full refunds within 30 days of purchase for unused products that remain in original packaging.") ?></textarea>
                </div>

                <div class="setting-row">
                    <label class="setting-label">Return Policy</label>
                    <textarea class="setting-input policy-textarea" name="return_policy"
                        rows="5"><?= htmlspecialchars($currentSettings['return_policy'] ?? "You can return unused products within 30 days as long as items remain in original condition and packaging.") ?></textarea>
                </div>

                <div class="setting-row">
                    <label class="setting-label">Shipping Policy</label>
                    <textarea class="setting-input policy-textarea" name="shipping_policy"
                        rows="5"><?= htmlspecialchars($currentSettings['shipping_policy'] ?? "Orders are processed within 1–3 business days.") ?></textarea>
                </div>

                <div class="setting-actions">
                    <button type="submit" class="btn-primary">Save Policies</button>
                </div>
            </div>
        </form>
    </div>

    <div class="settings-section <?= $activeTab === 'social' ? 'active' : '' ?>" id="tab-social">
        <form method="POST" action="?tab=social">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-icon">
                        <i class="fa-solid fa-share-nodes"></i>
                    </div>
                    <h2>Social Integration</h2>
                </div>

                <div class="setting-grid">
                    <div class="setting-row">
                        <label class="setting-label">Facebook Link</label>
                        <input class="setting-input" type="text" name="facebook_link"
                            value="<?= htmlspecialchars($currentSettings['facebook_link'] ?? '') ?>">
                    </div>

                    <div class="setting-row">
                        <label class="setting-label">Instagram Link</label>
                        <input class="setting-input" type="text" name="instagram_link"
                            value="<?= htmlspecialchars($currentSettings['instagram_link'] ?? '') ?>">
                    </div>
                </div>

                <div class="setting-actions">
                    <button type="submit" class="btn-primary">Save Social Links</button>
                </div>
            </div>
        </form>
    </div>

    <div class="settings-section <?= $activeTab === 'shipping' ? 'active' : '' ?>" id="tab-shipping">
        <form method="POST" action="?tab=shipping">
            <div class="setting-card">
                <div class="setting-card-header">
                    <div class="setting-icon">
                        <i class="fa-solid fa-truck"></i>
                    </div>
                    <h2>Shipping Configuration</h2>
                </div>

                <div class="setting-row">
                    <label class="setting-label">Shipping Fee</label>
                    <p class="setting-help">Default shipping fee applied at checkout.</p>
                    <input class="setting-input" type="number" name="shipping_fee"
                        value="<?= htmlspecialchars($currentSettings['shipping_fee'] ?? '') ?>">
                </div>

                <div class="setting-actions">
                    <button type="submit" class="btn-primary">Save Shipping</button>
                </div>
            </div>
        </form>
    </div>

    <div class="settings-section <?= $activeTab === 'team' ? 'active' : '' ?>" id="tab-team">
        <div class="setting-card">
            <div class="setting-card-header">
                <div class="setting-icon">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <h2>Team Management</h2>
            </div>

            <p class="setting-help">Manage admin members and internal roles for your store.</p>

            <form method="POST" action="?tab=team" class="invite-form-db">
                <input type="hidden" name="team_action" value="add">

                <input type="text" name="member_name" placeholder="Member name" required>
                <input type="email" name="member_email" placeholder="Member email" required>

                <select name="member_role">
                    <option value="Support">Support</option>
                    <option value="Manager">Manager</option>
                    <option value="Admin">Admin</option>
                    <option value="Owner">Owner</option>
                </select>

                <button type="submit">
                    <i class="fa-solid fa-plus"></i> Invite Member
                </button>
            </form>

            <div class="team-list">
                <?php foreach ($teamMembers as $member): ?>
                    <div class="team-member">
                        <div class="team-user">
                            <div class="team-avatar">
                                <?= strtoupper(substr($member['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($member['name']) ?></strong>
                                <p><?= htmlspecialchars($member['email']) ?></p>
                            </div>
                        </div>

                        <div class="team-tags">
                            <span class="role-badge"><?= htmlspecialchars($member['role']) ?></span>

                            <?php if ($member['status'] === 'active'): ?>
                                <span class="active-badge">Active</span>
                            <?php else: ?>
                                <span class="pending-badge">Pending</span>
                            <?php endif; ?>

                            <form method="POST" action="?tab=team" style="display:inline;">
                                <input type="hidden" name="team_action" value="toggle_status">
                                <input type="hidden" name="member_id" value="<?= (int) $member['id'] ?>">
                                <button type="submit" class="mini-action-btn">
                                    <i class="fa-solid fa-rotate"></i>
                                </button>
                            </form>

                            <form method="POST" action="?tab=team" style="display:inline;"
                                onsubmit="return confirm('Delete this member?')">
                                <input type="hidden" name="team_action" value="delete">
                                <input type="hidden" name="member_id" value="<?= (int) $member['id'] ?>">
                                <button type="submit" class="mini-delete-btn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="settings-section <?= $activeTab === 'account' ? 'active' : '' ?>" id="tab-account">

        <div class="setting-card">
            <div class="setting-card-header">
                <div class="setting-icon">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
                <h2>Billing & Subscription</h2>
            </div>

            <div class="setting-row">
                <label class="setting-label">Current Plan</label>
                <p class="setting-help">
                    <?= htmlspecialchars($currentPlan) ?> — suitable for ecommerce management.
                </p>

                <div class="setting-actions">
                    <button type="button" class="btn-secondary js-upgrade-plan">Upgrade Plan</button>
                    <button type="button" class="btn-secondary js-billing-history">Billing History</button>
                    <button type="button" class="btn-secondary js-download-invoice">Download Invoice</button>
                </div>

                <div class="billing-history-box" style="display:none; margin-top:20px;">
                    <table class="billing-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($billingHistory as $bill): ?>
                                <tr>
                                    <td><?= htmlspecialchars($bill['date']) ?></td>
                                    <td><?= htmlspecialchars($bill['plan']) ?></td>
                                    <td><?= htmlspecialchars($bill['amount']) ?></td>
                                    <td>
                                        <span class="active-badge"><?= htmlspecialchars($bill['status']) ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="setting-card danger-card">
            <div class="setting-card-header">
                <div class="setting-icon danger-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2>Danger Zone</h2>
            </div>

            <div class="setting-row">
                <label class="setting-label danger-text">Delete Account</label>
                <p class="setting-help">Delete everything. Your account and data will be gone for good.</p>

                <button type="button" class="delete-btn js-delete-account">Delete Account</button>
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.tab-btn');
        const sections = document.querySelectorAll('.settings-section');

        buttons.forEach(button => {
            button.addEventListener('click', function () {
                const tab = this.dataset.tab;

                buttons.forEach(btn => btn.classList.remove('active'));
                sections.forEach(section => section.classList.remove('active'));

                this.classList.add('active');

                const target = document.getElementById('tab-' + tab);
                if (target) {
                    target.classList.add('active');
                }

                const url = new URL(window.location);
                url.searchParams.set('tab', tab);
                window.history.replaceState({}, '', url);
            });
        });
    });

    // JS xác nhận bảo mật cao cho phần xóa tài khoản
    function confirmDelete() {
        const userInput = prompt("Hành động này không thể hoàn tác. Dữ liệu của bạn sẽ bị xóa vĩnh viễn. Nhập chữ 'DELETE' để xác nhận:");
        if (userInput === "DELETE") {
            return true;
        } else {
            alert("Xác nhận thất bại. Tài khoản của bạn được an toàn.");
            return false;
        }
    }
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const upgradeBtn = document.querySelector('.js-upgrade-plan');
    const billingBtn = document.querySelector('.js-billing-history');
    const invoiceBtn = document.querySelector('.js-download-invoice');
    const deleteBtn = document.querySelector('.js-delete-account');
    const billingBox = document.querySelector('.billing-history-box');

    if (upgradeBtn) {
        upgradeBtn.addEventListener('click', function () {
            alert('Upgrade plan feature is ready for future payment integration.');
        });
    }

    if (billingBtn && billingBox) {
        billingBtn.addEventListener('click', function () {
            billingBox.style.display = billingBox.style.display === 'none' ? 'block' : 'none';
        });
    }

    if (invoiceBtn) {
        invoiceBtn.addEventListener('click', function () {
            const content =
`3legant Store Invoice
Plan: Professional Plan
Amount: $150/month
Status: Paid
Date: 2026-04-01`;

            const blob = new Blob([content], { type: 'text/plain' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = '3legant_invoice.txt';
            link.click();
        });
    }

    if (deleteBtn) {
        deleteBtn.addEventListener('click', function () {
            if (confirm('This is a demo action. Real account deletion is disabled for safety.')) {
                alert('Demo only: account was not deleted.');
            }
        });
    }
});
</script>
<?php include '../layouts/admin_footer.php'; ?>