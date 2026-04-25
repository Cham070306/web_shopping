<?php

class ReportController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getDashboardStats()
    {
        $stats = [];

        // revenue
        $result = $this->conn->query("
            SELECT COALESCE(SUM(total),0) as total_revenue 
            FROM orders
            WHERE status != 'cancelled'
        ");
        $stats['revenue'] = $result->fetch_assoc()['total_revenue'];

        // orders
        $result = $this->conn->query("
            SELECT COUNT(*) as total_orders 
            FROM orders
        ");
        $stats['orders'] = $result->fetch_assoc()['total_orders'];

        // customers
        $result = $this->conn->query("
            SELECT COUNT(*) as total_customers 
            FROM users
            WHERE role='user'
        ");
        $stats['customers'] = $result->fetch_assoc()['total_customers'];

        // products
        $result = $this->conn->query("
            SELECT COUNT(*) as total_products 
            FROM products
        ");
        $stats['products'] = $result->fetch_assoc()['total_products'];

        // reviews
        $result = $this->conn->query("
            SELECT COUNT(*) as total_reviews 
            FROM reviews
        ");
        $stats['reviews'] = $result->fetch_assoc()['total_reviews'];

        // contacts
        $result = $this->conn->query("
            SELECT COUNT(*) as total_contacts 
            FROM contacts
        ");
        $stats['contacts'] = $result->fetch_assoc()['total_contacts'];

        return $stats;

    }

    public function getTopSellingProducts()
    {
        $sql = "
            SELECT 
                p.id,
                p.name,
                SUM(oi.quantity) as total_sold
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT 5
        ";

        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRecentOrders()
    {
        $sql = "
            SELECT 
                o.order_code,
                o.total,
                o.status,
                o.created_at,
                u.name as customer_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ";

        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    public function getOrderStatusStats()
    {
    $sql = "
        SELECT status, COUNT(*) as total
        FROM orders
        GROUP BY status
    ";

    $result = $this->conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
public function getTopSellingCategories()
{
    $sql = "
        SELECT 
            c.name,
            SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN categories c ON p.category_id = c.id
        GROUP BY c.id, c.name
        ORDER BY total_sold DESC
        LIMIT 6
    ";

    $result = $this->conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
}