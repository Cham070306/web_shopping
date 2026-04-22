<?php
class Review
{
    private $conn;
    private $table = "reviews";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($product_id, $user_id, $rating, $comment, $is_approved = 1)
    {
        $sql = "INSERT INTO {$this->table} (product_id, user_id, rating, comment, is_approved, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisi", $product_id, $user_id, $rating, $comment, $is_approved);
        return $stmt->execute();
    }

    public function getByProductId($product_id)
    {
        $sql = "SELECT r.*, u.name AS user_name, u.avatar AS user_avatar
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.is_approved = 1
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getTotalByProductId($product_id)
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table}
                WHERE product_id = ? AND is_approved = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return (int)($result['total'] ?? 0);
    }

    public function getAverageRatingByProductId($product_id)
    {
        $sql = "SELECT AVG(rating) AS avg_rating
                FROM {$this->table}
                WHERE product_id = ? AND is_approved = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return round((float)($result['avg_rating'] ?? 0), 1);
    }

    public function hasUserReviewed($product_id, $user_id)
    {
        $sql = "SELECT id
                FROM {$this->table}
                WHERE product_id = ? AND user_id = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    public function getAll()
    {
        $sql = "SELECT r.*, p.name AS product_name, u.name AS user_name
                FROM {$this->table} r
                INNER JOIN products p ON r.product_id = p.id
                INNER JOIN users u ON r.user_id = u.id
                ORDER BY r.created_at DESC";
        return $this->conn->query($sql);
    }

    public function approve($id)
    {
        $sql = "UPDATE {$this->table} SET is_approved = 1 WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}