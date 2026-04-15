<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

require_once "../config/database.php";

$currentPage = 'customers';
$pageTitle   = 'Customers';
$breadcrumb  = 'Operations / Customers';
$base_path   = '';

// ── Filters ──────────────────────────────────────
$search   = trim($_GET['search'] ?? '');
$role     = $_GET['role']   ?? '';
$status   = $_GET['status'] ?? '';
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset   = ($page_num - 1) * $per_page;

// ── Build query ───────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($role !== '') {
    $where[]  = "u.role = ?";
    $params[] = $role;
    $types   .= 's';
}
if ($status !== '') {
    $where[]  = "u.is_active = ?";
    $params[] = (int)$status;
    $types   .= 'i';
}
if ($search !== '') {
    $where[]  = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $like      = "%$search%";
    $params    = array_merge($params, [$like, $like, $like]);
    $types    .= 'sss';
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count total
$stmtCount = $conn->prepare("SELECT COUNT(*) FROM users u $whereSql");
if ($types) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$total_rows  = $stmtCount->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch users with order stats
$sql = "
    SELECT u.id, u.name, u.email, u.phone, u.role, u.is_active,
           u.avatar, u.created_at, u.updated_at,
           COUNT(DISTINCT o.id) AS order_count,
           COALESCE(SUM(o.total), 0) AS total_spent
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    $whereSql
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$allParams = array_merge($params, [$per_page, $offset]);
$allTypes  = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$customers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Summary counts ────────────────────────────────
$totalUsers  = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0];
$totalAdmins = $conn->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetch_row()[0];
$activeUsers = $conn->query("SELECT COUNT(*) FROM users WHERE is_active=1")->fetch_row()[0];
$newThisMonth = $conn->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at)=MONTH(NOW()) AND YEAR(created_at)=YEAR(NOW())")->fetch_row()[0];

include 'layouts/admin_header.php';
?>

<!-- ── Page header ── -->
<div class="adm-page-header">
    <div>
        <h1>Customers</h1>
        <p>Manage users and customers</p>
    </div>
</div>

<!-- ── KPI Cards ── -->
<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="stat-icon" style="background:#EEF4FF; color:#2C6ECB;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-label">Total Customers</div>
            <div class="stat-value"><?= number_format($totalUsers) ?></div>
        </div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-icon" style="background:#E8F9EE; color:#38CB89;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <div class="stat-label">Active</div>
            <div class="stat-value"><?= number_format($activeUsers) ?></div>
        </div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-icon" style="background:#FFF8EC; color:#F8A334;">
            <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
            <div class="stat-label">New This Month</div>
            <div class="stat-value"><?= number_format($newThisMonth) ?></div>
        </div>
    </div>
    <div class="adm-stat-card">
        <div class="stat-icon" style="background:#F3F0FF; color:#7C3AED;">
            <i class="fa-solid fa-user-shield"></i>
        </div>
        <div>
            <div class="stat-label">Admin</div>
            <div class="stat-value"><?= number_format($totalAdmins) ?></div>
        </div>
    </div>
</div>

<!-- ── Search & filter toolbar ── -->
<div class="adm-card" style="margin-bottom:20px;">
    <div class="adm-toolbar">
        <form method="GET" class="adm-search-form">
            <div class="adm-search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" placeholder="Search by name, email, phone..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="role" class="adm-select-sm">
                <option value="">All Roles</option>
                <option value="user"  <?= $role === 'user'  ? 'selected' : '' ?>>Customer</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <select name="status" class="adm-select-sm">
                <option value="">All Statuses</option>
                <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Locked</option>
            </select>
            <button type="submit" class="adm-btn adm-btn-primary">Filter</button>
            <?php if ($search || $role || $status !== ''): ?>
                <a href="customers.php" class="adm-btn adm-btn-outline">Clear Filter</a>
            <?php endif; ?>
        </form>
        <span style="color:var(--gray-400); font-size:13px;"><?= number_format($total_rows) ?> users</span>
    </div>
</div>

