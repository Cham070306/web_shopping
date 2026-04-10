<?php
session_start();
$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin' || !str_ends_with($_user['email'] ?? '', '@3legant.com')) {
    header("Location: ../user/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>3elegant | Admin UI Sample</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <style>
    * { font-family: 'Inter', sans-serif; }
    body {
      background:
        radial-gradient(circle at top left, rgba(99,102,241,0.10), transparent 28%),
        radial-gradient(circle at right top, rgba(16,185,129,0.08), transparent 24%),
        linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
    }
    .glass {
      background: rgba(255,255,255,0.78);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .sidebar-link {
      transition: all .2s ease;
      border: 1px solid transparent;
    }
    .sidebar-link.active {
      background: linear-gradient(90deg, #111827 0%, #1f2937 100%);
      color: white;
      box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);
    }
    .sidebar-link.active i { color: white !important; }
    .sidebar-link:hover:not(.active) {
      transform: translateX(3px);
      background: rgba(255,255,255,0.75);
      border-color: rgba(148,163,184,.25);
    }
    .card-hover { transition: transform .25s ease, box-shadow .25s ease; }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 18px 40px rgba(15,23,42,.08);
    }
    .section { display: none; animation: showUp .25s ease; }
    .section.active { display: block; }
    @keyframes showUp {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .status-pill {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      padding: .35rem .7rem;
      border-radius: 999px;
      font-size: 12px;
      font-weight: 600;
    }
    .dot { width: 7px; height: 7px; border-radius: 999px; display: inline-block; }
    .table-wrap::-webkit-scrollbar,
    .scrollbar-thin::-webkit-scrollbar { height: 8px; width: 8px; }
    .table-wrap::-webkit-scrollbar-thumb,
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
    .sidebar-mobile-overlay { background: rgba(15, 23, 42, .45); }
  </style>
</head>
<body class="min-h-screen text-slate-800">
  <div class="flex min-h-screen overflow-hidden">
    <div id="mobileOverlay" class="sidebar-mobile-overlay fixed inset-0 z-30 hidden lg:hidden" onclick="closeMobileSidebar()"></div>

    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-[290px] bg-slate-950 text-white p-5 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-2xl lg:shadow-none">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-indigo-500 to-slate-900 flex items-center justify-center shadow-lg">
            <span class="font-extrabold text-lg">3</span>
          </div>
          <div>
            <h1 class="text-xl font-bold">3elegant</h1>
            <p class="text-xs text-slate-400">Admin UI sample for team</p>
          </div>
        </div>
        <button class="lg:hidden text-slate-400" onclick="closeMobileSidebar()"><i class="fas fa-xmark text-lg"></i></button>
      </div>

      <div class="rounded-2xl bg-white/5 border border-white/10 p-4 mb-6">
        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-2">Workspace</p>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-semibold">Premium Store Admin</p>
            <p class="text-xs text-slate-400">Demo data + localStorage</p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] bg-emerald-500/15 text-emerald-300">Online</span>
        </div>
      </div>

      <nav class="space-y-5 text-sm">
        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">Overview</p>
          <button onclick="setActiveSection('dashboard')" data-section="dashboard" class="sidebar-link active w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-chart-pie text-slate-400 w-5"></i><span>Dashboard</span>
          </button>
        </div>

        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">Catalog</p>
          <button onclick="setActiveSection('categories')" data-section="categories" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3 mb-2">
            <i class="fa-solid fa-layer-group text-slate-400 w-5"></i><span>Categories</span>
          </button>
          <button onclick="setActiveSection('products')" data-section="products" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3 mb-2">
            <i class="fa-solid fa-box-open text-slate-400 w-5"></i><span>Products</span>
          </button>
          <button onclick="openProductDetail(products[0]?.id || 1)" data-section="product-detail" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-eye text-slate-400 w-5"></i><span>Product Detail</span>
          </button>
        </div>

        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">Sales</p>
          <button onclick="setActiveSection('orders')" data-section="orders" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-bag-shopping text-slate-400 w-5"></i><span>Orders</span>
          </button>
        </div>

        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">Operations</p>
          <button onclick="setActiveSection('inventory')" data-section="inventory" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3 mb-2">
            <i class="fa-solid fa-warehouse text-slate-400 w-5"></i><span>Inventory</span>
          </button>
          <button onclick="setActiveSection('customers')" data-section="customers" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-users text-slate-400 w-5"></i><span>Customers</span>
          </button>
        </div>

        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">Growth</p>
          <button onclick="setActiveSection('marketing')" data-section="marketing" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3 mb-2">
            <i class="fa-solid fa-tags text-slate-400 w-5"></i><span>Coupons</span>
          </button>
          <button onclick="setActiveSection('reports')" data-section="reports" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-chart-column text-slate-400 w-5"></i><span>Reports</span>
          </button>
        </div>

        <div>
          <p class="px-4 mb-2 text-[11px] uppercase tracking-[0.25em] text-slate-500">System</p>
          <button onclick="setActiveSection('settings')" data-section="settings" class="sidebar-link w-full rounded-2xl px-4 py-3 text-left flex items-center gap-3">
            <i class="fa-solid fa-gear text-slate-400 w-5"></i><span>Settings</span>
          </button>
        </div>
      </nav>

      <div class="mt-8 rounded-2xl bg-gradient-to-br from-indigo-500/15 to-white/5 border border-white/10 p-4">
        <div class="flex items-center gap-3">
          <div class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center font-bold">AD</div>
          <div class="min-w-0">
            <p class="font-semibold truncate">Admin Nguyễn</p>
            <p class="text-xs text-slate-400 truncate">admin@3elegant.com</p>
          </div>
        </div>
        <button onclick="resetDemoData()" class="mt-4 w-full py-2.5 rounded-xl bg-white text-slate-900 text-sm font-semibold hover:bg-slate-100 transition">Reset demo data</button>
      </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
      <header class="sticky top-0 z-20 px-4 lg:px-8 pt-4 lg:pt-6">
        <div class="glass border border-white/60 rounded-[28px] shadow-[0_10px_40px_rgba(148,163,184,.18)] px-4 lg:px-6 py-4 flex flex-col lg:flex-row lg:items-center gap-4 lg:justify-between">
          <div class="flex items-start gap-3">
            <button class="lg:hidden w-11 h-11 rounded-2xl bg-slate-100" onclick="openMobileSidebar()"><i class="fas fa-bars"></i></button>
            <div>
              <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-1">Admin panel</p>
              <h2 id="pageTitle" class="text-2xl lg:text-3xl font-extrabold tracking-tight">Dashboard</h2>
              <p id="pageDesc" class="text-sm text-slate-500 mt-1">A cleaner admin structure for splitting tasks across modules.</p>
            </div>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[220px] lg:min-w-[280px]">
              <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
              <input id="globalSearch" type="text" placeholder="Find products, orders, customers..." class="w-full rounded-2xl bg-slate-100/90 border border-slate-200 pl-11 pr-4 py-3 text-sm outline-none focus:ring-2 focus:ring-slate-300" />
            </div>
            <button onclick="handleGlobalSearch()" class="px-4 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">Quick search</button>
            <button onclick="exportCurrentSection()" class="px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold hover:bg-slate-50 transition flex items-center gap-2"><i class="fas fa-download"></i> Export</button>
          </div>
        </div>
      </header>

      <main class="flex-1 p-4 lg:p-8 space-y-6 overflow-y-auto scrollbar-thin">
        <section id="dashboard" class="section active space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            <div class="glass rounded-[28px] border border-white/70 p-5 card-hover">
              <div class="flex items-start justify-between">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fas fa-dollar-sign text-xl"></i></div>
                <span id="revenueGrowthBadge" class="status-pill bg-emerald-50 text-emerald-700"><span class="dot bg-emerald-500"></span>+0%</span>
              </div>
              <p class="text-sm text-slate-500 mt-5">Total revenue</p>
              <h3 id="totalRevenue" class="text-3xl font-extrabold mt-1">$0</h3>
              <p class="text-xs text-slate-400 mt-2">From all non-cancelled orders</p>
            </div>
            <div class="glass rounded-[28px] border border-white/70 p-5 card-hover">
              <div class="flex items-start justify-between">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-700 flex items-center justify-center"><i class="fas fa-cart-shopping text-xl"></i></div>
                <span class="status-pill bg-indigo-50 text-indigo-700"><span class="dot bg-indigo-500"></span><span id="todayOrders">0</span> today</span>
              </div>
              <p class="text-sm text-slate-500 mt-5">Total orders</p>
              <h3 id="totalOrders" class="text-3xl font-extrabold mt-1">0</h3>
              <p class="text-xs text-slate-400 mt-2">Pending: <span id="pendingOrders" class="font-bold text-amber-600">0</span></p>
            </div>
            <div class="glass rounded-[28px] border border-white/70 p-5 card-hover">
              <div class="flex items-start justify-between">
                <div class="w-14 h-14 rounded-2xl bg-sky-100 text-sky-700 flex items-center justify-center"><i class="fas fa-users text-xl"></i></div>
                <span class="status-pill bg-sky-50 text-sky-700"><span class="dot bg-sky-500"></span><span id="vipCustomersCount">0</span> VIP</span>
              </div>
              <p class="text-sm text-slate-500 mt-5">Customers</p>
              <h3 id="totalCustomers" class="text-3xl font-extrabold mt-1">0</h3>
              <p class="text-xs text-slate-400 mt-2">New this month: <span id="newCustomers" class="font-bold">0</span></p>
            </div>
            <div class="glass rounded-[28px] border border-white/70 p-5 card-hover">
              <div class="flex items-start justify-between">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fas fa-triangle-exclamation text-xl"></i></div>
                <span class="status-pill bg-rose-50 text-rose-700"><span class="dot bg-rose-500"></span>Cảnh báo</span>
              </div>
              <p class="text-sm text-slate-500 mt-5">Low stock items</p>
              <h3 id="lowStockCount" class="text-3xl font-extrabold mt-1">0</h3>
              <p class="text-xs text-slate-400 mt-2">Products with stock less than or equal to 5</p>
            </div>
          </div>

          <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
                <div>
                  <h3 class="text-lg font-bold">Daily revenue</h3>
                  <p class="text-sm text-slate-500">Using current demo order data.</p>
                </div>
                <select id="chartPeriod" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm outline-none" onchange="updateRevenueChart()">
                  <option value="7">Last 7 days</option>
                  <option value="30" selected>Last 30 days</option>
                </select>
              </div>
              <canvas id="revenueChart" height="120"></canvas>
            </div>

            <div class="space-y-6">
              <div class="glass rounded-[28px] border border-white/70 p-5">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-bold">Top products</h3>
                  <span class="text-xs text-slate-400">Bestselling</span>
                </div>
                <div id="topProductsList" class="space-y-4"></div>
              </div>
              <div class="glass rounded-[28px] border border-white/70 p-5">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-bold">Needs attention</h3>
                  <span class="text-xs text-slate-400">Realtime</span>
                </div>
                <div id="quickTasks" class="space-y-3"></div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-lg font-bold">Recent orders</h3>
                  <p class="text-sm text-slate-500">Shows the latest 5 orders for quick review.</p>
                </div>
                <button onclick="setActiveSection('orders')" class="text-sm font-semibold text-slate-700 hover:text-slate-900">View all</button>
              </div>
              <div id="recentOrdersList" class="space-y-3"></div>
            </div>
            <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <h3 class="text-lg font-bold mb-4">Inventory alerts</h3>
              <div id="stockAlerts" class="space-y-3"></div>
            </div>
          </div>
        </section>

        
        <section id="categories" class="section space-y-5">
          <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">
                <div>
                  <h3 class="text-xl font-bold">Category management</h3>
                  <p class="text-sm text-slate-500">Manage catalog groups separately so products can be assigned cleanly and the team can split work more clearly.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                  <input id="categorySearch" type="text" placeholder="Search category name" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none min-w-[220px]" oninput="renderCategories()" />
                  <button onclick="filterCategories('all')" class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-semibold">All</button>
                  <button onclick="filterCategories('active')" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm">Active</button>
                  <button onclick="filterCategories('inactive')" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm">Inactive</button>
                  <button onclick="openCategoryModal()" class="px-4 py-2.5 rounded-2xl bg-indigo-600 text-white text-sm font-semibold"><i class="fas fa-plus mr-2"></i>Add category</button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
                <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Total categories</p><p id="categoryCount" class="text-2xl font-extrabold mt-2">0</p></div>
                <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Active categories</p><p id="activeCategoryCount" class="text-2xl font-extrabold mt-2">0</p></div>
                <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Inactive categories</p><p id="inactiveCategoryCount" class="text-2xl font-extrabold mt-2">0</p></div>
                <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Mapped products</p><p id="mappedProductCount" class="text-2xl font-extrabold mt-2">0</p></div>
              </div>

              <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200/80 bg-white">
                <table class="min-w-full text-sm">
                  <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                      <th class="px-4 py-4 text-left">ID</th>
                      <th class="px-4 py-4 text-left">Category</th>
                      <th class="px-4 py-4 text-left">Description</th>
                      <th class="px-4 py-4 text-left">Products</th>
                      <th class="px-4 py-4 text-left">Status</th>
                      <th class="px-4 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="categoryTableBody"></tbody>
                </table>
              </div>
            </div>

            <div class="space-y-6">
              <div class="glass rounded-[28px] border border-white/70 p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                  <div>
                    <h4 class="text-lg font-bold">Category detail</h4>
                    <p class="text-sm text-slate-500">Preview selected category and linked products.</p>
                  </div>
                  <button onclick="openCategoryModal(currentCategoryId)" class="px-3 py-2 rounded-xl bg-slate-100 text-sm font-semibold">Edit</button>
                </div>
                <div class="space-y-4">
                  <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                      <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Selected</p>
                        <h5 id="categoryDetailName" class="text-xl font-extrabold mt-2">Tables</h5>
                      </div>
                      <div id="categoryDetailStatus"></div>
                    </div>
                    <p id="categoryDetailDesc" class="text-sm text-slate-500 mt-3">Dining, coffee, and side tables</p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-400">Products</p><p id="categoryDetailProducts" class="text-2xl font-extrabold mt-2">0</p></div>
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4"><p class="text-xs text-slate-400">Sales</p><p id="categoryDetailSales" class="text-2xl font-extrabold mt-2">0</p></div>
                  </div>
                </div>
              </div>

              <div class="glass rounded-[28px] border border-white/70 p-5">
                <div class="flex items-center justify-between gap-3 mb-4">
                  <h4 class="text-lg font-bold">Linked products</h4>
                  <span id="categoryLinkedCount" class="text-xs text-slate-400">0 items</span>
                </div>
                <div id="categoryLinkedProducts" class="space-y-3"></div>
              </div>
            </div>
          </div>
        </section>


        <section id="products" class="section space-y-5">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Product management</h3>
                <p class="text-sm text-slate-500">Includes CRUD, filters, search, pagination, and Excel export.</p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <input id="productSearch" type="text" placeholder="Search by name or category" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none min-w-[220px]" oninput="renderProducts()" />
                <button onclick="filterProducts('all')" class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-semibold">All</button>
                <button onclick="filterProducts('active')" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm">Active</button>
                <button onclick="filterProducts('lowstock')" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm">Low stock</button>
                <button onclick="openProductModal()" class="px-4 py-2.5 rounded-2xl bg-indigo-600 text-white text-sm font-semibold"><i class="fas fa-plus mr-2"></i>Add</button>
              </div>
            </div>
            <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200/80 bg-white">
              <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                  <tr>
                    <th class="px-4 py-4 text-left">ID</th>
                    <th class="px-4 py-4 text-left">Product</th>
                    <th class="px-4 py-4 text-left">Category</th>
                    <th class="px-4 py-4 text-left">Price</th>
                    <th class="px-4 py-4 text-left">Stock</th>
                    <th class="px-4 py-4 text-left">Sold</th>
                    <th class="px-4 py-4 text-left">Status</th>
                    <th class="px-4 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody id="productTableBody"></tbody>
              </table>
            </div>
            <div class="flex items-center justify-between mt-4">
              <button onclick="prevProductPage()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm"><i class="fas fa-chevron-left mr-1"></i> Previous</button>
              <p id="productPageInfo" class="text-sm text-slate-500">Page 1 / 1</p>
              <button onclick="nextProductPage()" class="px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm">Next <i class="fas fa-chevron-right ml-1"></i></button>
            </div>
          </div>
        </section>

        <section id="product-detail" class="section space-y-6">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Product detail overview</h3>
                <p class="text-sm text-slate-500">A focused screen for one product, category mapping, stock notes, and linked orders.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button onclick="setActiveSection('products')" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm font-semibold">Back to products</button>
                <button onclick="if(selectedProductId) editProduct(selectedProductId)" class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-semibold">Edit product</button>
              </div>
            </div>

            <div id="productDetailContent"></div>
          </div>
        </section>

        <section id="orders" class="section space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass rounded-[24px] border border-white/70 p-4"><p class="text-sm text-slate-500">Pending</p><h4 id="pendingCount" class="text-2xl font-extrabold mt-2">0</h4></div>
            <div class="glass rounded-[24px] border border-white/70 p-4"><p class="text-sm text-slate-500">Processing</p><h4 id="processingCount" class="text-2xl font-extrabold mt-2">0</h4></div>
            <div class="glass rounded-[24px] border border-white/70 p-4"><p class="text-sm text-slate-500">Shipped</p><h4 id="shippedCount" class="text-2xl font-extrabold mt-2">0</h4></div>
            <div class="glass rounded-[24px] border border-white/70 p-4"><p class="text-sm text-slate-500">Delivered</p><h4 id="deliveredCount" class="text-2xl font-extrabold mt-2">0</h4></div>
          </div>

          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Danh sách đơn hàng</h3>
                <p class="text-sm text-slate-500">Quick status updates with a popup.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <input id="orderSearch" type="text" placeholder="Order code / Customer name" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none min-w-[220px]" oninput="renderOrders()" />
                <select id="orderStatusFilter" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none" onchange="renderOrders()">
                  <option value="all">All trạng thái</option>
                  <option value="pending">Pending</option>
                  <option value="processing">Processing</option>
                  <option value="shipped">Shipped</option>
                  <option value="delivered">Delivered</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
            </div>
            <div class="table-wrap overflow-x-auto rounded-2xl border border-slate-200/80 bg-white">
              <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                  <tr>
                    <th class="px-4 py-4 text-left">Order code</th>
                    <th class="px-4 py-4 text-left">Customers</th>
                    <th class="px-4 py-4 text-left">Product</th>
                    <th class="px-4 py-4 text-left">Total</th>
                    <th class="px-4 py-4 text-left">Status</th>
                    <th class="px-4 py-4 text-left">Date</th>
                    <th class="px-4 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody id="ordersTableBody"></tbody>
              </table>
            </div>
          </div>
        </section>

        <section id="inventory" class="section space-y-5">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Inventory management</h3>
                <p class="text-sm text-slate-500">Restocking adds stock automatically and saves a log.</p>
              </div>
              <button onclick="openImportModal()" class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-semibold"><i class="fas fa-plus mr-2"></i>Create stock entry</button>
            </div>
            <div id="inventorySummary" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5"></div>
            <div class="space-y-3" id="inventoryList"></div>
          </div>
        </section>

        <section id="customers" class="section space-y-5">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Customers</h3>
                <p class="text-sm text-slate-500">Segment customers by rank and total spend.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <input id="customerSearch" type="text" placeholder="Search by name, email, phone" class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none min-w-[250px]" oninput="renderCustomers()" />
                <button onclick="exportCustomers()" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-sm font-semibold"><i class="fas fa-file-export mr-2"></i>Export</button>
              </div>
            </div>
            <div id="customerList" class="grid grid-cols-1 xl:grid-cols-2 gap-4"></div>
          </div>
        </section>

        <section id="marketing" class="section space-y-5">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <div>
                <h3 class="text-xl font-bold">Coupons</h3>
                <p class="text-sm text-slate-500">Create, enable, disable, and detect expiration automatically.</p>
              </div>
              <button onclick="openCouponModal()" class="px-4 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-semibold"><i class="fas fa-tag mr-2"></i>Add coupon</button>
            </div>
            <div id="couponList" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
          </div>
        </section>

        <section id="reports" class="section space-y-5">
          <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <div class="flex items-center justify-between mb-5">
                <div>
                  <h3 class="text-xl font-bold">Revenue by category</h3>
                  <p class="text-sm text-slate-500">Calculated from orders and mapped product categories.</p>
                </div>
                <button onclick="exportReport()" class="px-4 py-2.5 rounded-2xl bg-emerald-600 text-white text-sm font-semibold">Export Excel report</button>
              </div>
              <canvas id="categoryRevenueChart" height="120"></canvas>
            </div>
            <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6">
              <h3 class="text-xl font-bold mb-5">Order mix</h3>
              <canvas id="orderStatusChart" height="250"></canvas>
            </div>
          </div>
        </section>

        <section id="settings" class="section space-y-5">
          <div class="glass rounded-[28px] border border-white/70 p-5 lg:p-6 max-w-4xl">
            <h3 class="text-xl font-bold mb-1">Store settings</h3>
            <p class="text-sm text-slate-500 mb-6">Saved to localStorage so the team can demo quickly in the browser.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div><label class="text-sm font-semibold">Store name</label><input id="shopName" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 mt-2 outline-none"></div>
              <div><label class="text-sm font-semibold">Support email</label><input id="shopEmail" type="email" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 mt-2 outline-none"></div>
              <div><label class="text-sm font-semibold">Phone number</label><input id="shopPhone" type="text" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 mt-2 outline-none"></div>
              <div><label class="text-sm font-semibold">Default shipping fee ($)</label><input id="shippingFee" type="number" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 mt-2 outline-none"></div>
              <div class="md:col-span-2"><label class="text-sm font-semibold">Address</label><textarea id="shopAddress" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 mt-2 outline-none"></textarea></div>
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
              <button onclick="saveSettings()" class="px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold">Save changes</button>
              <button onclick="previewStoreInfo()" class="px-5 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold">Quick preview</button>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>

  <div id="categoryModal" class="fixed inset-0 bg-slate-950/60 z-50 hidden items-center justify-center p-4" onclick="if(event.target===this) closeCategoryModal()">
    <div class="w-full max-w-xl rounded-[28px] bg-white p-6 shadow-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 id="categoryModalTitle" class="text-2xl font-extrabold">Add category</h3>
          <p class="text-sm text-slate-500">Keep category management separate from products for easier team ownership.</p>
        </div>
        <button onclick="closeCategoryModal()" class="w-10 h-10 rounded-full bg-slate-100"><i class="fas fa-xmark"></i></button>
      </div>
      <input id="editCategoryId" type="hidden">
      <div class="space-y-4">
        <div><label class="text-sm font-semibold">Category name</label><input id="categoryName" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="Example: Lighting"></div>
        <div><label class="text-sm font-semibold">Description</label><input id="categoryDescription" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="Short note for the team"></div>
        <div><label class="text-sm font-semibold">Status</label>
          <select id="categoryStatus" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2 bg-white">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="closeCategoryModal()" class="px-5 py-3 rounded-2xl border border-slate-200 font-semibold">Cancel</button>
        <button onclick="saveCategory()" class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-semibold">Save category</button>
      </div>
    </div>
  </div>

  <div id="productModal" class="fixed inset-0 bg-slate-950/60 z-50 hidden items-center justify-center p-4" onclick="if(event.target===this) closeProductModal()">
    <div class="w-full max-w-2xl rounded-[28px] bg-white p-6 shadow-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 id="modalTitle" class="text-2xl font-extrabold">Add sản phẩm</h3>
          <p class="text-sm text-slate-500">Fill in the product information to create or edit an item.</p>
        </div>
        <button onclick="closeProductModal()" class="w-10 h-10 rounded-full bg-slate-100"><i class="fas fa-xmark"></i></button>
      </div>
      <input id="editProductId" type="hidden">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2"><label class="text-sm font-semibold">Product name</label><input id="prodName" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="Example: Nordic Gray Sofa"></div>
        <div><label class="text-sm font-semibold">Category</label><select id="prodCategory" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2 bg-white"></select></div>
        <div><label class="text-sm font-semibold">Price (USD)</label><input id="prodPrice" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="149"></div>
        <div><label class="text-sm font-semibold">Stock</label><input id="prodStock" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="20"></div>
        <div><label class="text-sm font-semibold">Sold quantity</label><input id="prodSold" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="10"></div>
        <div><label class="text-sm font-semibold">Status</label>
          <select id="prodStatus" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2 bg-white">
            <option value="active">Active</option>
            <option value="inactive">Ngừng bán</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="closeProductModal()" class="px-5 py-3 rounded-2xl border border-slate-200 font-semibold">Cancel</button>
        <button onclick="saveProduct()" class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-semibold">Save product</button>
      </div>
    </div>
  </div>

  <div id="couponModal" class="fixed inset-0 bg-slate-950/60 z-50 hidden items-center justify-center p-4" onclick="if(event.target===this) closeCouponModal()">
    <div class="w-full max-w-xl rounded-[28px] bg-white p-6 shadow-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-extrabold">Add mã giảm giá</h3>
          <p class="text-sm text-slate-500">Nhập thông tin khuyến mãi để hiển thị ở khu quản trị.</p>
        </div>
        <button onclick="closeCouponModal()" class="w-10 h-10 rounded-full bg-slate-100"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="space-y-4">
        <div><label class="text-sm font-semibold">Coupon code</label><input id="couponCode" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="WELCOME10"></div>
        <div><label class="text-sm font-semibold">Discount percent</label><input id="couponDiscount" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="10"></div>
        <div><label class="text-sm font-semibold">Expiry date</label><input id="couponExpiry" type="date" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2"></div>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="closeCouponModal()" class="px-5 py-3 rounded-2xl border border-slate-200 font-semibold">Cancel</button>
        <button onclick="addCoupon()" class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-semibold">Add mã</button>
      </div>
    </div>
  </div>

  <div id="importModal" class="fixed inset-0 bg-slate-950/60 z-50 hidden items-center justify-center p-4" onclick="if(event.target===this) closeImportModal()">
    <div class="w-full max-w-xl rounded-[28px] bg-white p-6 shadow-2xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h3 class="text-2xl font-extrabold">Stock import</h3>
          <p class="text-sm text-slate-500">Increase stock and save an inventory log.</p>
        </div>
        <button onclick="closeImportModal()" class="w-10 h-10 rounded-full bg-slate-100"><i class="fas fa-xmark"></i></button>
      </div>
      <div class="space-y-4">
        <div><label class="text-sm font-semibold">Product ID</label><input id="importProductId" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="1"></div>
        <div><label class="text-sm font-semibold">Import quantity</label><input id="importQuantity" type="number" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="25"></div>
        <div><label class="text-sm font-semibold">Note</label><input id="importNote" class="w-full rounded-2xl border border-slate-200 px-4 py-3 mt-2" placeholder="Restock from supplier A"></div>
      </div>
      <div class="flex justify-end gap-3 mt-6">
        <button onclick="closeImportModal()" class="px-5 py-3 rounded-2xl border border-slate-200 font-semibold">Cancel</button>
        <button onclick="saveImport()" class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-semibold">Confirm</button>
      </div>
    </div>
  </div>

  <script>
    const demoSeed = {
      categories: [
        { id: 1, name: 'Tables', description: 'Dining, coffee, and side tables', status: 'active' },
        { id: 2, name: 'Lighting', description: 'Lamps and lighting accessories', status: 'active' },
        { id: 3, name: 'Decor', description: 'Decorative home pieces', status: 'active' },
        { id: 4, name: 'Textiles', description: 'Blankets and soft furnishing', status: 'inactive' },
        { id: 5, name: 'Sofas', description: 'Large seating products', status: 'active' },
        { id: 6, name: 'Shelves', description: 'Storage and shelving', status: 'active' },
        { id: 7, name: 'Chairs', description: 'Dining and accent chairs', status: 'active' }
      ],
      products: [
        { id: 1, name: 'Tray Table - Oak', categoryId: 1, category: 'Tables', price: 199, stock: 45, sold: 124, status: 'active' },
        { id: 2, name: 'Modern Table Lamp', categoryId: 2, category: 'Lighting', price: 89, stock: 12, sold: 88, status: 'active' },
        { id: 3, name: 'Ceramic Vase Set', categoryId: 3, category: 'Decor', price: 49, stock: 3, sold: 62, status: 'active' },
        { id: 4, name: 'Cotton Blanket', categoryId: 4, category: 'Textiles', price: 79, stock: 0, sold: 47, status: 'inactive' },
        { id: 5, name: 'Nordic Sofa Gray', categoryId: 5, category: 'Sofas', price: 499, stock: 7, sold: 31, status: 'active' },
        { id: 6, name: 'Wall Shelf Minimal', categoryId: 6, category: 'Shelves', price: 129, stock: 4, sold: 55, status: 'active' },
        { id: 7, name: 'Dining Chair Walnut', categoryId: 7, category: 'Chairs', price: 109, stock: 16, sold: 92, status: 'active' }
      ],
      orders: [
        { id: 'ORD-1001', customer: 'Anna Nguyen', items: [{ name: 'Tray Table - Oak', qty: 1, price: 199 }], total: 199, status: 'pending', date: '2026-04-05' },
        { id: 'ORD-1002', customer: 'Binh Tran', items: [{ name: 'Modern Table Lamp', qty: 2, price: 89 }], total: 178, status: 'shipped', date: '2026-04-04' },
        { id: 'ORD-1003', customer: 'Nam Le', items: [{ name: 'Ceramic Vase Set', qty: 1, price: 49 }], total: 49, status: 'delivered', date: '2026-04-03' },
        { id: 'ORD-1004', customer: 'Ha Pham', items: [{ name: 'Cotton Blanket', qty: 1, price: 79 }], total: 79, status: 'pending', date: '2026-04-02' },
        { id: 'ORD-1005', customer: 'Quynh Anh Vu', items: [{ name: 'Nordic Sofa Gray', qty: 1, price: 499 }], total: 499, status: 'processing', date: '2026-04-01' },
        { id: 'ORD-1006', customer: 'Minh Ngo', items: [{ name: 'Wall Shelf Minimal', qty: 2, price: 129 }], total: 258, status: 'cancelled', date: '2026-03-30' },
        { id: 'ORD-1007', customer: 'Khanh Linh Do', items: [{ name: 'Dining Chair Walnut', qty: 4, price: 109 }], total: 436, status: 'delivered', date: '2026-03-28' }
      ],
      customers: [
        { name: 'Anna Nguyen', email: 'anna.nguyen@email.com', phone: '0987654321', total_spent: 540, orders: 3, rank: 'VIP', joined: '2026-03-02' },
        { name: 'Binh Tran', email: 'binh.tran@email.com', phone: '0978123456', total_spent: 1290, orders: 5, rank: 'VIP', joined: '2026-02-15' },
        { name: 'Nam Le', email: 'nam.le@email.com', phone: '0965234789', total_spent: 199, orders: 1, rank: 'Regular', joined: '2026-04-01' },
        { name: 'Ha Pham', email: 'ha.pham@email.com', phone: '0901122334', total_spent: 479, orders: 2, rank: 'Loyal', joined: '2026-03-25' },
        { name: 'Quynh Anh Vu', email: 'quynhanh@email.com', phone: '0912456789', total_spent: 1499, orders: 4, rank: 'VIP', joined: '2026-01-18' }
      ],
      coupons: [
        { code: 'SUMMER30', discount: 30, expiry: '2026-06-30', active: true },
        { code: 'NEW20', discount: 20, expiry: '2026-04-30', active: true },
        { code: 'VIP15', discount: 15, expiry: '2026-03-31', active: true }
      ],
      inventoryLogs: [
        { productId: 2, quantity: 20, note: 'Restocked table lamps', date: '05/04/2026, 09:20:00' },
        { productId: 3, quantity: 15, note: 'Decor replenishment', date: '03/04/2026, 14:35:00' },
        { productId: 6, quantity: 10, note: 'New shelf batch', date: '01/04/2026, 10:15:00' }
      ]
    };

    let categories = JSON.parse(localStorage.getItem('categories')) || structuredClone(demoSeed.categories);
    let products = JSON.parse(localStorage.getItem('products')) || structuredClone(demoSeed.products);
    let orders = JSON.parse(localStorage.getItem('orders')) || structuredClone(demoSeed.orders);
    let customers = JSON.parse(localStorage.getItem('customers')) || structuredClone(demoSeed.customers);
    let coupons = JSON.parse(localStorage.getItem('coupons')) || structuredClone(demoSeed.coupons);
    let inventoryLogs = JSON.parse(localStorage.getItem('inventoryLogs')) || structuredClone(demoSeed.inventoryLogs);

    let currentProductPage = 1;
    const productsPerPage = 5;
    let currentProductFilter = 'all';
    let currentSection = 'dashboard';
    let revenueChart, categoryRevenueChart, orderStatusChart;
    let selectedProductId = products[0]?.id || null;

    function persistData() {
      localStorage.setItem('categories', JSON.stringify(categories));
      localStorage.setItem('products', JSON.stringify(products));
      localStorage.setItem('orders', JSON.stringify(orders));
      localStorage.setItem('customers', JSON.stringify(customers));
      localStorage.setItem('coupons', JSON.stringify(coupons));
      localStorage.setItem('inventoryLogs', JSON.stringify(inventoryLogs));
    }

    function money(value) {
      return '$' + Number(value || 0).toLocaleString('en-US');
    }

    function formatDate(value) {
      if (!value) return '--';
      const d = new Date(value);
      if (Number.isNaN(d.getTime())) return value;
      return d.toLocaleDateString('vi-VN');
    }

    function getStatusLabel(status) {
      const map = {
        pending: ['Pending', 'bg-amber-50 text-amber-700', 'bg-amber-500'],
        processing: ['Processing', 'bg-indigo-50 text-indigo-700', 'bg-indigo-500'],
        shipped: ['Shipped', 'bg-sky-50 text-sky-700', 'bg-sky-500'],
        delivered: ['Delivered', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        cancelled: ['Cancelled', 'bg-rose-50 text-rose-700', 'bg-rose-500'],
        active: ['Active', 'bg-emerald-50 text-emerald-700', 'bg-emerald-500'],
        inactive: ['Ngừng bán', 'bg-slate-100 text-slate-600', 'bg-slate-400']
      };
      return map[status] || [status, 'bg-slate-100 text-slate-700', 'bg-slate-400'];
    }

    function renderStatusPill(status) {
      const [label, classes, dot] = getStatusLabel(status);
      return `<span class="status-pill ${classes}"><span class="dot ${dot}"></span>${label}</span>`;
    }

    function calculateMetrics() {
      const validOrders = orders.filter(o => o.status !== 'cancelled');
      const revenue = validOrders.reduce((sum, o) => sum + o.total, 0);
      const pending = orders.filter(o => o.status === 'pending').length;
      const vipCount = customers.filter(c => c.rank === 'VIP').length;
      const lowStock = products.filter(p => p.stock <= 5).length;
      const today = '2026-04-05';
      const todayOrders = orders.filter(o => o.date === today).length;
      const newCustomers = customers.filter(c => c.joined && c.joined.startsWith('2026-04')).length;
      const growth = orders.length ? Math.round((validOrders.length / orders.length) * 100) : 0;
      return { revenue, pending, vipCount, lowStock, todayOrders, newCustomers, growth };
    }

    function getProductName(productId) {
      const product = products.find(p => p.id === productId);
      return product ? product.name : `Product #${productId}`;
    }

    function renderDashboard() {
      const metrics = calculateMetrics();
      document.getElementById('totalRevenue').innerText = money(metrics.revenue);
      document.getElementById('totalOrders').innerText = orders.length;
      document.getElementById('pendingOrders').innerText = metrics.pending;
      document.getElementById('totalCustomers').innerText = customers.length;
      document.getElementById('newCustomers').innerText = metrics.newCustomers;
      document.getElementById('vipCustomersCount').innerText = metrics.vipCount;
      document.getElementById('todayOrders').innerText = metrics.todayOrders;
      document.getElementById('lowStockCount').innerText = metrics.lowStock;
      document.getElementById('revenueGrowthBadge').innerHTML = `<span class="dot bg-emerald-500"></span>+${metrics.growth}%`;

      const topProducts = [...products].sort((a, b) => (b.sold || 0) - (a.sold || 0)).slice(0, 4);
      document.getElementById('topProductsList').innerHTML = topProducts.map((p, idx) => `
        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200/80 p-3 bg-white/70">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-11 h-11 rounded-2xl bg-slate-100 flex items-center justify-center font-bold">${idx + 1}</div>
            <div class="min-w-0">
              <p class="font-semibold truncate">${p.name}</p>
              <p class="text-xs text-slate-400">${p.category} • Sold ${p.sold || 0}</p>
            </div>
          </div>
          <span class="font-bold text-slate-800">${money(p.price)}</span>
        </div>
      `).join('');

      const pending = orders.filter(o => o.status === 'pending').length;
      const lowStock = products.filter(p => p.stock <= 5).length;
      const expiredCoupons = coupons.filter(c => isCouponExpired(c.expiry)).length;
      document.getElementById('quickTasks').innerHTML = [
        { title: 'Đơn hàng chờ xử lý', value: pending, color: 'amber', action: "setActiveSection('orders')" },
        { title: 'Low stock items', value: lowStock, color: 'rose', action: "setActiveSection('inventory')" },
        { title: 'Coupon đã hết hạn', value: expiredCoupons, color: 'slate', action: "setActiveSection('marketing')" }
      ].map(item => `
        <button onclick="${item.action}" class="w-full flex items-center justify-between rounded-2xl border border-slate-200 p-3 bg-white/70 hover:bg-white text-left">
          <div>
            <p class="font-semibold">${item.title}</p>
            <p class="text-xs text-slate-400">Click to open the related module</p>
          </div>
          <div class="text-xl font-extrabold">${item.value}</div>
        </button>
      `).join('');

      const recentOrders = [...orders].sort((a, b) => new Date(b.date) - new Date(a.date)).slice(0, 5);
      document.getElementById('recentOrdersList').innerHTML = recentOrders.map(o => `
        <div class="rounded-2xl border border-slate-200 p-4 bg-white/70 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div class="flex flex-wrap items-center gap-2 mb-1"><p class="font-bold">${o.id}</p>${renderStatusPill(o.status)}</div>
            <p class="text-sm text-slate-500">${o.customer} • ${o.items.map(i => `${i.name} x${i.qty}`).join(', ')}</p>
          </div>
          <div class="text-right">
            <p class="font-extrabold">${money(o.total)}</p>
            <p class="text-xs text-slate-400">${formatDate(o.date)}</p>
          </div>
        </div>
      `).join('');

      const alerts = products.filter(p => p.stock <= 5).sort((a, b) => a.stock - b.stock);
      document.getElementById('stockAlerts').innerHTML = alerts.length ? alerts.map(p => `
        <div class="rounded-2xl border border-rose-100 bg-rose-50/70 p-4">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="font-semibold">${p.name}</p>
              <p class="text-sm text-rose-700">Stock hiện tại: ${p.stock}</p>
            </div>
            <button onclick="openImportModal(); document.getElementById('importProductId').value='${p.id}'" class="text-sm font-semibold text-rose-700">Nhập thêm</button>
          </div>
        </div>
      `).join('') : `<div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-emerald-700 font-medium">Inventory looks healthy. No products are below the alert threshold.</div>`;

      updateRevenueChart();
      updateReportCharts();
    }

    function getCategoryUsageCount(categoryId) {
      return products.filter(p => Number(p.categoryId) === Number(categoryId)).length;
    }

    function getCategorySalesCount(categoryId) {
      return products
        .filter(p => Number(p.categoryId) === Number(categoryId))
        .reduce((sum, p) => sum + Number(p.sold || 0), 0);
    }

    function populateCategoryOptions(selectedCategoryId = '') {
      const select = document.getElementById('prodCategory');
      if (!select) return;
      const options = categories.map(category => `
        <option value="${category.id}" ${String(category.id) === String(selectedCategoryId) ? 'selected' : ''}>${category.name}</option>
      `).join('');
      select.innerHTML = categories.length ? options : '<option value="">No categories yet</option>';
    }

    function getFilteredCategories() {
      const search = document.getElementById('categorySearch')?.value?.toLowerCase().trim() || '';
      return categories.filter(category => {
        const matchFilter = currentCategoryFilter === 'all' || category.status === currentCategoryFilter;
        const matchSearch = !search || category.name.toLowerCase().includes(search) || (category.description || '').toLowerCase().includes(search);
        return matchFilter && matchSearch;
      });
    }

    function renderCategoryDetail(categoryId = currentCategoryId) {
      const category = categories.find(item => Number(item.id) === Number(categoryId)) || categories[0];
      if (!category) return;
      currentCategoryId = category.id;
      const linkedProducts = products.filter(product => Number(product.categoryId) === Number(category.id));

      document.getElementById('categoryDetailName').innerText = category.name;
      document.getElementById('categoryDetailDesc').innerText = category.description || 'No description yet.';
      document.getElementById('categoryDetailStatus').innerHTML = renderStatusPill(category.status);
      document.getElementById('categoryDetailProducts').innerText = linkedProducts.length;
      document.getElementById('categoryDetailSales').innerText = linkedProducts.reduce((sum, item) => sum + Number(item.sold || 0), 0);
      document.getElementById('categoryLinkedCount').innerText = `${linkedProducts.length} item${linkedProducts.length === 1 ? '' : 's'}`;

      document.getElementById('categoryLinkedProducts').innerHTML = linkedProducts.length ? linkedProducts.map(product => `
        <div class="rounded-2xl border border-slate-200 bg-white/70 p-3">
          <div class="flex items-center justify-between gap-3">
            <div>
              <p class="font-semibold">${product.name}</p>
              <p class="text-xs text-slate-400 mt-1">${money(product.price)} • Stock ${product.stock}</p>
            </div>
            <button onclick="openProductDetail(${product.id})" class="px-3 py-2 rounded-xl bg-slate-100 text-sm font-semibold">View</button>
          </div>
        </div>
      `).join('') : `<div class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-sm text-slate-400">No products are linked to this category yet.</div>`;
    }

    function renderCategories() {
      const filtered = getFilteredCategories();
      document.getElementById('categoryCount').innerText = categories.length;
      document.getElementById('activeCategoryCount').innerText = categories.filter(category => category.status === 'active').length;
      document.getElementById('inactiveCategoryCount').innerText = categories.filter(category => category.status === 'inactive').length;
      document.getElementById('mappedProductCount').innerText = products.length;

      document.getElementById('categoryTableBody').innerHTML = filtered.length ? filtered.map(category => `
        <tr class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50/70">
          <td class="px-4 py-4 text-slate-500 font-medium">#${category.id}</td>
          <td class="px-4 py-4">
            <p class="font-semibold">${category.name}</p>
            <p class="text-xs text-slate-400">${getCategorySalesCount(category.id)} units sold across mapped products</p>
          </td>
          <td class="px-4 py-4 text-slate-500">${category.description || '--'}</td>
          <td class="px-4 py-4 font-semibold">${getCategoryUsageCount(category.id)}</td>
          <td class="px-4 py-4">${renderStatusPill(category.status)}</td>
          <td class="px-4 py-4 text-right whitespace-nowrap">
            <button onclick="viewCategory(${category.id})" class="w-9 h-9 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 mr-1"><i class="far fa-eye"></i></button>
            <button onclick="openCategoryModal(${category.id})" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 mr-1"><i class="far fa-pen-to-square"></i></button>
            <button onclick="deleteCategory(${category.id})" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100"><i class="far fa-trash-can"></i></button>
          </td>
        </tr>
      `).join('') : `<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No categories matched your search.</td></tr>`;

      if (!categories.some(item => Number(item.id) === Number(currentCategoryId)) && categories.length) {
        currentCategoryId = categories[0].id;
      }
      renderCategoryDetail(currentCategoryId);
      populateCategoryOptions();
    }

    function filterCategories(filter) {
      currentCategoryFilter = filter;
      renderCategories();
    }

    function viewCategory(categoryId) {
      currentCategoryId = Number(categoryId);
      renderCategoryDetail(currentCategoryId);
    }

    function openCategoryModal(id = null) {
      const modal = document.getElementById('categoryModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');

      if (id) {
        const category = categories.find(item => Number(item.id) === Number(id));
        if (!category) return;
        document.getElementById('categoryModalTitle').innerText = 'Edit category';
        document.getElementById('editCategoryId').value = category.id;
        document.getElementById('categoryName').value = category.name;
        document.getElementById('categoryDescription').value = category.description || '';
        document.getElementById('categoryStatus').value = category.status;
      } else {
        document.getElementById('categoryModalTitle').innerText = 'Add category';
        document.getElementById('editCategoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryDescription').value = '';
        document.getElementById('categoryStatus').value = 'active';
      }
    }

    function closeCategoryModal() {
      const modal = document.getElementById('categoryModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function saveCategory() {
      const id = Number(document.getElementById('editCategoryId').value);
      const name = document.getElementById('categoryName').value.trim();
      const description = document.getElementById('categoryDescription').value.trim();
      const status = document.getElementById('categoryStatus').value;

      if (!name) {
        Swal.fire('Missing data', 'Please enter a category name.', 'error');
        return;
      }

      const duplicate = categories.find(category => category.name.toLowerCase() === name.toLowerCase() && Number(category.id) !== id);
      if (duplicate) {
        Swal.fire('Duplicate category', 'This category name already exists.', 'warning');
        return;
      }

      if (id) {
        const index = categories.findIndex(category => Number(category.id) === id);
        categories[index] = { ...categories[index], name, description, status };
        products = products.map(product => Number(product.categoryId) === id ? { ...product, category: name } : product);
        Swal.fire('Updated', 'Category updated successfully.', 'success');
      } else {
        const newId = categories.length ? Math.max(...categories.map(category => Number(category.id))) + 1 : 1;
        categories.push({ id: newId, name, description, status });
        Swal.fire('Added', 'Category created successfully.', 'success');
      }

      persistData();
      closeCategoryModal();
      rerenderAll();
    }

    function deleteCategory(id) {
      const linkedCount = getCategoryUsageCount(id);
      if (linkedCount > 0) {
        Swal.fire('Cannot delete', 'This category still has linked products. Move or delete those products first.', 'warning');
        return;
      }

      Swal.fire({
        title: 'Delete category?',
        text: 'This will remove the category from the current demo data.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#e11d48'
      }).then(result => {
        if (!result.isConfirmed) return;
        categories = categories.filter(category => Number(category.id) !== Number(id));
        if (Number(currentCategoryId) === Number(id) && categories.length) {
          currentCategoryId = categories[0].id;
        }
        persistData();
        rerenderAll();
        Swal.fire('Deleted', 'Category removed successfully.', 'success');
      });
    }

    function getFilteredProducts() {
      const search = document.getElementById('productSearch')?.value?.toLowerCase().trim() || '';
      return products.filter(p => {
        const matchFilter = currentProductFilter === 'all'
          ? true
          : currentProductFilter === 'active'
          ? p.status === 'active'
          : p.stock <= 5;
        const matchSearch = !search || p.name.toLowerCase().includes(search) || p.category.toLowerCase().includes(search);
        return matchFilter && matchSearch;
      });
    }

    function renderProducts() {
      const filtered = getFilteredProducts();
      const totalPages = Math.max(1, Math.ceil(filtered.length / productsPerPage));
      if (currentProductPage > totalPages) currentProductPage = totalPages;
      const start = (currentProductPage - 1) * productsPerPage;
      const paginated = filtered.slice(start, start + productsPerPage);

      document.getElementById('productTableBody').innerHTML = paginated.length ? paginated.map(p => `
        <tr class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50/70">
          <td class="px-4 py-4 text-slate-500 font-medium">#${p.id}</td>
          <td class="px-4 py-4">
            <p class="font-semibold">${p.name}</p>
            <p class="text-xs text-slate-400">${p.stock <= 5 ? 'Needs stock attention' : 'Operating normally'}</p>
          </td>
          <td class="px-4 py-4 text-slate-500">${p.category}</td>
          <td class="px-4 py-4 font-bold">${money(p.price)}</td>
          <td class="px-4 py-4 ${p.stock <= 5 ? 'text-rose-600 font-bold' : 'font-semibold'}">${p.stock}</td>
          <td class="px-4 py-4 text-slate-600">${p.sold || 0}</td>
          <td class="px-4 py-4">${renderStatusPill(p.status)}</td>
          <td class="px-4 py-4 text-right">
            <button onclick="openProductDetail(${p.id})" class="w-9 h-9 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 mr-1"><i class="far fa-eye"></i></button>
            <button onclick="editProduct(${p.id})" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 mr-1"><i class="far fa-pen-to-square"></i></button>
            <button onclick="deleteProduct(${p.id})" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100"><i class="far fa-trash-can"></i></button>
          </td>
        </tr>
      `).join('') : `<tr><td colspan="8" class="px-4 py-12 text-center text-slate-400">Không có sản phẩm phù hợp.</td></tr>`;

      document.getElementById('productPageInfo').innerText = `Page ${currentProductPage} / ${totalPages}`;
    }

    function openProductDetail(id) {
      selectedProductId = id;
      setActiveSection('product-detail');
      renderProductDetail();
    }

    function renderProductDetail() {
      const product = products.find(p => p.id === selectedProductId) || products[0];
      if (!product) {
        document.getElementById('productDetailContent').innerHTML = '<div class="rounded-2xl border border-slate-200 bg-white/70 p-6 text-slate-400">No product available.</div>';
        return;
      }

      const relatedOrders = orders.filter(o => o.items.some(i => i.name === product.name));
      const estimatedRevenue = relatedOrders.reduce((sum, o) => {
        const lines = o.items.filter(i => i.name === product.name);
        return sum + lines.reduce((s, i) => s + (i.price * i.qty), 0);
      }, 0);

      const stockState = product.stock <= 5 ? 'Low stock' : product.stock <= 12 ? 'Watch stock' : 'Healthy stock';
      const notes = [
        `Main category: ${product.category}`,
        `Current stock level: ${product.stock} units`,
        `Total sold units: ${product.sold || 0}`,
        product.status === 'active' ? 'Visible on storefront and eligible for sales.' : 'Hidden from storefront until reactivated.'
      ];

      document.getElementById('productDetailContent').innerHTML = `
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
          <div class="xl:col-span-2 space-y-6">
            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <div class="flex flex-col md:flex-row gap-5">
                <div class="w-full md:w-[280px] h-[240px] rounded-[24px] bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-sm">
                  Product image preview
                </div>
                <div class="flex-1">
                  <div class="flex flex-wrap items-center gap-2 mb-3">
                    ${renderStatusPill(product.status)}
                    <span class="status-pill ${product.stock <= 5 ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700'}"><span class="dot ${product.stock <= 5 ? 'bg-rose-500' : 'bg-emerald-500'}"></span>${stockState}</span>
                  </div>
                  <h3 class="text-2xl font-extrabold">${product.name}</h3>
                  <p class="text-sm text-slate-500 mt-2">Category: <span class="font-semibold text-slate-700">${product.category}</span></p>
                  <p class="text-sm text-slate-500">SKU: PRD-${String(product.id).padStart(4,'0')}</p>
                  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-5">
                    <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Price</p><p class="font-bold mt-1">${money(product.price)}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Stock</p><p class="font-bold mt-1">${product.stock}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Sold</p><p class="font-bold mt-1">${product.sold || 0}</p></div>
                    <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Revenue</p><p class="font-bold mt-1">${money(estimatedRevenue)}</p></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <h4 class="text-lg font-bold mb-3">Description</h4>
              <p class="text-sm text-slate-600 leading-7">This product detail view is designed for the admin team to review the category mapping, status, pricing, stock condition, and sales performance before editing the product record. It also gives a clear place to attach gallery images and operational notes later.</p>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-bold">Linked orders</h4>
                <span class="text-xs text-slate-400">${relatedOrders.length} orders found</span>
              </div>
              <div class="space-y-3">
                ${relatedOrders.length ? relatedOrders.map(o => `
                  <div class="rounded-2xl border border-slate-200 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                      <div class="flex flex-wrap items-center gap-2 mb-1"><p class="font-bold">${o.id}</p>${renderStatusPill(o.status)}</div>
                      <p class="text-sm text-slate-500">${o.customer} • ${formatDate(o.date)}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-bold">${money(o.total)}</p>
                      <button onclick="setActiveSection('orders'); document.getElementById('orderSearch').value='${o.id.toLowerCase()}'; renderOrders();" class="text-sm text-slate-700 font-semibold">Open order</button>
                    </div>
                  </div>
                `).join('') : '<div class="rounded-2xl border border-slate-200 p-4 text-slate-400">No linked orders yet for this demo product.</div>'}
              </div>
            </div>
          </div>

          <div class="space-y-6">
            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <h4 class="text-lg font-bold mb-4">Gallery placeholders</h4>
              <div class="grid grid-cols-2 gap-3">
                <div class="h-28 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs text-slate-400">Main photo</div>
                <div class="h-28 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs text-slate-400">Side photo</div>
                <div class="h-28 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs text-slate-400">Material</div>
                <div class="h-28 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-xs text-slate-400">Lifestyle</div>
              </div>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <h4 class="text-lg font-bold mb-4">Inventory notes</h4>
              <div class="space-y-3">
                ${notes.map(note => `<div class="rounded-2xl bg-slate-50 p-3 text-sm text-slate-600">${note}</div>`).join('')}
              </div>
            </div>

            <div class="rounded-[24px] border border-slate-200 bg-white/80 p-5">
              <h4 class="text-lg font-bold mb-4">Quick actions</h4>
              <div class="space-y-2">
                <button onclick="editProduct(${product.id})" class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold">Edit this product</button>
                <button onclick="setActiveSection('inventory'); document.getElementById('importProductId').value='${product.id}'; openImportModal();" class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold">Create stock import</button>
                <button onclick="setActiveSection('products')" class="w-full px-4 py-3 rounded-2xl bg-white border border-slate-200 text-sm font-semibold">Return to product list</button>
              </div>
            </div>
          </div>
        </div>`;
    }

    function renderOrders() {
      const filter = document.getElementById('orderStatusFilter').value;
      const search = document.getElementById('orderSearch').value.toLowerCase().trim();
      const filtered = orders.filter(o => {
        const matchFilter = filter === 'all' || o.status === filter;
        const matchSearch = !search || o.id.toLowerCase().includes(search) || o.customer.toLowerCase().includes(search);
        return matchFilter && matchSearch;
      });

      document.getElementById('ordersTableBody').innerHTML = filtered.length ? filtered.map(o => `
        <tr class="border-b border-slate-100 last:border-b-0 hover:bg-slate-50/70">
          <td class="px-4 py-4 font-bold">${o.id}</td>
          <td class="px-4 py-4">
            <p class="font-semibold">${o.customer}</p>
            <p class="text-xs text-slate-400">${o.items.reduce((sum, i) => sum + i.qty, 0)} sản phẩm</p>
          </td>
          <td class="px-4 py-4 text-slate-500">${o.items.map(i => `${i.name} x${i.qty}`).join(', ')}</td>
          <td class="px-4 py-4 font-bold">${money(o.total)}</td>
          <td class="px-4 py-4">${renderStatusPill(o.status)}</td>
          <td class="px-4 py-4 text-slate-500">${formatDate(o.date)}</td>
          <td class="px-4 py-4 text-right">
            <button onclick="updateOrderStatus('${o.id}')" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-sm font-semibold">Update</button>
          </td>
        </tr>
      `).join('') : `<tr><td colspan="7" class="px-4 py-12 text-center text-slate-400">Không tìm thấy đơn hàng.</td></tr>`;

      document.getElementById('pendingCount').innerText = orders.filter(o => o.status === 'pending').length;
      document.getElementById('processingCount').innerText = orders.filter(o => o.status === 'processing').length;
      document.getElementById('shippedCount').innerText = orders.filter(o => o.status === 'shipped').length;
      document.getElementById('deliveredCount').innerText = orders.filter(o => o.status === 'delivered').length;
    }

    function renderCustomers() {
      const search = document.getElementById('customerSearch').value.toLowerCase().trim();
      const filtered = customers.filter(c => !search || c.name.toLowerCase().includes(search) || c.email.toLowerCase().includes(search) || c.phone.includes(search));

      document.getElementById('customerList').innerHTML = filtered.length ? filtered.map(c => `
        <div class="rounded-[24px] border border-slate-200 bg-white/70 p-5 card-hover">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-lg font-bold">${c.name}</p>
              <p class="text-sm text-slate-500 mt-1">${c.email}</p>
              <p class="text-sm text-slate-500">${c.phone}</p>
            </div>
            <span class="status-pill ${c.rank === 'VIP' ? 'bg-amber-50 text-amber-700' : c.rank === 'Loyal' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'}"><span class="dot ${c.rank === 'VIP' ? 'bg-amber-500' : c.rank === 'Loyal' ? 'bg-indigo-500' : 'bg-slate-400'}"></span>${c.rank}</span>
          </div>
          <div class="grid grid-cols-3 gap-3 mt-5">
            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Total spend</p><p class="font-bold mt-1">${money(c.total_spent)}</p></div>
            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Orders</p><p class="font-bold mt-1">${c.orders}</p></div>
            <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Joined</p><p class="font-bold mt-1">${formatDate(c.joined)}</p></div>
          </div>
        </div>
      `).join('') : `<div class="text-slate-400">Không có khách hàng phù hợp.</div>`;
    }

    function isCouponExpired(expiry) {
      return expiry < '2026-04-05';
    }

    function renderCoupons() {
      document.getElementById('couponList').innerHTML = coupons.length ? coupons.map(c => {
        const expired = isCouponExpired(c.expiry);
        const stateLabel = expired ? 'Expired' : c.active ? 'Active' : 'Disable';
        const stateClass = expired ? 'bg-rose-50 text-rose-700' : c.active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600';
        return `
          <div class="rounded-[24px] border border-slate-200 bg-white/70 p-5 card-hover">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-xl font-extrabold tracking-wide">${c.code}</p>
                <p class="text-sm text-slate-500 mt-1">Save ${c.discount}% on eligible orders</p>
              </div>
              <span class="status-pill ${stateClass}">${stateLabel}</span>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3">
              <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Discount</p><p class="font-bold mt-1">${c.discount}%</p></div>
              <div class="rounded-2xl bg-slate-50 p-3"><p class="text-xs text-slate-400">Expiry</p><p class="font-bold mt-1">${formatDate(c.expiry)}</p></div>
            </div>
            <div class="mt-4 flex gap-2">
              <button onclick="toggleCoupon('${c.code}')" ${expired ? 'disabled' : ''} class="flex-1 px-4 py-2.5 rounded-2xl ${expired ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-slate-900 text-white'} text-sm font-semibold">${c.active ? 'Disable' : 'Enable'}</button>
              <button onclick="deleteCoupon('${c.code}')" class="px-4 py-2.5 rounded-2xl bg-rose-50 text-rose-600 text-sm font-semibold">Xóa</button>
            </div>
          </div>
        `;
      }).join('') : `<div class="text-slate-400">Chưa có coupon nào.</div>`;
    }

    function renderInventory() {
      const totalStock = products.reduce((sum, p) => sum + p.stock, 0);
      const lowStock = products.filter(p => p.stock <= 5).length;
      const totalLogs = inventoryLogs.length;
      document.getElementById('inventorySummary').innerHTML = `
        <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Total stock</p><p class="text-2xl font-extrabold mt-2">${totalStock}</p></div>
        <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Product cần nhập</p><p class="text-2xl font-extrabold mt-2">${lowStock}</p></div>
        <div class="rounded-[24px] bg-white/70 border border-slate-200 p-4"><p class="text-sm text-slate-500">Stock entries</p><p class="text-2xl font-extrabold mt-2">${totalLogs}</p></div>
      `;

      document.getElementById('inventoryList').innerHTML = inventoryLogs.length ? [...inventoryLogs].reverse().map(log => `
        <div class="rounded-[24px] border border-slate-200 bg-white/70 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <p class="font-bold">${getProductName(log.productId)}</p>
            <p class="text-sm text-slate-500">Product ID: ${log.productId} • ${log.note || 'No note'}</p>
            <p class="text-xs text-slate-400 mt-1">${log.date}</p>
          </div>
          <div class="text-emerald-700 text-xl font-extrabold">+${log.quantity}</div>
        </div>
      `).join('') : `<div class="rounded-2xl border border-slate-200 bg-white/70 p-4 text-slate-400">Chưa có lịch sử nhập kho.</div>`;
    }

    function openProductModal(id = null) {
      const modal = document.getElementById('productModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      if (id) {
        const p = products.find(item => item.id === id);
        if (!p) return;
        document.getElementById('modalTitle').innerText = 'Chỉnh sửa sản phẩm';
        document.getElementById('editProductId').value = p.id;
        document.getElementById('prodName').value = p.name;
        populateCategoryOptions(p.categoryId || '');
        document.getElementById('prodPrice').value = p.price;
        document.getElementById('prodStock').value = p.stock;
        document.getElementById('prodSold').value = p.sold || 0;
        document.getElementById('prodStatus').value = p.status;
      } else {
        document.getElementById('modalTitle').innerText = 'Add sản phẩm';
        document.getElementById('editProductId').value = '';
        document.getElementById('prodName').value = '';
        populateCategoryOptions();
        document.getElementById('prodPrice').value = '';
        document.getElementById('prodStock').value = '';
        document.getElementById('prodSold').value = '';
        document.getElementById('prodStatus').value = 'active';
      }
    }

    function closeProductModal() {
      const modal = document.getElementById('productModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    function saveProduct() {
      const id = Number(document.getElementById('editProductId').value);
      const name = document.getElementById('prodName').value.trim();
      const categoryId = Number(document.getElementById('prodCategory').value);
      const selectedCategory = categories.find(category => Number(category.id) === categoryId);
      const category = selectedCategory ? selectedCategory.name : '';
      const price = Number(document.getElementById('prodPrice').value);
      const stock = Number(document.getElementById('prodStock').value);
      const sold = Number(document.getElementById('prodSold').value || 0);
      const status = document.getElementById('prodStatus').value;

      if (!name || !category || Number.isNaN(categoryId) || !selectedCategory || Number.isNaN(price) || price < 0 || Number.isNaN(stock) || stock < 0 || Number.isNaN(sold) || sold < 0) {
        Swal.fire('Missing data', 'Please enter a valid product name, category, price, stock, and sold quantity.', 'error');
        return;
      }

      if (id) {
        const index = products.findIndex(p => p.id === id);
        if (index !== -1) {
          products[index] = { ...products[index], name, categoryId, category, price, stock, sold, status };
        }
        Swal.fire('Đã cập nhật', 'Product đã được chỉnh sửa thành công.', 'success');
      } else {
        const newId = products.length ? Math.max(...products.map(p => p.id)) + 1 : 1;
        products.push({ id: newId, name, categoryId, category, price, stock, sold, status });
        Swal.fire('Đã thêm', 'Product mới đã được tạo.', 'success');
      }

      persistData();
      closeProductModal();
      rerenderAll();
    }

    function editProduct(id) { openProductModal(id); }

    function deleteProduct(id) {
      Swal.fire({
        title: 'Xóa sản phẩm?',
        text: 'Actions này sẽ xóa khỏi dữ liệu demo hiện tại.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#e11d48'
      }).then(res => {
        if (!res.isConfirmed) return;
        products = products.filter(p => p.id !== id);
        persistData();
        rerenderAll();
        Swal.fire('Đã xóa', 'Product đã được xóa.', 'success');
      });
    }

    function filterProducts(filter) {
      currentProductFilter = filter;
      currentProductPage = 1;
      renderProducts();
    }

    function prevProductPage() {
      if (currentProductPage > 1) {
        currentProductPage--;
        renderProducts();
      }
    }

    function nextProductPage() {
      const total = Math.max(1, Math.ceil(getFilteredProducts().length / productsPerPage));
      if (currentProductPage < total) {
        currentProductPage++;
        renderProducts();
      }
    }

    function exportProducts() {
      const rows = products.map(p => ({ ID: p.id, Name: p.name, Category: p.category, Price: p.price, Stock: p.stock, Sold: p.sold || 0, Status: p.status }));
      const ws = XLSX.utils.json_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'SanPham');
      XLSX.writeFile(wb, 'admin-products-sample.xlsx');
      Swal.fire('Xuất thành công', 'Danh sách sản phẩm đã được tải xuống.', 'success');
    }

    function updateOrderStatus(orderId) {
      const order = orders.find(o => o.id === orderId);
      if (!order) return;
      Swal.fire({
        title: 'Update trạng thái đơn hàng',
        input: 'select',
        inputOptions: {
          pending: 'Pending',
          processing: 'Processing',
          shipped: 'Shipped',
          delivered: 'Delivered',
          cancelled: 'Cancelled'
        },
        inputValue: order.status,
        showCancelButton: true,
        confirmButtonText: 'Lưu',
        cancelButtonText: 'Cancel'
      }).then(res => {
        if (!res.value) return;
        order.status = res.value;
        persistData();
        rerenderAll();
        Swal.fire('Đã cập nhật', `Đơn ${order.id} đã đổi trạng thái.`, 'success');
      });
    }

    function openCouponModal() {
      document.getElementById('couponModal').classList.remove('hidden');
      document.getElementById('couponModal').classList.add('flex');
    }

    function closeCouponModal() {
      document.getElementById('couponModal').classList.add('hidden');
      document.getElementById('couponModal').classList.remove('flex');
      document.getElementById('couponCode').value = '';
      document.getElementById('couponDiscount').value = '';
      document.getElementById('couponExpiry').value = '';
    }

    function addCoupon() {
      const code = document.getElementById('couponCode').value.trim().toUpperCase();
      const discount = Number(document.getElementById('couponDiscount').value);
      const expiry = document.getElementById('couponExpiry').value;
      if (!code || Number.isNaN(discount) || discount <= 0 || discount > 100 || !expiry) {
        Swal.fire('Thiếu dữ liệu', 'Nhập đầy đủ mã, phần trăm giảm và ngày hết hạn.', 'error');
        return;
      }
      if (coupons.some(c => c.code === code)) {
        Swal.fire('Trùng mã', 'Coupons này đã tồn tại.', 'warning');
        return;
      }
      coupons.push({ code, discount, expiry, active: true });
      persistData();
      renderCoupons();
      closeCouponModal();
      Swal.fire('Thành công', 'Đã thêm coupon mới.', 'success');
    }

    function toggleCoupon(code) {
      const coupon = coupons.find(c => c.code === code);
      if (!coupon || isCouponExpired(coupon.expiry)) return;
      coupon.active = !coupon.active;
      persistData();
      renderCoupons();
    }

    function deleteCoupon(code) {
      coupons = coupons.filter(c => c.code !== code);
      persistData();
      renderCoupons();
      Swal.fire('Đã xóa', 'Coupon đã được xóa.', 'success');
    }

    function openImportModal() {
      document.getElementById('importModal').classList.remove('hidden');
      document.getElementById('importModal').classList.add('flex');
    }

    function closeImportModal() {
      document.getElementById('importModal').classList.add('hidden');
      document.getElementById('importModal').classList.remove('flex');
      document.getElementById('importProductId').value = '';
      document.getElementById('importQuantity').value = '';
      document.getElementById('importNote').value = '';
    }

    function saveImport() {
      const productId = Number(document.getElementById('importProductId').value);
      const quantity = Number(document.getElementById('importQuantity').value);
      const note = document.getElementById('importNote').value.trim();
      const product = products.find(p => p.id === productId);
      if (!product || Number.isNaN(quantity) || quantity <= 0) {
        Swal.fire('Không hợp lệ', 'Kiểm tra lại Product ID và số lượng nhập.', 'error');
        return;
      }
      product.stock += quantity;
      inventoryLogs.push({ productId, quantity, note: note || 'Stock import thủ công', date: new Date().toLocaleString('vi-VN') });
      persistData();
      closeImportModal();
      rerenderAll();
      Swal.fire('Thành công', `Đã nhập thêm ${quantity} sản phẩm cho ${product.name}.`, 'success');
    }

    function exportCustomers() {
      const ws = XLSX.utils.json_to_sheet(customers);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'KhachHang');
      XLSX.writeFile(wb, 'admin-customers-sample.xlsx');
      Swal.fire('Xuất thành công', 'Danh sách khách hàng đã được tải xuống.', 'success');
    }

    function exportReport() {
      const rows = orders.map(o => ({ OrderCode: o.id, Customer: o.customer, Total: o.total, Status: o.status, Date: o.date }));
      const ws = XLSX.utils.json_to_sheet(rows);
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, 'BaoCao');
      XLSX.writeFile(wb, 'admin-report-sample.xlsx');
      Swal.fire('Đã xuất', 'Báo cáo đơn hàng đã được tải xuống.', 'success');
    }

    function saveSettings() {
      localStorage.setItem('shopName', document.getElementById('shopName').value.trim());
      localStorage.setItem('shopEmail', document.getElementById('shopEmail').value.trim());
      localStorage.setItem('shopPhone', document.getElementById('shopPhone').value.trim());
      localStorage.setItem('shopAddress', document.getElementById('shopAddress').value.trim());
      localStorage.setItem('shippingFee', document.getElementById('shippingFee').value.trim());
      Swal.fire('Đã lưu', 'Thông tin cửa hàng đã được lưu vào localStorage.', 'success');
    }

    function loadSettings() {
      document.getElementById('shopName').value = localStorage.getItem('shopName') || '3elegant Store';
      document.getElementById('shopEmail').value = localStorage.getItem('shopEmail') || 'contact@3elegant.com';
      document.getElementById('shopPhone').value = localStorage.getItem('shopPhone') || '1900 1234';
      document.getElementById('shopAddress').value = localStorage.getItem('shopAddress') || '123 Đường ABC, Quận 1, TP.HCM';
      document.getElementById('shippingFee').value = localStorage.getItem('shippingFee') || '15';
    }

    function previewStoreInfo() {
      Swal.fire({
        title: document.getElementById('shopName').value || '3elegant Store',
        html: `
          <div style="text-align:left; line-height:1.8">
            <p><b>Email:</b> ${document.getElementById('shopEmail').value || '--'}</p>
            <p><b>Điện thoại:</b> ${document.getElementById('shopPhone').value || '--'}</p>
            <p><b>Address:</b> ${document.getElementById('shopAddress').value || '--'}</p>
            <p><b>Phí ship:</b> ${document.getElementById('shippingFee').value || '0'} USD</p>
          </div>
        `,
        confirmButtonText: 'Đóng'
      });
    }

    function buildRevenueDataset(days) {
      const labels = [];
      const values = [];
      for (let i = days - 1; i >= 0; i--) {
        const date = new Date('2026-04-05');
        date.setDate(date.getDate() - i);
        const key = date.toISOString().slice(0, 10);
        labels.push(date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }));
        values.push(orders.filter(o => o.date === key && o.status !== 'cancelled').reduce((sum, o) => sum + o.total, 0));
      }
      return { labels, values };
    }

    function updateRevenueChart() {
      const period = Number(document.getElementById('chartPeriod').value || 30);
      const { labels, values } = buildRevenueDataset(period);
      if (!revenueChart) {
        const ctx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: 'Revenue',
              data: values,
              borderColor: '#111827',
              backgroundColor: 'rgba(99,102,241,.10)',
              borderWidth: 3,
              fill: true,
              tension: 0.35,
              pointRadius: 4,
              pointBackgroundColor: '#111827'
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.15)' } },
              x: { grid: { display: false } }
            }
          }
        });
      } else {
        revenueChart.data.labels = labels;
        revenueChart.data.datasets[0].data = values;
        revenueChart.update();
      }
    }

    function updateReportCharts() {
      const categoryMap = {};
      orders.filter(o => o.status !== 'cancelled').forEach(order => {
        order.items.forEach(item => {
          const product = products.find(p => p.name === item.name);
          const category = product?.category || 'Khác';
          categoryMap[category] = (categoryMap[category] || 0) + item.price * item.qty;
        });
      });
      const categoryLabels = Object.keys(categoryMap);
      const categoryValues = Object.values(categoryMap);
      const statusLabels = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
      const statusValues = statusLabels.map(status => orders.filter(o => o.status === status).length);
      const readableStatus = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

      if (!categoryRevenueChart) {
        categoryRevenueChart = new Chart(document.getElementById('categoryRevenueChart').getContext('2d'), {
          type: 'bar',
          data: {
            labels: categoryLabels,
            datasets: [{ label: 'Revenue', data: categoryValues, backgroundColor: ['#111827', '#334155', '#6366f1', '#14b8a6', '#f59e0b'] }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,.15)' } }, x: { grid: { display: false } } }
          }
        });
      } else {
        categoryRevenueChart.data.labels = categoryLabels;
        categoryRevenueChart.data.datasets[0].data = categoryValues;
        categoryRevenueChart.update();
      }

      if (!orderStatusChart) {
        orderStatusChart = new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
          type: 'doughnut',
          data: {
            labels: readableStatus,
            datasets: [{ data: statusValues, backgroundColor: ['#f59e0b', '#6366f1', '#0ea5e9', '#10b981', '#f43f5e'] }]
          },
          options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });
      } else {
        orderStatusChart.data.datasets[0].data = statusValues;
        orderStatusChart.update();
      }
    }

    function setActiveSection(section) {
      currentSection = section;
      document.querySelectorAll('.section').forEach(item => item.classList.remove('active'));
      document.getElementById(section).classList.add('active');
      document.querySelectorAll('.sidebar-link').forEach(btn => btn.classList.remove('active'));
      document.querySelector(`.sidebar-link[data-section="${section}"]`).classList.add('active');

      const titles = {
        dashboard: ['Dashboard', 'A cleaner admin structure for splitting tasks across modules.'],
        categories: ['Category management', 'Separate category CRUD so the catalog module is easier to split for the team.'],
        products: ['Product management', 'Manage products, map them to categories, and review inventory status.'],
        'product-detail': ['Product detail', 'A dedicated admin view for one product, linked orders, and stock notes.'],
        orders: ['Orders', 'Track, filter, and update order status.'],
        inventory: ['Inventory', 'Review stock imports, logs, and low-stock alerts.'],
        customers: ['Customers', 'Hiển thị thông tin, phân loại và tổng chi tiêu.'],
        marketing: ['Coupons', 'Manage discount codes for the marketing module.'],
        reports: ['Reports', 'Revenue charts and order status summaries.'],
        settings: ['Settings', 'Store information saved quickly to localStorage for demo purposes.']
      };
      document.getElementById('pageTitle').innerText = titles[section][0];
      document.getElementById('pageDesc').innerText = titles[section][1];

      if (section === 'dashboard') renderDashboard();
      if (section === 'categories') renderCategories();
      if (section === 'products') renderProducts();
      if (section === 'product-detail') renderProductDetail();
      if (section === 'orders') renderOrders();
      if (section === 'inventory') renderInventory();
      if (section === 'customers') renderCustomers();
      if (section === 'marketing') renderCoupons();
      if (section === 'reports') updateReportCharts();
      if (section === 'settings') loadSettings();
      closeMobileSidebar();
    }

    function handleGlobalSearch() {
      const query = document.getElementById('globalSearch').value.trim().toLowerCase();
      if (!query) {
        Swal.fire('Chưa nhập từ khóa', 'Please enter a product name, customer, or order code.', 'info');
        return;
      }

      const foundProduct = products.find(p => p.name.toLowerCase().includes(query) || p.category.toLowerCase().includes(query));
      const foundOrder = orders.find(o => o.id.toLowerCase().includes(query) || o.customer.toLowerCase().includes(query));
      const foundCustomer = customers.find(c => c.name.toLowerCase().includes(query) || c.email.toLowerCase().includes(query) || c.phone.includes(query));

      if (foundOrder) {
        setActiveSection('orders');
        document.getElementById('orderSearch').value = query;
        renderOrders();
        return;
      }
      if (foundProduct) {
        setActiveSection('products');
        document.getElementById('productSearch').value = query;
        renderProducts();
        return;
      }
      if (foundCustomer) {
        setActiveSection('customers');
        document.getElementById('customerSearch').value = query;
        renderCustomers();
        return;
      }

      Swal.fire('Không tìm thấy', 'No data matched this keyword.', 'warning');
    }

    function exportCurrentSection() {
      if (currentSection === 'products') return exportProducts();
      if (currentSection === 'customers') return exportCustomers();
      if (currentSection === 'reports' || currentSection === 'orders') return exportReport();
      Swal.fire('Gợi ý', 'Bạn đang ở khu không cần export riêng. Hãy chuyển sang Products, Customers hoặc Reports.', 'info');
    }

    function openMobileSidebar() {
      document.getElementById('sidebar').classList.remove('-translate-x-full');
      document.getElementById('mobileOverlay').classList.remove('hidden');
    }

    function closeMobileSidebar() {
      document.getElementById('sidebar').classList.add('-translate-x-full');
      document.getElementById('mobileOverlay').classList.add('hidden');
    }

    function resetDemoData() {
      Swal.fire({
        title: 'Reset dữ liệu demo?',
        text: 'Sẽ đưa toàn bộ products, orders, customers, coupons và inventory về trạng thái mẫu ban đầu.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Reset',
        cancelButtonText: 'Cancel'
      }).then(res => {
        if (!res.isConfirmed) return;
        categories = structuredClone(demoSeed.categories);
        products = structuredClone(demoSeed.products);
        orders = structuredClone(demoSeed.orders);
        customers = structuredClone(demoSeed.customers);
        coupons = structuredClone(demoSeed.coupons);
        inventoryLogs = structuredClone(demoSeed.inventoryLogs);
        persistData();
        rerenderAll();
        Swal.fire('Đã reset', 'Dữ liệu demo đã quay về bản mẫu.', 'success');
      });
    }

    function rerenderAll() {
      renderCategories();
      renderProducts();
      renderOrders();
      renderCustomers();
      renderCoupons();
      renderInventory();
      renderDashboard();
      updateReportCharts();
    }

    window.onload = () => {
      loadSettings();
      rerenderAll();
      setActiveSection('dashboard');
      document.getElementById('globalSearch').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') handleGlobalSearch();
      });
    };
  </script>
</body>
</html>
