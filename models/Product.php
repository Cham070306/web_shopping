<?php
class Product {
    private $conn;
    private $table = "products";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả sản phẩm
    public function getAll() {
        $query = "SELECT p.*, c.name as category_name 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  ORDER BY p.id DESC";
        $result = $this->conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Lấy 1 sản phẩm theo ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Lấy các sản phẩm nổi bật
    public function getFeatured($limit = 4) {
        $query = "SELECT * FROM {$this->table} WHERE is_active = 1 AND is_featured = 1 ORDER BY id DESC LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Giảm số lượng tồn kho và tăng số lượng đã bán
    public function updateStockAfterSale($id, $quantity) {
        $query = "UPDATE {$this->table} SET stock = stock - ?, sold = sold + ? WHERE id = ? AND stock >= ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iiii", $quantity, $quantity, $id, $quantity);
        return $stmt->execute();
    }

    // Tạo sản phẩm mới
    public function create($data) {
        $query = "INSERT INTO {$this->table} 
                  (category_id, name, slug, sku, description, short_desc, price, sale_price, stock, sold, thumbnail, meta_title, meta_description, is_featured, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                  
        $stmt = $this->conn->prepare($query);
        
        $category_id = !empty($data['category_id']) ? $data['category_id'] : null;
        $name = $data['name'];
        $slug = $data['slug'];
        $sku = !empty($data['sku']) ? $data['sku'] : null;
        $description = !empty($data['description']) ? $data['description'] : null;
        $short_desc = !empty($data['short_desc']) ? $data['short_desc'] : null;
        $price = $data['price'];
        $sale_price = !empty($data['sale_price']) ? $data['sale_price'] : null;
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        $sold = isset($data['sold']) ? (int)$data['sold'] : 0;
        $thumbnail = !empty($data['thumbnail']) ? $data['thumbnail'] : null;
        $meta_title = !empty($data['meta_title']) ? $data['meta_title'] : null;
        $meta_description = !empty($data['meta_description']) ? $data['meta_description'] : null;
        $is_featured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("isssssddiisssii", 
            $category_id, $name, $slug, $sku, $description, $short_desc, 
            $price, $sale_price, $stock, $sold, $thumbnail, 
            $meta_title, $meta_description, $is_featured, $is_active
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // Cập nhật sản phẩm
    public function update($id, $data) {
        $query = "UPDATE {$this->table} 
                  SET category_id = ?, name = ?, slug = ?, sku = ?, description = ?, short_desc = ?, 
                      price = ?, sale_price = ?, stock = ?, thumbnail = ?, 
                      meta_title = ?, meta_description = ?, is_featured = ?, is_active = ? 
                  WHERE id = ?";
                  
        $stmt = $this->conn->prepare($query);
        
        $category_id = !empty($data['category_id']) ? $data['category_id'] : null;
        $name = $data['name'];
        $slug = $data['slug'];
        $sku = !empty($data['sku']) ? $data['sku'] : null;
        $description = !empty($data['description']) ? $data['description'] : null;
        $short_desc = !empty($data['short_desc']) ? $data['short_desc'] : null;
        $price = $data['price'];
        $sale_price = !empty($data['sale_price']) ? $data['sale_price'] : null;
        $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
        // Sold usually isn't updated directly via forms
        $thumbnail = !empty($data['thumbnail']) ? $data['thumbnail'] : null;
        $meta_title = !empty($data['meta_title']) ? $data['meta_title'] : null;
        $meta_description = !empty($data['meta_description']) ? $data['meta_description'] : null;
        $is_featured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
        $is_active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("isssssddisssiii", 
            $category_id, $name, $slug, $sku, $description, $short_desc, 
            $price, $sale_price, $stock, $thumbnail, 
            $meta_title, $meta_description, $is_featured, $is_active, $id
        );
        
        return $stmt->execute();
    }

    // Xoá sản phẩm
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // --- PRODUCT IMAGES (GALLERY) ---
    
    // Thêm nhiều ảnh phụ
    public function addImages($product_id, $images) {
        if (empty($images)) return true;
        
        $query = "INSERT INTO product_images (product_id, image_url, sort_order) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        
        $success = true;
        $sort = 0;
        foreach ($images as $img) {
            $stmt->bind_param("isi", $product_id, $img, $sort);
            if (!$stmt->execute()) {
                $success = false;
            }
            $sort++;
        }
        return $success;
    }

    // Lấy danh sách ảnh phụ
    public function getImages($product_id) {
        $query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC, id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Xoá 1 ảnh phụ
    public function deleteImage($image_id) {
        $query = "DELETE FROM product_images WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $image_id);
        return $stmt->execute();
    }
}
?>