<!-- ── Customers table ── -->
<div class="adm-card">
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Last Active</th>
                    <th>Date Registered</th>
                    <th>Status</th>
                    <th style="text-align:center;">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($customers)): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:48px; color:var(--gray-400);">
                        <i class="fa-solid fa-users" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                        No users found
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($customers as $c):
                    $avatarUrl = $c['avatar'] && $c['avatar'] !== 'default.jpg'
                        ? '../assets/uploads/users/' . $c['avatar']
                        : null;
                    $initials = strtoupper(substr($c['name'], 0, 1));
                ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" class="adm-user-avatar" onerror="this.outerHTML='<div class=\'adm-user-avatar adm-avatar-init\'><?= $initials ?></div>'">
                            <?php else: ?>
                                <div class="adm-user-avatar adm-avatar-init"><?= $initials ?></div>
                            <?php endif; ?>
                            <div>
                                <p class="adm-cell-main"><?= htmlspecialchars($c['name']) ?></p>
                                <p class="adm-cell-sub"><?= htmlspecialchars($c['email']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="adm-badge <?= $c['role'] === 'admin' ? 'badge-purple' : 'badge-gray' ?>">
                            <?= $c['role'] === 'admin' ? 'Admin' : 'User' ?>
                        </span>
                    </td>
                    <td><?= (int)$c['order_count'] ?> orders</td>
                    <td><strong><?= number_format((int)$c['total_spent'], 0, ',', '.') ?>₫</strong></td>
                    <td class="adm-cell-sub">
                        <?= $c['updated_at'] ? date('d/m/Y', strtotime($c['updated_at'])) : '—' ?>
                    </td>
                    <td class="adm-cell-sub"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <span class="adm-badge <?= $c['is_active'] ? 'badge-green' : 'badge-red' ?>">
                            <?= $c['is_active'] ? 'Active' : 'Locked' ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <div class="adm-action-group">
                            <button class="adm-action-btn" title="View Details" onclick="openCustomerModal(<?= $c['id'] ?>)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="adm-action-btn" title="<?= $c['is_active'] ? 'Lock Account' : 'Unlock Account' ?>"
                                    onclick="toggleUserStatus(<?= $c['id'] ?>, <?= $c['is_active'] ?>)">
                                <i class="fa-solid <?= $c['is_active'] ? 'fa-lock' : 'fa-lock-open' ?>"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="adm-pagination">
        <span class="adm-pagination-info">Page <?= $page_num ?> / <?= $total_pages ?></span>
        <div class="adm-pagination-btns">
            <?php if ($page_num > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num - 1])) ?>" class="adm-page-btn">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            <?php endif; ?>
            <?php for ($p = max(1, $page_num - 2); $p <= min($total_pages, $page_num + 2); $p++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"
                   class="adm-page-btn <?= $p === $page_num ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <?php if ($page_num < $total_pages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page_num + 1])) ?>" class="adm-page-btn">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- ── Customer Detail Modal ── -->
<div class="adm-modal-overlay" id="customerModal">
    <div class="adm-modal adm-modal-lg">
        <div class="adm-modal-header">
            <h3 id="customerModalTitle">Customer Details</h3>
            <button class="adm-modal-close" onclick="closeModal('customerModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body" id="customerModalBody">
            <div class="adm-loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Loading...
            </div>
        </div>
    </div>
</div>

<style>
/* ── Toolbar & Forms ── */
.adm-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
}
.adm-search-form {
    display: flex;
    align-items: center;
    gap: 12px;
}
.adm-search-box {
    position: relative;
    display: flex;
    align-items: center;
}
.adm-search-box i {
    position: absolute;
    left: 14px;
    color: var(--gray-400);
    font-size: 14px;
}
.adm-search-box input {
    padding: 9px 16px 9px 38px;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
    width: 240px;
    transition: all 0.2s;
}
.adm-search-box input:focus {
    border-color: var(--black);
    box-shadow: 0 0 0 3px rgba(20,23,24,0.1);
}
.adm-select-sm, .adm-select {
    padding: 9px 16px;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    outline: none;
    background: #fff;
    cursor: pointer;
    color: var(--black);
}
.adm-select-sm:focus, .adm-select:focus { border-color: var(--black); }
.adm-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 6px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid transparent;
}
.adm-btn-primary { background: var(--black); color: #fff; }
.adm-btn-primary:hover { background: var(--gray-600); }
.adm-btn-outline { background: transparent; border-color: var(--gray-300); color: var(--black); }
.adm-btn-outline:hover { background: var(--gray-100); border-color: var(--gray-400); }

/* ── Modal Styles ── */
.adm-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(10, 17, 41, 0.6);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(4px);
}
.adm-modal-overlay.open { display: flex; }
.adm-modal {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    animation: modalFadeUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.adm-modal-lg { max-width: 700px; }
@keyframes modalFadeUp {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
.adm-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--gray-200);
}
.adm-modal-header h3 { font-size: 18px; font-weight: 700; color: var(--black); margin: 0; }
.adm-modal-close {
    background: var(--gray-100);
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--gray-600);
    transition: all 0.2s;
}
.adm-modal-close:hover { background: var(--gray-200); color: var(--black); }
.adm-modal-body { padding: 24px; }
.adm-loading { text-align: center; color: var(--gray-400); padding: 40px; font-size: 15px; }

