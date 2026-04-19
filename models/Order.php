<?php

class Order
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* ================================================================
     *  CART — lưu giỏ hàng vào bảng `cart`
     * ================================================================ */

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng
     */
    public function getCartItems($user_id = null, $session_id = null): array
    {
        $sql = "SELECT c.id, c.product_id, c.variant_id, c.quantity,
                       p.name, p.thumbnail, p.price, p.sale_price, p.stock, p.slug,
                       v.color, v.size, v.price_diff, v.stock AS variant_stock
                FROM cart c
                JOIN products p ON c.product_id = p.id
                LEFT JOIN product_variants v ON c.variant_id = v.id
                WHERE ";

        $params = [];
        $types  = '';

        if ($user_id) {
            $sql   .= "c.user_id = ?";
            $params[] = $user_id;
            $types .= 'i';
        } else {
            $sql   .= "c.session_id = ?";
            $params[] = $session_id;
            $types .= 's';
        }

        $sql .= " ORDER BY c.added_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Thêm sản phẩm vào giỏ — nếu đã có thì cộng dồn quantity
     */
    public function addToCart($user_id, $session_id, $product_id, $variant_id, $quantity): bool
    {
        // Kiểm tra đã tồn tại chưa
        $where  = $user_id ? "user_id = ?" : "session_id = ?";
        $bind   = $user_id ?: $session_id;
        $btype  = $user_id ? 'i' : 's';

        if ($variant_id) {
            $check = $this->conn->prepare(
                "SELECT id, quantity FROM cart WHERE $where AND product_id = ? AND variant_id = ?"
            );
            $check->bind_param($btype . 'ii', $bind, $product_id, $variant_id);
        } else {
            $check = $this->conn->prepare(
                "SELECT id, quantity FROM cart WHERE $where AND product_id = ? AND variant_id IS NULL"
            );
            $check->bind_param($btype . 'i', $bind, $product_id);
        }

        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            $newQty = $existing['quantity'] + $quantity;
            $update = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param('ii', $newQty, $existing['id']);
            return $update->execute();
        }

        // Chèn mới
        $stmt = $this->conn->prepare(
            "INSERT INTO cart (user_id, session_id, product_id, variant_id, quantity) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('isiii', $user_id, $session_id, $product_id, $variant_id, $quantity);
        return $stmt->execute();
    }

    /**
     * Cập nhật số lượng 1 dòng cart
     */
    public function updateCartQty($cart_id, $quantity): bool
    {
        $stmt = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param('ii', $quantity, $cart_id);
        return $stmt->execute();
    }

    /**
     * Xoá 1 dòng khỏi giỏ
     */
    public function removeCartItem($cart_id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->bind_param('i', $cart_id);
        return $stmt->execute();
    }

    /**
     * Xoá toàn bộ giỏ hàng (sau khi đặt hàng thành công)
     */
    public function clearCart($user_id = null, $session_id = null): bool
    {
        if ($user_id) {
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
        } else {
            $stmt = $this->conn->prepare("DELETE FROM cart WHERE session_id = ?");
            $stmt->bind_param('s', $session_id);
        }
        return $stmt->execute();
    }

    /**
     * Đếm tổng số lượng sản phẩm trong giỏ (dùng cho badge trên navbar)
     */
    public function getCartCount($user_id = null, $session_id = null): int
    {
        if ($user_id) {
            $stmt = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS cnt FROM cart WHERE user_id = ?");
            $stmt->bind_param('i', $user_id);
        } else {
            $stmt = $this->conn->prepare("SELECT COALESCE(SUM(quantity), 0) AS cnt FROM cart WHERE session_id = ?");
            $stmt->bind_param('s', $session_id);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Gộp giỏ hàng khách (session) vào tài khoản khi login
     */
    public function mergeGuestCart($session_id, $user_id): void
    {
        // Lấy cart items của guest
        $guestItems = $this->conn->prepare("SELECT id, product_id, variant_id, quantity FROM cart WHERE session_id = ?");
        $guestItems->bind_param('s', $session_id);
        $guestItems->execute();
        $items = $guestItems->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($items as $item) {
            // Kiểm tra user đã có sản phẩm này chưa
            if ($item['variant_id']) {
                $check = $this->conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ?");
                $check->bind_param('iii', $user_id, $item['product_id'], $item['variant_id']);
            } else {
                $check = $this->conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND variant_id IS NULL");
                $check->bind_param('ii', $user_id, $item['product_id']);
            }
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();

            if ($existing) {
                // Cộng dồn
                $newQty = $existing['quantity'] + $item['quantity'];
                $update = $this->conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $update->bind_param('ii', $newQty, $existing['id']);
                $update->execute();
                // Xoá dòng guest
                $del = $this->conn->prepare("DELETE FROM cart WHERE id = ?");
                $del->bind_param('i', $item['id']);
                $del->execute();
            } else {
                // Gán user_id, xoá session_id
                $move = $this->conn->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE id = ?");
                $move->bind_param('ii', $user_id, $item['id']);
                $move->execute();
            }
        }
    }

    /* ================================================================
     *  COUPON
     * ================================================================ */

    /**
     * Validate mã giảm giá
     */
    public function validateCoupon($code, $subtotal)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coupons WHERE code = ? AND is_active = 1"
        );
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();

        if (!$coupon) return false;

        // Kiểm tra hết hạn
        if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
            return false;
        }

        // Kiểm tra chưa bắt đầu
        if ($coupon['starts_at'] && strtotime($coupon['starts_at']) > time()) {
            return false;
        }

        // Kiểm tra lượt sử dụng
        if ($coupon['max_uses'] > 0 && $coupon['used_count'] >= $coupon['max_uses']) {
            return false;
        }

        // Kiểm tra đơn tối thiểu
        if ($subtotal < (float) $coupon['min_order']) {
            return false;
        }

        return $coupon;
    }

    /* ================================================================
     *  ORDER — tạo và quản lý đơn hàng
     * ================================================================ */

    /**
     * Sinh mã đơn hàng unique
     */
    public function generateOrderCode(): string
    {
        return 'ORD' . strtoupper(uniqid());
    }

    /**
     * Tạo đơn hàng mới, trả về order id
     */
    public function createOrder($data)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO orders
                (user_id, order_code, full_name, email, phone, address, city, note,
                 subtotal, discount, shipping_fee, total, coupon_code,
                 payment_method, payment_status, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $payment_status = $data['payment_status'] ?? 'pending';
        $status         = $data['status'] ?? 'pending';

        $stmt->bind_param(
            'isssssssddddssss',
            $data['user_id'],
            $data['order_code'],
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['note'],
            $data['subtotal'],
            $data['discount'],
            $data['shipping_fee'],
            $data['total'],
            $data['coupon_code'],
            $data['payment_method'],
            $payment_status,
            $status
        );

        if ($stmt->execute()) {
            // Tăng used_count của coupon nếu có
            if (!empty($data['coupon_code'])) {
                $upCoupon = $this->conn->prepare(
                    "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?"
                );
                $upCoupon->bind_param('s', $data['coupon_code']);
                $upCoupon->execute();
            }
            return $this->conn->insert_id;
        }

        return false;
    }

    /**
     * Lấy đơn hàng theo ID
     */
    public function getOrderById($id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Lấy đơn hàng theo mã code
     */
    public function getOrderByCode($code): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE order_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Lấy danh sách đơn hàng của 1 user
     */
    public function getOrdersByUser($user_id, $status = ''): array
    {
        $sql = "SELECT o.*, COUNT(oi.id) AS item_count
                FROM orders o
                LEFT JOIN order_items oi ON oi.order_id = o.id
                WHERE o.user_id = ?";
        $types  = 'i';
        $params = [$user_id];

        if ($status) {
            $sql .= " AND o.status = ?";
            $types  .= 's';
            $params[] = $status;
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Huỷ đơn hàng (chỉ cho phép khi status = pending)
     */
    public function cancelOrder($order_id, $user_id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE orders SET status = 'cancelled'
             WHERE id = ? AND user_id = ? AND status = 'pending'"
        );
        $stmt->bind_param('ii', $order_id, $user_id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    /* ================================================================
     *  ADMIN — quản lý đơn hàng
     * ================================================================ */

    /**
     * Lấy danh sách đơn hàng (admin) có filter, phân trang
     */
    public function getAllOrders($filters = [], $limit = 15, $offset = 0): array
    {
        $sql    = "SELECT o.*, COUNT(oi.id) AS item_count
                   FROM orders o
                   LEFT JOIN order_items oi ON oi.order_id = o.id
                   WHERE 1=1";
        $types  = '';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND o.status = ?";
            $types  .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (o.order_code LIKE ? OR o.full_name LIKE ? OR o.email LIKE ?)";
            $types  .= 'sss';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
        $types  .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Đếm tổng đơn hàng (admin) theo filter
     */
    public function countAllOrders($filters = []): int
    {
        $sql    = "SELECT COUNT(*) AS cnt FROM orders WHERE 1=1";
        $types  = '';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $types  .= 's';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $sql .= " AND (order_code LIKE ? OR full_name LIKE ? OR email LIKE ?)";
            $types  .= 'sss';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $stmt = $this->conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Cập nhật trạng thái đơn hàng (admin)
     */
    public function updateOrderStatus($id, $status): bool
    {
        $sql = "UPDATE orders SET status = ?";

        // Nếu chuyển sang delivered → ghi delivered_at
        if ($status === 'delivered') {
            $sql .= ", delivered_at = NOW()";
        }

        $sql .= " WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    /**
     * Cập nhật trạng thái thanh toán (admin)
     */
    public function updatePaymentStatus($id, $payment_status): bool
    {
        $stmt = $this->conn->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->bind_param('si', $payment_status, $id);
        return $stmt->execute();
    }

    /**
     * Lấy đơn hàng gần nhất (dashboard widget)
     */
    public function getRecentOrders($limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT o.*, COUNT(oi.id) AS item_count
             FROM orders o
             LEFT JOIN order_items oi ON oi.order_id = o.id
             GROUP BY o.id
             ORDER BY o.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /* ================================================================
     *  INVENTORY — quản lý tồn kho (admin)
     * ================================================================ */

    /**
     * Lấy sản phẩm sắp hết hàng
     */
    public function getLowStockProducts($threshold = 10): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, name, sku, stock, sold, thumbnail
             FROM products
             WHERE stock <= ? AND is_active = 1
             ORDER BY stock ASC"
        );
        $stmt->bind_param('i', $threshold);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Danh sách kho hàng (admin inventory page) với tìm kiếm + phân trang
     */
    public function getInventoryList($search = '', $category_id = 0, $limit = 20, $offset = 0): array
    {
        $sql = "SELECT p.id, p.name, p.sku, p.stock, p.sold, p.price, p.thumbnail, p.is_active,
                       c.name AS category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        $types  = '';
        $params = [];

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $types  .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if ($category_id > 0) {
            $sql .= " AND p.category_id = ?";
            $types  .= 'i';
            $params[] = $category_id;
        }

        $sql .= " ORDER BY p.stock ASC LIMIT ? OFFSET ?";
        $types  .= 'ii';
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Đếm tổng sản phẩm kho hàng
     */
    public function countInventory($search = '', $category_id = 0): int
    {
        $sql = "SELECT COUNT(*) AS cnt FROM products p WHERE 1=1";
        $types  = '';
        $params = [];

        if ($search) {
            $like = '%' . $search . '%';
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $types  .= 'ss';
            $params[] = $like;
            $params[] = $like;
        }

        if ($category_id > 0) {
            $sql .= " AND p.category_id = ?";
            $types  .= 'i';
            $params[] = $category_id;
        }

        $stmt = $this->conn->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Cập nhật stock sản phẩm
     */
    public function updateStock($product_id, $new_stock): bool
    {
        $stmt = $this->conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt->bind_param('ii', $new_stock, $product_id);
        return $stmt->execute();
    }

    /* ================================================================
     *  STATS — thống kê cho dashboard / reports
     * ================================================================ */

    /**
     * Tổng doanh thu (không tính đơn cancelled)
     */
    public function getAdminDashboardStats(): array
    {
        $stats = [
            'total_orders' => 0,
            'revenue' => 0,
            'pending_orders' => 0,
            'total_customers' => 0
        ];

        // Total orders
        $res = $this->conn->query("SELECT COUNT(*) as c FROM orders");
        if ($res) {
            $stats['total_orders'] = (int)$res->fetch_assoc()['c'];
        }

        // Revenue (Completed/Delivered orders)
        $res = $this->conn->query("SELECT SUM(total) as s FROM orders WHERE status IN ('delivered', 'confirmed', 'shipping')");
        if ($res) {
            $stats['revenue'] = (float)$res->fetch_assoc()['s'];
        }

        // Pending orders
        $res = $this->conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'");
        if ($res) {
            $stats['pending_orders'] = (int)$res->fetch_assoc()['c'];
        }

        // Total users (customers)
        $res = $this->conn->query("SELECT COUNT(DISTINCT user_id) as c FROM orders WHERE user_id IS NOT NULL");
        if ($res) {
            $stats['total_customers'] = (int)$res->fetch_assoc()['c'];
        }

        return $stats;
    }

    public function getTotalRevenue(): float
    {
        $result = $this->conn->query(
            "SELECT COALESCE(SUM(total), 0) AS revenue
             FROM orders
             WHERE status != 'cancelled'"
        );
        $row = $result->fetch_assoc();
        return (float) ($row['revenue'] ?? 0);
    }

    /**
     * Tổng đơn hàng hôm nay
     */
    public function getTodayOrders(): int
    {
        $result = $this->conn->query(
            "SELECT COUNT(*) AS cnt FROM orders WHERE DATE(created_at) = CURDATE()"
        );
        $row = $result->fetch_assoc();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Doanh thu theo ngày (cho biểu đồ)
     */
    public function getRevenueByDate($days = 30): array
    {
        $stmt = $this->conn->prepare(
            "SELECT DATE(created_at) AS date, SUM(total) AS revenue
             FROM orders
             WHERE status != 'cancelled'
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC"
        );
        $stmt->bind_param('i', $days);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Sản phẩm bán chạy nhất
     */
    public function getTopProducts($limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.name, p.sold, p.thumbnail,
                    COALESCE(SUM(oi.subtotal), 0) AS revenue
             FROM products p
             LEFT JOIN order_items oi ON oi.product_id = p.id
             LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY p.sold DESC
             LIMIT ?"
        );
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
