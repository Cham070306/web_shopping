<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    header("Location: ../../user/login.php");
    exit;
}

require_once "../../config/config.php";
require_once "../../config/database.php";
require_once "../../models/Order.php";
require_once "../../models/Category.php";

$orderModel = new Order($conn);

// Handle POST actions upfront so they return JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'update_stock') {
    header('Content-Type: application/json');
    $product_id = (int)$_POST['product_id'];
    $new_stock  = (int)$_POST['stock'];
    $orderModel->updateStock($product_id, $new_stock);
    echo json_encode(['success' => true]);
    exit;
}

$categoryModel = new Category($conn);
$allCategories = $categoryModel->getAll();

$search      = trim($_GET['search'] ?? '');
$category_id = (int)($_GET['category'] ?? 0);
$page_num    = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 20;
$offset      = ($page_num - 1) * $per_page;

$products = $orderModel->getInventoryList($search, $category_id, $per_page, $offset);
$total    = $orderModel->countInventory($search, $category_id);
$pages    = max(1, ceil($total / $per_page));
$lowStock = $orderModel->getLowStockProducts(10); // Lấy danh sách sp sắp hết hàng (<=10)

$currentPage = 'inventory';
$pageTitle   = 'Quản lý tồn kho';
$breadcrumb  = 'Operations / Inventory';
$base_path   = '../';

include '../layouts/admin_header.php';

function invThumb($thumb) {
    if (!$thumb) return '../../assets/images/sofa.jpg';
    if (strpos($thumb, 'http') === 0) return htmlspecialchars($thumb);
    return '../../assets/images/' . htmlspecialchars($thumb);
}
?>

