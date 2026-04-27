<?php
session_start();

$_user = $_SESSION['user'] ?? [];
if (
    empty($_user) ||
    ($_user['role'] ?? '') !== 'admin' ||
    !str_ends_with($_user['email'] ?? '', '@3legant.com')
) {
    header("Location: ../user/login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';

$currentPage = 'dashboard';
$pageTitle   = 'Dashboard';
$breadcrumb  = 'Overview / Dashboard';
$base_path   = '';

$orderModel = new Order($conn);

$stats = $orderModel->getAdminDashboardStats();
$recentOrders = $orderModel->getRecentOrders(5);
$lowStockCount = count($orderModel->getLowStockProducts(10));

if (!function_exists('formatVND')) {
    function formatVND($price) {
        return number_format((float)$price, 0, ',', '.') . ' đ';
    }
}

$statusLabels = [
    'pending'   => ['Pending', '#FFAB00', '#FFF7ED'],
    'confirmed' => ['Confirmed', '#2196F3', '#E3F2FD'],
    'shipping'  => ['Shipping', '#9C27B0', '#F3E5F5'],
    'delivered' => ['Delivered', '#38CB89', '#E8F9EE'],
    'cancelled' => ['Cancelled', '#FF5630', '#FFF0F0'],
];

/* =========================
   REVENUE CHART - REAL DATA
========================= */
$range = $_GET['range'] ?? '30';

$allowedRanges = [
    '7'  => 7,
    '30' => 30,
    '90' => 90
];

$days = $allowedRanges[$range] ?? 30;

$dailyRevenueMap = [];

for ($i = $days - 1; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dailyRevenueMap[$date] = 0;
}

$sqlDailyRevenue = "
    SELECT 
        DATE(created_at) AS order_date,
        SUM(total) AS revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL " . ($days - 1) . " DAY)
      AND status IN ('confirmed', 'shipping', 'delivered')
    GROUP BY DATE(created_at)
    ORDER BY order_date ASC
";

$resultDaily = $conn->query($sqlDailyRevenue);
if ($resultDaily) {
    while ($row = $resultDaily->fetch_assoc()) {
        $dailyRevenueMap[$row['order_date']] = (float)$row['revenue'];
    }
}

$dailyRevenueLabels = [];
$dailyRevenueData = [];

foreach ($dailyRevenueMap as $date => $value) {
    $dailyRevenueLabels[] = date('d-m', strtotime($date));
    $dailyRevenueData[] = $value;
}

$rangeText = $days == 7 ? 'last 7 days' : ($days == 90 ? 'last 3 months' : 'last 30 days');

/* =========================
   TOP PRODUCTS - REAL DATA
========================= */
$topProducts = [];

$sqlTopProducts = "
    SELECT 
        p.id,
        p.name,
        c.name AS category_name,
        SUM(oi.quantity) AS total_sold,
        SUM(oi.quantity * oi.price) AS total_revenue,
        COALESCE(NULLIF(p.sale_price, 0), p.price) AS display_price
    FROM order_items oi
    INNER JOIN orders o 
        ON o.id = oi.order_id
    INNER JOIN products p 
        ON p.id = oi.product_id
    LEFT JOIN categories c 
        ON c.id = p.category_id
    WHERE o.status IN ('confirmed', 'shipping', 'delivered')
    GROUP BY p.id, p.name, c.name, p.sale_price, p.price
    HAVING total_sold > 0
    ORDER BY total_sold DESC, total_revenue DESC
    LIMIT 4
";

$resultTopProducts = $conn->query($sqlTopProducts);
if ($resultTopProducts) {
    while ($row = $resultTopProducts->fetch_assoc()) {
        $topProducts[] = $row;
    }
}

include 'layouts/admin_header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.dashboard-grid-2 {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr);
    gap: 24px;
    margin-bottom: 30px;
}

.dashboard-chart-card,
.dashboard-top-card {
    background: #fff;
    border-radius: 24px;
    padding: 28px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
    min-width: 0;
}

.chart-header,
.top-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}

