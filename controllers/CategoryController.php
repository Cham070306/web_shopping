<?php
session_start();
require_once "../config/database.php";
require_once "../models/Category.php";

$_user = $_SESSION['user'] ?? [];
if (empty($_user) || ($_user['role'] ?? '') !== 'admin') {
    die("Access denied");
}

$categoryModel = new Category($conn);
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'image' => ''
    ];

    if (empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
    }

    if ($categoryModel->create($data)) {
        $_SESSION['success'] = "Danh mục đã được tạo thành công.";
    } else {
        $_SESSION['error'] = "Lỗi khi tạo danh mục.";
    }
    header("Location: ../admin/categories/index.php");
    exit;
}

if ($action === 'update') {
    $id = $_POST['id'] ?? 0;
    
    // Get existing to prevent overwriting image with blank if not handled yet
    $current = $categoryModel->getById($id);
    if (!$current) {
        die("Category not found");
    }

    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'slug' => trim($_POST['slug'] ?? ''),
        'sort_order' => $_POST['sort_order'] ?? 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'image' => $current['image'] 
    ];

    if (empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['name'])));
    }

    if ($categoryModel->update($id, $data)) {
        $_SESSION['success'] = "Danh mục đã được cập nhật.";
    } else {
        $_SESSION['error'] = "Lỗi khi cập nhật danh mục.";
    }
    header("Location: ../admin/categories/index.php");
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? 0;
    
    if ($categoryModel->delete($id)) {
        $_SESSION['success'] = "Đã xoá danh mục thành công.";
    } else {
        $_SESSION['error'] = "Không thể xoá danh mục này.";
    }
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

// Fallback
header("Location: ../admin/categories/index.php");
exit;