<style>
/* Filter */
.filter-bar {
    background: #fff; padding: 16px; border-radius: 12px; border: 1px solid #E8ECEF;
    display: flex; gap: 16px; margin-bottom: 24px; align-items: center;
}
.filter-input { flex: 1; min-width: 200px; padding: 10px 14px; border: 1px solid #E8ECEF; border-radius: 8px; outline: none; font-family: 'Inter', sans-serif;}
.filter-select { padding: 10px 14px; border: 1px solid #E8ECEF; border-radius: 8px; outline: none; background: #fff; font-family: 'Inter', sans-serif; }
.btn-filter { padding: 10px 24px; background: #141718; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
.btn-filter:hover { background: #343839; }

/* Alert */
.inv-alert {
    background: #FFF7ED; border: 1px solid #FFAB00; color: #B27700; padding: 16px 20px;
    border-radius: 12px; margin-bottom: 24px; display: flex; flex-direction: column; gap: 12px;
}
.inv-alert-header { display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
.inv-alert-title { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
.inv-alert-title i { color: #FFAB00; font-size: 18px; }
.low-stock-list { display: none; padding-top: 12px; border-top: 1px dashed #FFD699; }
.low-stock-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.ls-card {
    background: #fff; padding: 12px; border-radius: 8px; display: flex; gap: 12px; align-items: center;
    border: 1px solid #FFD699;
}
.ls-card img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; }
.ls-info { flex: 1; min-width: 0; }
.ls-name { font-size: 13px; font-weight: 600; color: #141718; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;}
.ls-stock { font-size: 12px; color: #FF5630; font-weight: 600; }

/* Table */
.inv-table { width: 100%; border-collapse: collapse; }
.inv-table th { text-align: left; font-size: 12px; font-weight: 600; color: #6C7275; text-transform: uppercase; padding: 16px; border-bottom: 1px solid #E8ECEF; background: #fafafa;}
.inv-table td { padding: 16px; border-bottom: 1px solid #E8ECEF; vertical-align: middle; }
.td-product { display: flex; align-items: center; gap: 12px; }
.td-product img { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid #E8ECEF; }
.td-name { font-weight: 600; font-size: 14px; color: #141718; line-height: 1.4; }
.td-sku { font-size: 12px; color: #6C7275; font-family: monospace; }
.td-cat { font-size: 13px; color: #6C7275; }

.stock-val { font-weight: 700; font-size: 14px; }
.stock-val.red { color: #FF5630; }
.stock-val.orange { color: #FFAB00; }
.stock-val.green { color: #38CB89; }

.badge-status { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.bg-green { background: #E8F9EE; color: #38CB89; }
.bg-orange { background: #FFF7ED; color: #FFAB00; }
.bg-red { background: #FFF0F0; color: #FF5630; }

/* Inline Update */
.update-wrap { display: flex; align-items: center; gap: 8px; }
.update-input {
    width: 60px; padding: 8px; border: 1px solid #E8ECEF; border-radius: 6px;
    font-size: 14px; text-align: center; outline: none; font-weight: 600; font-family: 'Inter';
}
.btn-save-inline {
    padding: 8px 12px; background: #F3F5F7; border: 1px solid #E8ECEF; border-radius: 6px;
    cursor: pointer; font-size: 12px; font-weight: 600; color: #141718; transition: 0.2s;
}
.btn-save-inline:hover { background: #141718; color: #fff; border-color: #141718; }
.btn-save-inline.loading { background: #E8ECEF; color: #6C7275; cursor: not-allowed; }

.pagination { display: flex; gap: 8px; justify-content: center; margin-top: 24px; padding-bottom: 40px; }
.page-btn {
    width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
    border: 1px solid #E8ECEF; border-radius: 8px; color: #141718; text-decoration: none; font-size: 14px;
}
.page-btn:hover { background: #F3F5F7; }
.page-btn.active { background: #141718; color: #fff; border-color: #141718; }
</style>

<div class="adm-page-header">
    <div>
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= htmlspecialchars($breadcrumb) ?></p>
    </div>
</div>

<?php if (count($lowStock) > 0): ?>
    <div class="inv-alert">
        <div class="inv-alert-header" onclick="document.getElementById('lsList').style.display = document.getElementById('lsList').style.display === 'block' ? 'none' : 'block'">
            <div class="inv-alert-title"><i class="fa-solid fa-triangle-exclamation"></i> Chú ý: Có <?= count($lowStock) ?> sản phẩm sắp hoặc đã hết hàng</div>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="low-stock-list" id="lsList">
            <div class="low-stock-grid">
                <?php foreach ($lowStock as $ls): ?>
                    <div class="ls-card">
                        <img src="<?= invThumb($ls['thumbnail']) ?>" alt="">
                        <div class="ls-info">
                            <div class="ls-name" title="<?= htmlspecialchars($ls['name']) ?>"><?= htmlspecialchars($ls['name']) ?></div>
                            <div class="ls-stock">Tồn: <?= $ls['stock'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Filter -->
<form class="filter-bar" method="GET">
    <input type="text" name="search" class="filter-input" placeholder="Tìm tên sản phẩm, SKU..." value="<?= htmlspecialchars($search) ?>">
    <select name="category" class="filter-select">
        <option value="0">Tất cả danh mục</option>
        <?php foreach ($allCategories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= $category_id === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-filter">Lọc dữ liệu</button>
</form>

<!-- Table -->
<div class="adm-card" style="padding:0; overflow: hidden;">
    <table class="inv-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Đã bán</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Cập nhật Tồn kho</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="6" style="text-align:center; padding: 40px; color:#6C7275;">Không tìm thấy sản phẩm nào.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $p):
                    $stk = (int)$p['stock'];
                    $sold = (int)($p['sold_qty'] ?? 0);
                    $stockColor = $stk <= 5 ? 'red' : ($stk <= 10 ? 'orange' : 'green');
                    if ($stk <= 0) {
                        $badge = ['Hết hàng', 'bg-red'];
                    } elseif ($stk <= 10) {
                        $badge = ['Sắp hết', 'bg-orange'];
                    } else {
                        $badge = ['Còn hàng', 'bg-green'];
                    }
                ?>
                <tr>
                    <td>
                        <div class="td-product">
                            <img src="<?= invThumb($p['thumbnail']) ?>" alt="">
                            <div>
                                <div class="td-name"><?= htmlspecialchars($p['name']) ?></div>
                                <div class="td-sku">SKU: <?= htmlspecialchars($p['sku'] ?: 'N/A') ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="td-cat"><?= htmlspecialchars($p['category_name']) ?></td>
                    <td style="font-size: 14px; font-weight: 500;"><?= $sold ?></td>
                    <td><span class="stock-val <?= $stockColor ?>" id="stock-val-<?= $p['id'] ?>"><?= $stk ?></span></td>
                    <td><span class="badge-status <?= $badge[1] ?>" id="badge-<?= $p['id'] ?>"><?= $badge[0] ?></span></td>
                    <td>
                        <div class="update-wrap">
                            <input type="number" class="update-input" id="input-<?= $p['id'] ?>" value="<?= $stk ?>" min="0">
                            <button class="btn-save-inline" onclick="saveStock(<?= $p['id'] ?>, this)">Lưu</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_id ?>"
           class="page-btn <?= $i === $page_num ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<!-- Toast -->
<div id="toastMsg" style="position:fixed;bottom:24px;right:24px;background:#141718;color:#fff;padding:12px 24px;border-radius:8px;font-size:14px;display:none;z-index:1000;box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>

<?php include '../layouts/admin_footer.php'; ?>

<script>
function showToast(msg) {
    const t = document.getElementById('toastMsg');
    t.innerHTML = '<i class="fa-solid fa-check-circle" style="color:#38CB89; margin-right:8px;"></i>' + msg;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 3000);
}

async function saveStock(pid, btn) {
    const input = document.getElementById('input-' + pid);
    let val = parseInt(input.value) || 0;
    if (val < 0) { val = 0; input.value = 0; }

    btn.classList.add('loading');
    btn.textContent = '...';
    btn.disabled = true;

    try {
        const fd = new FormData();
        fd.append('product_id', pid);
        fd.append('stock', val);

        const res = await fetch('?action=update_stock', { method: 'POST', body: fd });
        const data = await res.json();
        
        if (data.success) {
            // Update UI dynamically
            const stockValEl = document.getElementById('stock-val-' + pid);
            stockValEl.textContent = val;
            
            // Color logic
            stockValEl.className = 'stock-val ' + (val <= 5 ? 'red' : (val <= 10 ? 'orange' : 'green'));
            
            // Badge logic
            const badge = document.getElementById('badge-' + pid);
            if (val <= 0) {
                badge.className = 'badge-status bg-red';
                badge.textContent = 'Hết hàng';
            } else if (val <= 10) {
                badge.className = 'badge-status bg-orange';
                badge.textContent = 'Sắp hết';
            } else {
                badge.className = 'badge-status bg-green';
                badge.textContent = 'Còn hàng';
            }
            
            showToast('Cập nhật tồn kho thành công!');
        } else {
            alert('Lỗi cập nhật');
        }
    } catch (e) {
        alert('Lỗi mạng kết nối');
    } finally {
        btn.classList.remove('loading');
        btn.textContent = 'Lưu';
        btn.disabled = false;
    }
}
</script>