.chart-header h3,
.top-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.chart-header p {
    color: #64748b;
    margin: 6px 0 0;
    font-size: 15px;
}

.chart-select {
    border: 1px solid #dbe3ef;
    border-radius: 18px;
    padding: 12px 18px;
    font-size: 15px;
    color: #1f2937;
    background: #fff;
    outline: none;
    cursor: pointer;
}

.revenue-chart-box {
    position: relative;
    height: 360px;
    width: 100%;
    padding-top: 10px;
}

.top-subtitle {
    color: #94a3b8;
    font-weight: 600;
}
.top-product-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.top-product-item {
    display: grid;
    grid-template-columns: 50px minmax(0, 1fr) auto;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid #e5eaf3;
    border-radius: 16px;
    min-height: 82px;
}

.top-rank {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #1f2937;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
}


.top-info {
    min-width: 0;
}

.top-name {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.top-meta {
    margin-top: 2px;
    color: #94a3b8;
    font-size: 12px;
    line-height: 1.4;
}

.top-price {
    font-size: 15px;
    font-weight: 800;
    color: #1f2937;
    white-space: nowrap;
}

.empty-dashboard-box {
    padding: 40px 20px;
    text-align: center;
    color: #94a3b8;
    border: 1px dashed #dbe3ef;
    border-radius: 20px;
}

@media (max-width: 1200px) {
    .dashboard-grid-2 {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .chart-header,
    .top-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .dashboard-chart-card,
    .dashboard-top-card {
        padding: 20px;
    }

    .top-product-item {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .top-price {
        grid-column: 2;
    }
}
</style>

<div class="adm-page-header">
    <div>
        <h1>Dashboard Overview</h1>
        <p>Business overview statistics</p>
    </div>
</div>

<div class="adm-stats-grid">
    <div class="adm-stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value"><?= formatVND($stats['revenue'] ?? 0) ?></div>
        <div class="stat-note"><span class="badge badge-green">System revenue</span></div>
    </div>

    <div class="adm-stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
        <div class="stat-note">
            <span class="badge badge-green"><?= number_format($stats['pending_orders'] ?? 0) ?> pending</span>
            Needs processing
        </div>
    </div>

    <div class="adm-stat-card">
        <div class="stat-label">Active Customers</div>
        <div class="stat-value"><?= number_format($stats['total_customers'] ?? 0) ?></div>
        <div class="stat-note"><span class="badge badge-green">Customers</span></div>
    </div>

    <div class="adm-stat-card">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value" style="color: <?= $lowStockCount > 0 ? 'var(--red)' : '#38CB89' ?>;">
            <?= $lowStockCount ?>
        </div>
        <div class="stat-note" style="color: <?= $lowStockCount > 0 ? 'var(--red)' : '#6C7275' ?>;">
            <?= $lowStockCount > 0 ? 'Needs attention immediately' : 'Inventory is stable' ?>
        </div>
    </div>
</div>

<div class="dashboard-grid-2">
    <div class="dashboard-chart-card">
        <div class="chart-header">
            <div>
                <h3>Daily revenue</h3>
                <p>Using real order data from the <?= htmlspecialchars($rangeText) ?>.</p>
            </div>

            <form method="GET">
                <select class="chart-select" name="range" onchange="this.form.submit()">
                    <option value="7" <?= $range == '7' ? 'selected' : '' ?>>Last 7 days</option>
                    <option value="30" <?= $range == '30' ? 'selected' : '' ?>>Last 30 days</option>
                    <option value="90" <?= $range == '90' ? 'selected' : '' ?>>Last 3 months</option>
                </select>
            </form>
        </div>

        <div class="revenue-chart-box">
            <canvas id="dailyRevenueChart"></canvas>
        </div>
    </div>

    <div class="dashboard-top-card">
        <div class="top-header">
            <h3>Top products</h3>
            <span class="top-subtitle">Bestselling</span>
        </div>

        <?php if (!empty($topProducts)): ?>
            <div class="top-product-list">
                <?php foreach ($topProducts as $index => $product): ?>
                    <div class="top-product-item">
                        <div class="top-rank"><?= $index + 1 ?></div>

                        <div class="top-info">
                            <div class="top-name">
                                <?= htmlspecialchars($product['name'] ?? 'Product') ?>
                            </div>
                            <div class="top-meta">
                                <?= htmlspecialchars($product['category_name'] ?? 'No category') ?>
                                • Sold <?= number_format($product['total_sold'] ?? 0) ?>
                            </div>
                        </div>

                        <div class="top-price">
                            <?= formatVND($product['display_price'] ?? 0) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-dashboard-box">
                No bestselling products yet.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="adm-card" style="margin-bottom: 30px;">
    <div style="padding: 20px; display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid var(--gray-300);">
        <h3 style="font-size: 16px; font-weight:600;">Recent Orders</h3>
        <a href="orders/index.php" style="font-size: 13px; color: var(--blue); text-decoration:none; font-weight:500;">View All</a>
    </div>

    <table class="adm-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($recentOrders)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px; color: #6C7275;">
                        No recent orders.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($recentOrders as $ord): ?>
                    <?php $currConf = $statusLabels[$ord['status']] ?? ['Unknown', '#000', '#eee']; ?>

                    <tr>
                        <td style="color: var(--gray-400); font-family: monospace;">
                            <a href="orders/detail.php?id=<?= $ord['id'] ?>" style="color:inherit; text-decoration:none;">
                                <?= htmlspecialchars($ord['order_code']) ?>
                            </a>
                        </td>

                        <td style="font-weight: 500;">
                            <?= htmlspecialchars($ord['full_name']) ?>
                        </td>

                        <td style="color: var(--gray-400); font-size:13px;">
                            <?= date('d M, Y', strtotime($ord['created_at'])) ?>
                        </td>

                        <td style="font-weight: 500;">
                            <?= formatVND($ord['total']) ?>
                        </td>

                        <td>
                            <span class="badge" style="background:<?= $currConf[2] ?>; color:<?= $currConf[1] ?>; padding: 4px 8px; border-radius: 4px;">
                                <?= $currConf[0] ?>
                            </span>
                        </td>

                        <td>
                            <a href="orders/detail.php?id=<?= $ord['id'] ?>" style="color: var(--gray-400);">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const revenueLabels = <?= json_encode($dailyRevenueLabels) ?>;
    const revenueData = <?= json_encode($dailyRevenueData) ?>;
    const chartRange = '<?= $range ?>';

    const canvas = document.getElementById('dailyRevenueChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // gradient đẹp hơn
    const gradient = ctx.createLinearGradient(0, 0, 0, 360);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.28)');
    gradient.addColorStop(0.45, 'rgba(79, 70, 229, 0.12)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenueLabels,
            datasets: [{
                label: 'Revenue',
                data: revenueData,

                borderColor: '#0b0931',
                backgroundColor: gradient,
                fill: true,

                tension: 0.45,
                borderWidth: 3,

                // ẩn chấm mặc định → nhìn clean hơn
                pointRadius: 0,

                // hover mới hiện chấm
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#110e3b',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3,

                cubicInterpolationMode: 'monotone'
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            interaction: {
                mode: 'index',
                intersect: false
            },

            plugins: {
                legend: {
                    display: false
                },

                tooltip: {
                    backgroundColor: '#111827',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    padding: 14,
                    cornerRadius: 14,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: ' + 
                                new Intl.NumberFormat('vi-VN').format(context.raw) + 
                                ' đ';
                        }
                    }
                }
            },

            scales: {
                y: {
                    beginAtZero: true,
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        padding: 10,
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    },
                    grid: {
                        color: '#eef2f7',
                        drawTicks: false
                    }
                },

                x: {
                    border: {
                        display: false
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6b7280',
                        maxRotation: chartRange === '90' ? 0 : 45,
                        minRotation: chartRange === '90' ? 0 : 45,
                        autoSkip: true,
                        maxTicksLimit: chartRange === '90' ? 8 : 14
                    }
                }
            }
        }
    });
});
</script>

<?php include 'layouts/admin_footer.php'; ?>