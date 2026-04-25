<?php
class Coupon
{
    private $conn;
    private $table = "coupons";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAll($search = '')
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " AND (code LIKE ? OR description LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($sql);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (code, description, type, value, min_order, max_discount, max_uses, starts_at, expires_at, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        $code = strtoupper(trim($data['code']));
        $description = trim($data['description']);
        $type = $data['type'];
        $value = (float)$data['value'];
        $min_order = (float)$data['min_order'];
        $max_discount = $data['max_discount'] !== '' ? (float)$data['max_discount'] : null;
        $max_uses = (int)$data['max_uses'];
        $starts_at = !empty($data['starts_at']) ? $data['starts_at'] : null;
        $expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
        $is_active = !empty($data['is_active']) ? 1 : 0;

        $stmt->bind_param(
            "sssdddiisi",
            $code,
            $description,
            $type,
            $value,
            $min_order,
            $max_discount,
            $max_uses,
            $starts_at,
            $expires_at,
            $is_active
        );

        return $stmt->execute();
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $id);
        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>