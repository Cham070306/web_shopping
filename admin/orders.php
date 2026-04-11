<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../user/login.php");
    exit;
}

require_once "../config/database.php";

$currentPage = 'orders';
$pageTitle   = 'Orders';
$breadcrumb  = 'Sales / Orders';
$base_path   = '';

// ── Filters ──────────────────────────────────────
$status   = $_GET['status']   ?? '';
$search   = trim($_GET['search']  ?? '');
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset   = ($page_num - 1) * $per_page;

// ── Build query ───────────────────────────────────
$where  = [];
$params = [];
$types  = '';

if ($status !== '') {
    $where[]  = "o.status = ?";
    $params[] = $status;
    $types   .= 's';
}
if ($search !== '') {
    $where[]  = "(o.order_code LIKE ? OR o.full_name LIKE ? OR o.email LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}

$whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Count total
$countSql  = "SELECT COUNT(*) FROM orders o $whereSql";
$stmtCount = $conn->prepare($countSql);
if ($types) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$total_rows  = $stmtCount->get_result()->fetch_row()[0];
$total_pages = max(1, ceil($total_rows / $per_page));

// Fetch orders
$sql = "
    SELECT o.id, o.order_code, o.full_name, o.email, o.phone,
           o.total, o.status, o.payment_method, o.payment_status,
           o.created_at, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    $whereSql
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
";
$stmt = $conn->prepare($sql);
$allParams = array_merge($params, [$per_page, $offset]);
$allTypes  = $types . 'ii';
$stmt->bind_param($allTypes, ...$allParams);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Status summary counts ─────────────────────────
$summaryRes = $conn->query("
    SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status
");
$summary = [];
while ($row = $summaryRes->fetch_assoc()) {
    $summary[$row['status']] = $row['cnt'];
}
$summary['all'] = array_sum($summary);

include 'layouts/admin_header.php';
?>

<!-- ── Page header ── -->
<div class="adm-page-header">
    <div>
        <h1>Orders</h1>
        <p>Quản lý đơn hàng của cửa hàng</p>
    </div>
    <div style="display:flex; gap:10px;">
        <button class="adm-btn adm-btn-outline" onclick="exportOrders()">
            <i class="fa-solid fa-download"></i> Export CSV
        </button>
    </div>
</div>

<!-- ── KPI summary tabs ── -->
<div class="adm-order-tabs">
    <?php
    $tabs = [
        ''          => ['label' => 'Tất cả',       'cls' => ''],
        'pending'   => ['label' => 'Chờ xử lý',    'cls' => 'tab-orange'],
        'confirmed' => ['label' => 'Đã xác nhận',  'cls' => 'tab-blue'],
        'shipping'  => ['label' => 'Đang giao',    'cls' => 'tab-sky'],
        'delivered' => ['label' => 'Đã giao',      'cls' => 'tab-green'],
        'cancelled' => ['label' => 'Đã hủy',       'cls' => 'tab-red'],
    ];
    $cntKey = ['' => 'all', 'pending' => 'pending', 'confirmed' => 'confirmed',
               'shipping' => 'shipping', 'delivered' => 'delivered', 'cancelled' => 'cancelled'];
    foreach ($tabs as $val => $tab):
        $cnt    = $summary[$cntKey[$val]] ?? 0;
        $active = ($status === $val) ? 'active' : '';
        $href   = '?' . http_build_query(array_merge($_GET, ['status' => $val, 'page' => 1]));
    ?>
    <a href="<?= $href ?>" class="adm-order-tab <?= $active ?> <?= $tab['cls'] ?>">
        <?= $tab['label'] ?>
        <span class="tab-cnt"><?= $cnt ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Search & filter toolbar ── -->
<div class="adm-card" style="margin-bottom:20px;">
    <div class="adm-toolbar">
        <form method="GET" class="adm-search-form">
            <?php if ($status): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <?php endif; ?>
            <div class="adm-search-box">
                <i class="fa-solid fa-search"></i>
                <input type="text" name="search" placeholder="Tìm mã đơn, tên, email KH..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="adm-btn adm-btn-primary">Tìm kiếm</button>
            <?php if ($search || $status): ?>
                <a href="orders.php" class="adm-btn adm-btn-outline">Xóa lọc</a>
            <?php endif; ?>
        </form>
        <span style="color:var(--gray-400); font-size:13px;"><?= number_format($total_rows) ?> đơn hàng</span>
    </div>
</div>

<!-- ── Orders table ── -->
<div class="adm-card">
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Sản phẩm</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th style="text-align:center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:48px; color:var(--gray-400);">
                        <i class="fa-solid fa-inbox" style="font-size:32px; margin-bottom:10px; display:block;"></i>
                        Không có đơn hàng nào
                    </td>
                </tr>
            <?php else: ?>
                <?php
                $statusCfg = [
                    'pending'   => ['label' => 'Chờ xử lý',   'class' => 'badge-orange'],
                    'confirmed' => ['label' => 'Đã xác nhận', 'class' => 'badge-blue'],
                    'shipping'  => ['label' => 'Đang giao',   'class' => 'badge-sky'],
                    'delivered' => ['label' => 'Đã giao',     'class' => 'badge-green'],
                    'cancelled' => ['label' => 'Đã hủy',      'class' => 'badge-red'],
                ];
                $paymentCfg = [
                    'cod'           => ['label' => 'COD',          'class' => 'badge-gray'],
                    'bank_transfer' => ['label' => 'Chuyển khoản', 'class' => 'badge-blue'],
                    'momo'          => ['label' => 'MoMo',         'class' => 'badge-pink'],
                ];
                foreach ($orders as $o):
                    $sc = $statusCfg[$o['status']]          ?? ['label' => $o['status'],          'class' => 'badge-gray'];
                    $pc = $paymentCfg[$o['payment_method']] ?? ['label' => $o['payment_method'],  'class' => 'badge-gray'];
                ?>
                <tr>
                    <td>
                        <span class="adm-order-code"><?= htmlspecialchars($o['order_code']) ?></span>
                    </td>
                    <td>
                        <p class="adm-cell-main"><?= htmlspecialchars($o['full_name']) ?></p>
                        <p class="adm-cell-sub"><?= htmlspecialchars($o['email']) ?></p>
                    </td>
                    <td class="adm-cell-sub"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td><?= (int)$o['item_count'] ?> sp</td>
                    <td><strong><?= number_format((int)$o['total'], 0, ',', '.') ?>₫</strong></td>
                    <td><span class="adm-badge <?= $pc['class'] ?>"><?= $pc['label'] ?></span></td>
                    <td><span class="adm-badge <?= $sc['class'] ?>"><?= $sc['label'] ?></span></td>
                    <td style="text-align:center;">
                        <div class="adm-action-group">
                            <button class="adm-action-btn" title="Chi tiết" onclick="openOrderModal(<?= $o['id'] ?>)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="adm-action-btn" title="Cập nhật trạng thái" onclick="openStatusModal(<?= $o['id'] ?>, '<?= $o['status'] ?>')">
                                <i class="fa-solid fa-pen-to-square"></i>
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
        <span class="adm-pagination-info">
            Trang <?= $page_num ?> / <?= $total_pages ?>
        </span>
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

<!-- ── Order Detail Modal ── -->
<div class="adm-modal-overlay" id="orderModal">
    <div class="adm-modal adm-modal-lg">
        <div class="adm-modal-header">
            <h3 id="orderModalTitle">Chi tiết đơn hàng</h3>
            <button class="adm-modal-close" onclick="closeModal('orderModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body" id="orderModalBody">
            <div class="adm-loading">
                <i class="fa-solid fa-spinner fa-spin"></i> Đang tải...
            </div>
        </div>
    </div>
</div>

<!-- ── Update Status Modal ── -->
<div class="adm-modal-overlay" id="statusModal">
    <div class="adm-modal" style="max-width:440px;">
        <div class="adm-modal-header">
            <h3>Cập nhật trạng thái đơn</h3>
            <button class="adm-modal-close" onclick="closeModal('statusModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="adm-modal-body">
            <form id="statusForm">
                <input type="hidden" id="statusOrderId">
                <div class="adm-form-group">
                    <label class="adm-label">Trạng thái mới</label>
                    <select id="statusSelect" class="adm-select">
                        <option value="pending">Chờ xử lý</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="shipping">Đang giao hàng</option>
                        <option value="delivered">Đã giao thành công</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                </div>
                <div class="adm-form-group">
                    <label class="adm-label">Ghi chú (tuỳ chọn)</label>
                    <input type="text" id="statusNote" class="adm-input" placeholder="Lý do cập nhật...">
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end;">
                    <button type="button" class="adm-btn adm-btn-outline" onclick="closeModal('statusModal')">Hủy</button>
                    <button type="submit" class="adm-btn adm-btn-primary">Lưu thay đổi</button>
                </div>
            </form>
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

.adm-form-group { margin-bottom: 16px; }
.adm-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: var(--gray-600); }
.adm-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--gray-300);
    border-radius: 6px;
    font-size: 13px;
    font-family: inherit;
    outline: none;
    box-sizing: border-box;
}
.adm-input:focus { border-color: var(--black); box-shadow: 0 0 0 3px rgba(20,23,24,0.05); }

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

