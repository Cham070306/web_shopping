<?php

class OrderDetail
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Thêm 1 dòng order item
     * $item = ['product_id', 'product_name', 'variant', 'price', 'quantity', 'subtotal', 'thumbnail']
     */
    public function addItem($order_id, $item): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO order_items
                (order_id, product_id, product_name, product_sku, variant,
                 price, quantity, subtotal, thumbnail)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $product_sku = $item['product_sku'] ?? null;
        $variant     = $item['variant'] ?? null;

        $stmt->bind_param(
            'iisssdids',
            $order_id,
            $item['product_id'],
            $item['product_name'],
            $product_sku,
            $variant,
            $item['price'],
            $item['quantity'],
            $item['subtotal'],
            $item['thumbnail']
        );

        return $stmt->execute();
    }

    /**
     * Lấy tất cả items của 1 đơn hàng
     */
    public function getItemsByOrderId($order_id): array
    {
        $stmt = $this->conn->prepare(
            "SELECT oi.*, p.slug
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             ORDER BY oi.id ASC"
        );
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Đếm số dòng items trong 1 đơn hàng
     */
    public function getItemCountByOrderId($order_id): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) AS cnt FROM order_items WHERE order_id = ?"
        );
        $stmt->bind_param('i', $order_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Giảm stock + tăng sold sau khi đặt hàng thành công
     * Return false nếu không đủ stock
     */
    public function decreaseStock($product_id, $quantity): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE products
             SET stock = stock - ?, sold = sold + ?
             WHERE id = ? AND stock >= ?"
        );
        $stmt->bind_param('iiii', $quantity, $quantity, $product_id, $quantity);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }
}
