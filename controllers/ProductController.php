<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "../config/database.php";

$action = $_GET['action'] ?? '';

// Check if user is logged in for wishlist actions
$user_id = $_SESSION['user']['id'] ?? null;

if ($action === 'remove_wishlist') {
    if (!$user_id) {
        if (isset($_POST['ajax'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        $_SESSION['error'] = 'Please log in to manage your wishlist.';
        header('Location: ../user/login.php');
        exit;
    }

    $wishlist_id = $_POST['wishlist_id'] ?? null;

    if ($wishlist_id) {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $wishlist_id, $user_id);
        $stmt->execute();

        if (isset($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        }

        $_SESSION['success'] = 'Item removed from wishlist successfully.';
    }

    header('Location: ../user/wishlist.php');
    exit;
}

if ($action === 'ajax_toggle_wishlist') {
    header('Content-Type: application/json');
    if (!$user_id) {
        echo json_encode(['success' => false, 'need_login' => true, 'message' => 'Please log in to manage your wishlist.']);
        exit;
    }

    $product_id = $_POST['product_id'] ?? null;
    if ($product_id) {
        $check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            // Remove
            $del_stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $del_stmt->bind_param("ii", $user_id, $product_id);
            $del_stmt->execute();
            echo json_encode(['success' => true, 'is_wished' => false]);
            exit;
        } else {
            // Add
            $insert_stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $insert_stmt->bind_param("ii", $user_id, $product_id);
            $insert_stmt->execute();
            echo json_encode(['success' => true, 'is_wished' => true]);
            exit;
        }
    }
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

// Add to wishlist logic
if ($action === 'add_wishlist') {
    if (!$user_id) {
        $_SESSION['error'] = 'Please log in to add items to your wishlist.';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../user/shop.php'));
        exit;
    }
    $product_id = $_POST['product_id'] ?? null;
    if ($product_id) {
        $check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['success'] = 'Product is already in your wishlist.';
        } else {
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $product_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Added to wishlist successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add to wishlist.';
            }
        }
    }
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../user/wishlist.php'));
    exit;
}

// ==========================================
// ADMIN ACTIONS
// ==========================================
$admin_action = $_POST['action'] ?? '';

if ($admin_action === 'create_product') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        die('Unauthorized');
    }
    require_once "../models/Product.php";
    $productModel = new Product($conn);

    $thumbnail = '';
    // Handle image upload
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['thumbnail']['tmp_name'];
        $file_name = time() . '_' . $_FILES['thumbnail']['name'];
        $upload_dir = '../assets/product-images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $thumbnail = $file_name;
        }
    }

    $data = [
        'category_id' => $_POST['category_id'] ?? null,
        'name' => trim($_POST['name'] ?? ''),
        'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'] ?? ''))),
        'sku' => !empty(trim($_POST['sku'] ?? '')) ? trim($_POST['sku']) : strtoupper(uniqid('PRD-')),
        'price' => $_POST['price'] ?? 0,
        'sale_price' => !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
        'stock' => $_POST['stock'] ?? 0,
        'thumbnail' => $thumbnail,
        'description' => trim($_POST['description'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    $product_id = $productModel->create($data);
    if ($product_id) {
        // Handle gallery images
        $gallery_images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $total_files = count($_FILES['images']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['images']['tmp_name'][$i];
                    $img_name = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                    if (move_uploaded_file($tmp_name, $upload_dir . $img_name)) {
                        $gallery_images[] = $img_name;
                    }
                }
            }
        }
        if (!empty($gallery_images)) {
            $productModel->addImages($product_id, $gallery_images);
        }

        $_SESSION['success'] = "Product added successfully!";
    } else {
        $_SESSION['error'] = "Error adding product.";
    }
    header("Location: ../admin/products/index.php");
    exit;
}

if ($admin_action === 'delete_product') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        die('Unauthorized');
    }
    require_once "../models/Product.php";
    $productModel = new Product($conn);
    $id = $_POST['id'] ?? 0;
    
    if ($productModel->delete($id)) {
        $_SESSION['success'] = "Product deleted from system.";
    } else {
        $_SESSION['error'] = "Cannot delete product (possible data constraint).";
    }
    header("Location: ../admin/products/index.php");
    exit;
}

if ($admin_action === 'update_product') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        die('Unauthorized');
    }
    require_once "../models/Product.php";
    $productModel = new Product($conn);
    
    $id = $_POST['id'] ?? 0;
    
    // Default to the current thumbnail
    $thumbnail = $_POST['current_thumbnail'] ?? '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['thumbnail']['tmp_name'];
        $file_name = time() . '_' . $_FILES['thumbnail']['name'];
        $upload_dir = '../assets/product-images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        if (move_uploaded_file($file_tmp, $upload_dir . $file_name)) {
            $thumbnail = $file_name;
        }
    }

    $data = [
        'category_id' => $_POST['category_id'] ?? null,
        'name' => trim($_POST['name'] ?? ''),
        'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'] ?? ''))),
        'sku' => trim($_POST['sku'] ?? ''),
        'price' => $_POST['price'] ?? 0,
        'sale_price' => !empty($_POST['sale_price']) ? $_POST['sale_price'] : null,
        'stock' => $_POST['stock'] ?? 0,
        'thumbnail' => $thumbnail,
        'description' => trim($_POST['description'] ?? ''),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    ];

    if ($productModel->update($id, $data)) {
        // Handle gallery images
        $gallery_images = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $total_files = count($_FILES['images']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['images']['tmp_name'][$i];
                    $img_name = time() . '_' . uniqid() . '_' . basename($_FILES['images']['name'][$i]);
                    $upload_dir = '../assets/product-images/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    if (move_uploaded_file($tmp_name, $upload_dir . $img_name)) {
                        $gallery_images[] = $img_name;
                    }
                }
            }
        }
        if (!empty($gallery_images)) {
            $productModel->addImages($id, $gallery_images);
        }

        $_SESSION['success'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Cannot update product.";
    }
    header("Location: ../admin/products/index.php");
    exit;
}

if ($admin_action === 'delete_product_image') {
    if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        die('Unauthorized');
    }
    require_once "../models/Product.php";
    $productModel = new Product($conn);
    
    $image_id = $_POST['image_id'] ?? 0;
    $product_id = $_POST['product_id'] ?? 0;
    
    // Optional: Get image url and delete file physically here if needed
    // But safely just delete from DB for now as old files might be used elsewhere or clean up script needed
    
    if ($productModel->deleteImage($image_id)) {
        $_SESSION['success'] = "Image removed successfully.";
    } else {
        $_SESSION['error'] = "Failed to remove image.";
    }
    header("Location: ../admin/products/edit.php?id=" . $product_id);
    exit;
}

// Fallback if no action matches
header('Location: ../user/index.php');
exit;