/* ── Orders page specific ── */
.adm-order-tabs {
    display: flex;
    gap: 4px;
    margin-bottom: 20px;
    background: var(--white);
    border: 1px solid var(--gray-300);
    border-radius: 10px;
    padding: 4px;
    overflow-x: auto;
}
.adm-order-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 500;
    color: var(--gray-400);
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s;
}
.adm-order-tab:hover { background: var(--gray-100); color: var(--black); }
.adm-order-tab.active { background: var(--black); color: #fff; }
.adm-order-tab.active .tab-cnt { background: rgba(255,255,255,0.2); }
.tab-cnt {
    background: var(--gray-200);
    color: var(--gray-500);
    border-radius: 99px;
    padding: 1px 7px;
    font-size: 11px;
    font-weight: 700;
}
.adm-order-code {
    font-weight: 700;
    color: var(--blue);
    font-size: 13px;
}
</style>

<script>
// ── Order detail modal ──
async function openOrderModal(id) {
    document.getElementById('orderModalTitle').textContent = 'Đang tải...';
    document.getElementById('orderModalBody').innerHTML = '<div class="adm-loading"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</div>';
    openModal('orderModal');

    try {
        const res  = await fetch(`../controllers/OrderController.php?action=detail&id=${id}`);
        const data = await res.json();
        if (!data.success) throw new Error();

        const o = data.order, items = data.items;
        document.getElementById('orderModalTitle').textContent = 'Đơn hàng ' + o.order_code;

        const statusMap = { pending:'Chờ xử lý', confirmed:'Đã xác nhận', shipping:'Đang giao', delivered:'Đã giao', cancelled:'Đã hủy' };
        const payMap    = { cod:'COD', bank_transfer:'Chuyển khoản', momo:'MoMo' };

        let rows = items.map(i => {
            const th = i.thumbnail?.startsWith('http') ? i.thumbnail : '../assets/images/' + (i.thumbnail || 'placeholder.jpg');
            return `<tr>
                <td><img src="${th}" style="width:44px;height:44px;border-radius:6px;object-fit:contain;background:#f3f5f7;" onerror="this.src='../assets/images/placeholder.jpg'"></td>
                <td>
                    <p style="font-weight:600;font-size:13px;">${i.product_name}</p>
                    <p style="font-size:12px;color:var(--gray-400);">${i.variant || ''} · SL: ${i.quantity}</p>
                </td>
                <td style="font-weight:600;">${Number(i.price).toLocaleString('vi-VN')}₫</td>
                <td style="font-weight:700;">${Number(i.subtotal).toLocaleString('vi-VN')}₫</td>
            </tr>`;
        }).join('');

        document.getElementById('orderModalBody').innerHTML = `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
            <div class="adm-info-block"><span>Khách hàng</span><strong>${o.full_name}</strong></div>
            <div class="adm-info-block"><span>Email</span><strong>${o.email}</strong></div>
            <div class="adm-info-block"><span>SĐT</span><strong>${o.phone}</strong></div>
            <div class="adm-info-block"><span>Ngày đặt</span><strong>${new Date(o.created_at).toLocaleDateString('vi-VN')}</strong></div>
            <div class="adm-info-block" style="grid-column:1/-1;"><span>Địa chỉ</span><strong>${o.address}, ${o.city || ''} ${o.province || ''}</strong></div>
            <div class="adm-info-block"><span>Thanh toán</span><strong>${payMap[o.payment_method] || o.payment_method}</strong></div>
            <div class="adm-info-block"><span>Trạng thái</span><strong>${statusMap[o.status] || o.status}</strong></div>
        </div>
        <table class="adm-table" style="margin-bottom:16px;">
            <thead><tr><th>Ảnh</th><th>Sản phẩm</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead>
            <tbody>${rows}</tbody>
        </table>
        <div style="text-align:right; font-size:16px;">
            <span style="color:var(--gray-400);">Tổng cộng: </span>
            <strong style="font-size:18px;">${Number(o.total).toLocaleString('vi-VN')}₫</strong>
        </div>`;
    } catch {
        document.getElementById('orderModalBody').innerHTML = '<p style="text-align:center;color:var(--red);">Không tải được dữ liệu.</p>';
    }
}

// ── Status modal ──
function openStatusModal(id, currentStatus) {
    document.getElementById('statusOrderId').value = id;
    document.getElementById('statusSelect').value   = currentStatus;
    document.getElementById('statusNote').value     = '';
    openModal('statusModal');
}

document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id     = document.getElementById('statusOrderId').value;
    const status = document.getElementById('statusSelect').value;
    const note   = document.getElementById('statusNote').value;

    const fd = new FormData();
    fd.append('order_id', id);
    fd.append('status', status);
    fd.append('note', note);

    try {
        const res  = await fetch('../controllers/OrderController.php?action=update_status', { method:'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            closeModal('statusModal');
            location.reload();
        } else {
            alert('Cập nhật thất bại: ' + (data.message || ''));
        }
    } catch {
        alert('Lỗi kết nối!');
    }
});

// ── Modal helpers ──
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.adm-modal-overlay').forEach(el => {
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); });
});

// ── Export CSV (placeholder) ──
function exportOrders() {
    alert('Tính năng export sẽ được triển khai qua OrderController.');
}
</script>

<?php include 'layouts/admin_footer.php'; ?>
