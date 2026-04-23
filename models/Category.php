<?php
class Category {
    private $conn;
    private $table = "categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả danh mục
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY sort_order ASC, id DESC";
        $result = $this->conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Lấy 1 danh mục theo ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Tạo danh mục mới
    public function create($data) {
        $name = $data['name'];
        $slug = $data['slug'];
        $parent_id = !empty($data['parent_id']) ? $data['parent_id'] : null;
        $image = !empty($data['image']) ? $data['image'] : null;
        $description = !empty($data['description']) ? $data['description'] : null;
        $sort_order = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $query = "INSERT INTO {$this->table} (name, slug, parent_id, image, description, sort_order, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssissii", $name, $slug, $parent_id, $image, $description, $sort_order, $is_active);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Cập nhật danh mục
    public function update($id, $data) {
        $name = $data['name'];
        $slug = $data['slug'];
        $parent_id = !empty($data['parent_id']) ? $data['parent_id'] : null;
        $image = !empty($data['image']) ? $data['image'] : null;
        $description = !empty($data['description']) ? $data['description'] : null;
        $sort_order = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        // Nếu người dùng không upload ảnh mới thì giữ nguyên ảnh cũ, ta cần logic nhỏ ở controller. 
        // Trong model này ta giả định $data['image'] là ảnh đã chốt.
        $query = "UPDATE {$this->table} 
                  SET name = ?, slug = ?, parent_id = ?, image = ?, description = ?, sort_order = ?, is_active = ? 
                  WHERE id = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssissiii", $name, $slug, $parent_id, $image, $description, $sort_order, $is_active, $id);
        
        return $stmt->execute();
    }

    // Xoá danh mục
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
