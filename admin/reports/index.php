<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../../config/database.php";
require_once "../../controllers/ReportController.php";

$report = new ReportController($conn);

$stats = $report->getDashboardStats();

$topCategories = method_exists($report, 'getTopSellingCategories')
    ? $report->getTopSellingCategories()
    : [];

$recentOrders = method_exists($report, 'getRecentOrders')
    ? $report->getRecentOrders()
    : [];

$orderStatusStats = method_exists($report, 'getOrderStatusStats')
    ? $report->getOrderStatusStats()
    : [];

$currentPage = 'reports';
$pageTitle = 'Reports Dashboard';
$breadcrumb = 'System / Reports';
$base_path = '../';
?>

<?php include '../layouts/admin_header.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.reports-page {
    padding: 4px;
    max-width: 100%;
    overflow-x: hidden;
}

.report-hero {
    background: #fff;
    border-radius: 22px;
    padding: 28px 30px;
    margin-bottom: 24px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.06);
}

.report-hero span {
    letter-spacing: 6px;
    font-size: 12px;
    color: #94a3b8;
    font-weight: 700;
}

.report-hero h1 {
    margin: 8px 0 6px;
    font-size: 34px;
    color: #111827;
}

.report-hero p {
    margin: 0;
    color: #64748b;
    font-size: 15px;
}

.report-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
    margin-bottom: 18px;
}

.report-card {
    background: #fff;
    border-radius: 22px;
    padding: 24px;
    box-shadow: 0 14px 40px rgba(15, 23, 42, 0.07);
    min-width: 0;
}

.report-card.stat {
    min-height: 120px;
}

.report-card.stat p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.report-card.stat h2 {
    margin: 16px 0 0;
    font-size: 30px;
    color: #0f172a;
    word-break: break-word;
}

.report-main-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(320px, 1fr);
    gap: 18px;
    margin-bottom: 18px;
}

.card-head {
    margin-bottom: 18px;
}

.card-head h3 {
    margin: 0;
    font-size: 22px;
    color: #111827;
}

.card-head p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 13px;
}

.chart-large,
.chart-small {
    min-height: 420px;
}

.chart-box {
    position: relative;
    width: 100%;
    height: 330px;
}

.chart-box canvas {
    width: 100% !important;
    height: 100% !important;
}

.report-bottom-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 18px;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
}

.report-order-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 680px;
}

.report-order-table th,
.report-order-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    font-size: 14px;
}

.report-order-table th {
    color: #64748b;
    font-size: 12px;
    text-transform: uppercase;
    background: #f8fafc;
}

.status-pill {
    display: inline-flex;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.status-pending { background: #fff7ed; color: #f59e0b; }
.status-processing { background: #eef2ff; color: #6366f1; }
.status-confirmed { background: #eef2ff; color: #6366f1; }
.status-shipping,
.status-shipped { background: #ecfeff; color: #06b6d4; }
.status-delivered { background: #ecfdf5; color: #10b981; }
.status-cancelled { background: #fef2f2; color: #ef4444; }

.empty-text {
    text-align: center;
    color: #94a3b8;
    padding: 24px !important;
}

@media (max-width: 1200px) {
    .report-stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-main-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .report-hero {
        padding: 22px;
    }

    .report-hero h1 {
        font-size: 26px;
    }

    .report-stats-grid {
        grid-template-columns: 1fr;
    }

    .report-card {
        padding: 18px;
    }
}
</style>

<div class="reports-page">

    <div class="report-hero">
        <span>ADMIN PANEL</span>
        <h1>Reports Dashboard</h1>
        <p>Revenue charts, order status summaries and business performance.</p>
    </div>

    <div class="report-stats-grid">
        <div class="report-card stat">
            <p>Total Revenue</p>
            <h2><?= number_format($stats['revenue'] ?? 0, 0, ',', '.') ?> đ</h2>
        </div>

        <div class="report-card stat">
            <p>Total Orders</p>
            <h2><?= number_format($stats['orders'] ?? 0) ?></h2>
        </div>

        <div class="report-card stat">
            <p>Total Customers</p>
            <h2><?= number_format($stats['customers'] ?? 0) ?></h2>
        </div>

        <div class="report-card stat">
            <p>Total Products</p>
            <h2><?= number_format($stats['products'] ?? 0) ?></h2>
        </div>
    </div>

    <div class="report-main-grid">
        <div class="report-card chart-large">
            <div class="card-head">
                <h3>Top Selling Categories</h3>
                <p>Best performing categories based on sold quantity.</p>
            </div>

            <div class="chart-box">
                <canvas id="topCategoriesChart"></canvas>
            </div>
        </div>

        <div class="report-card chart-small">
            <div class="card-head">
                <h3>Order Status</h3>
                <p>Current order mix.</p>
            </div>

            <div class="chart-box">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>

   

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryLabels = <?= json_encode(array_column($topCategories, 'name')) ?>;
    const categoryData = <?= json_encode(array_map('intval', array_column($topCategories, 'total_sold'))) ?>;

    const orderStatusLabels = <?= json_encode(array_column($orderStatusStats, 'status')) ?>;
    const orderStatusData = <?= json_encode(array_map('intval', array_column($orderStatusStats, 'total'))) ?>;

    const categoryCanvas = document.getElementById('topCategoriesChart');
    const statusCanvas = document.getElementById('orderStatusChart');

    const chartColors = [
        '#111827',
        '#6366F1',
        '#14B8A6',
        '#F59E0B',
        '#EC4899',
        '#3B82F6',
        '#8B5CF6',
        '#22C55E',
        '#F97316',
        '#64748B'
    ];

    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'bar',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryData,
                    backgroundColor: chartColors,
                    borderRadius: 12,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sold: ' + context.raw;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        grid: {
                            color: '#eef2f7'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            maxRotation: 0,
                            minRotation: 0
                        }
                    }
                }
            }
        });
    }

    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: orderStatusLabels,
                datasets: [{
                    data: orderStatusData,
                    backgroundColor: chartColors,
                    borderWidth: 4,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    }
});
</script>

<?php include '../layouts/admin_footer.php'; ?>