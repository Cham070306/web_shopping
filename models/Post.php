<?php
class Post
{
    private $conn;
    private $table = "posts";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // =========================
    // USER
    // =========================

    public function getPublishedPosts($search = '', $categoryId = '', $limit = 6, $offset = 0)
    {
        $sql = "SELECT p.*, pc.name AS category_name, u.name AS author_name
                FROM {$this->table} p
                LEFT JOIN post_categories pc ON p.category_id = pc.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.is_published = 1";

        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        if ($categoryId !== '') {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$categoryId;
            $types .= 'i';
        }

        $sql .= " ORDER BY p.published_at DESC, p.id DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countPublishedPosts($search = '', $categoryId = '')
    {
        $sql = "SELECT COUNT(*) AS total
                FROM {$this->table} p
                WHERE p.is_published = 1";

        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " AND (p.title LIKE ? OR p.excerpt LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        if ($categoryId !== '') {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$categoryId;
            $types .= 'i';
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    public function getPublishedPostBySlug($slug)
    {
        $sql = "SELECT p.*, pc.name AS category_name, u.name AS author_name
                FROM {$this->table} p
                LEFT JOIN post_categories pc ON p.category_id = pc.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.slug = ? AND p.is_published = 1
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $slug);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function increaseViews($id)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET views = views + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getRelatedPosts($categoryId, $currentId, $limit = 3)
    {
        $sql = "SELECT p.*, pc.name AS category_name
                FROM {$this->table} p
                LEFT JOIN post_categories pc ON p.category_id = pc.id
                WHERE p.is_published = 1
                  AND p.category_id = ?
                  AND p.id <> ?
                ORDER BY p.published_at DESC, p.id DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $categoryId, $currentId, $limit);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategories()
    {
        $sql = "SELECT * FROM post_categories ORDER BY name ASC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // =========================
    // ADMIN
    // =========================

    public function getAllPosts($search = '', $status = '', $limit = 10, $offset = 0)
    {
        $sql = "SELECT p.*, pc.name AS category_name, u.name AS author_name
                FROM {$this->table} p
                LEFT JOIN post_categories pc ON p.category_id = pc.id
                LEFT JOIN users u ON p.author_id = u.id
                WHERE 1=1";

        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " AND (p.title LIKE ? OR p.slug LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        if ($status !== '') {
            $sql .= " AND p.is_published = ?";
            $params[] = (int)$status;
            $types .= 'i';
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= 'ii';

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAllPosts($search = '', $status = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table} p WHERE 1=1";

        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " AND (p.title LIKE ? OR p.slug LIKE ?)";
            $like = "%{$search}%";
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        if ($status !== '') {
            $sql .= " AND p.is_published = ?";
            $params[] = (int)$status;
            $types .= 'i';
        }

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $row = $stmt->get_result()->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data)
{
    $sql = "INSERT INTO posts 
        (category_id, author_id, title, slug, thumbnail, meta_title, excerpt, meta_description, content, is_published, published_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $this->conn->prepare($sql);

            $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
            $author_id = (int)$data['author_id'];
            $title = $data['title'];
            $slug = $data['slug'];
            $thumbnail = $data['thumbnail'];
            $meta_title = $data['meta_title'];
            $excerpt = $data['excerpt'];
            $meta_description = $data['meta_description'];
            $content = $data['content'];
            $is_published = (int)$data['is_published'];

            $stmt->bind_param(
                "iisssssssi",
                $category_id,
                $author_id,
                $title,
                $slug,
                $thumbnail,
                $meta_title,
                $excerpt,
                $meta_description,
                $content,
                $is_published
            );

            return $stmt->execute();
        }

    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET category_id = ?, title = ?, slug = ?, thumbnail = ?, meta_title = ?, excerpt = ?, meta_description = ?, content = ?, is_published = ?, published_at = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);

        $category_id = !empty($data['category_id']) ? (int)$data['category_id'] : null;
        $title = trim($data['title'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $thumbnail = trim($data['thumbnail'] ?? '');
        $meta_title = trim($data['meta_title'] ?? '');
        $excerpt = trim($data['excerpt'] ?? '');
        $meta_description = trim($data['meta_description'] ?? '');
        $content = trim($data['content'] ?? '');
        $is_published = !empty($data['is_published']) ? 1 : 0;
        $published_at = $is_published ? ($data['published_at'] ?? date('Y-m-d H:i:s')) : null;

        $stmt->bind_param(
            "isssssssisi",
            $category_id,
            $title,
            $slug,
            $thumbnail,
            $meta_title,
            $excerpt,
            $meta_description,
            $content,
            $is_published,
            $published_at,
            $id
        );

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function slugExists($slug, $excludeId = 0)
    {
        if ($excludeId > 0) {
            $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE slug = ? AND id <> ?");
            $stmt->bind_param("si", $slug, $excludeId);
        } else {
            $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE slug = ?");
            $stmt->bind_param("s", $slug);
        }

        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
    public function getAll()
{
    $sql = "SELECT * FROM posts ORDER BY id DESC";
    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}
    

}
?>