/* ── Customer page specific ── */
.adm-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.adm-stat-card {
    background: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-label { font-size: 12px; color: var(--gray-400); font-weight: 500; margin-bottom: 4px; }
.stat-value { font-size: 22px; font-weight: 700; color: var(--black); }

.adm-user-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}
.adm-avatar-init {
    background: linear-gradient(135deg, #6d6af8, #2f326f);
    color: #fff;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.adm-info-block {
    background: var(--gray-100);
    border-radius: 8px;
    padding: 12px 14px;
}
.adm-info-block span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: var(--gray-400);
    margin-bottom: 4px;
}
.adm-info-block strong {
    font-size: 14px;
    color: var(--black);
    font-weight: 600;
}

@media (max-width: 900px) {
    .adm-stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
// ── Customer detail modal ──
async function openCustomerModal(id) {
    document.getElementById('customerModalTitle').textContent = 'Loading...';
    document.getElementById('customerModalBody').innerHTML = '<div class="adm-loading"><i class="fa-solid fa-spinner fa-spin"></i> Loading...</div>';
    openModal('customerModal');

    try {
        const res  = await fetch(`../controllers/AdminController.php?action=customer_detail&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error();

        const u = data.user, orders = data.orders;
        document.getElementById('customerModalTitle').textContent = u.name;

        const avatar = u.avatar && u.avatar !== 'default.jpg'
            ? `<img src="../assets/uploads/users/${u.avatar}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">`
            : `<div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#6d6af8,#2f326f);color:#fff;font-weight:800;font-size:28px;display:flex;align-items:center;justify-content:center;">${u.name[0].toUpperCase()}</div>`;

        let orderRows = orders.length
            ? orders.map(o => `<tr>
                <td style="font-weight:600;color:var(--blue);">${o.order_code}</td>
                <td>${new Date(o.created_at).toLocaleDateString('vi-VN')}</td>
                <td><strong>${Number(o.total).toLocaleString('vi-VN')}₫</strong></td>
                <td><span class="adm-badge badge-gray">${o.status}</span></td>
              </tr>`).join('')
            : '<tr><td colspan="4" style="text-align:center;color:var(--gray-400);padding:20px;">No orders found</td></tr>';

        document.getElementById('customerModalBody').innerHTML = `
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
            ${avatar}
            <div>
                <h3 style="font-size:18px;font-weight:700;margin:0 0 4px;">${u.name}</h3>
                <p style="color:var(--gray-400);font-size:13px;">${u.email}</p>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:20px;">
            <div class="adm-info-block"><span>Phone</span><strong>${u.phone || '—'}</strong></div>
            <div class="adm-info-block"><span>Role</span><strong>${u.role === 'admin' ? 'Admin' : 'User'}</strong></div>
            <div class="adm-info-block"><span>Registered Date</span><strong>${new Date(u.created_at).toLocaleDateString('en-US')}</strong></div>
            <div class="adm-info-block"><span>Status</span><strong>${u.is_active ? 'Active' : 'Locked'}</strong></div>
            <div class="adm-info-block"><span>Total Orders</span><strong>${data.order_count}</strong></div>
            <div class="adm-info-block"><span>Total Spent</span><strong>${Number(data.total_spent).toLocaleString('vi-VN')}₫</strong></div>
        </div>
        <h4 style="font-size:14px;font-weight:600;margin-bottom:10px;">Order History</h4>
        <table class="adm-table">
            <thead><tr><th>Order Code</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>${orderRows}</tbody>
        </table>`;
    } catch {
        document.getElementById('customerModalBody').innerHTML = '<p style="text-align:center;color:var(--red);">Failed to load data.</p>';
    }
}

// ── Toggle user active status ──
async function toggleUserStatus(id, isActive) {
    const action = isActive ? 'Lock' : 'Unlock';
    if (!confirm(`Are you sure you want to ${action.toLowerCase()} this account?`)) return;

    const fd = new FormData();
    fd.append('user_id', id);
    fd.append('is_active', isActive ? 0 : 1);

    try {
        const res  = await fetch('../controllers/AdminController.php?action=toggle_user', { method:'POST', body: fd });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Action failed: ' + (data.message || ''));
    } catch {
        alert('Connection error!');
    }
}

// ── Modal helpers ──
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.adm-modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});
</script>

<?php include 'layouts/admin_footer.php'; ?>
