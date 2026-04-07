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
        $_SESSION['error'] = 'Please log in to manage your wishlist.';
        header('Location: ../user/login.php');
        exit;
    }

    $wishlist_id = $_POST['wishlist_id'] ?? null;

    if ($wishlist_id) {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $wishlist_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Item removed from wishlist successfully.';
        } else {
            $_SESSION['error'] = 'Failed to remove item from wishlist.';
        }
    }
    
    // Redirect back to wishlist page
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
        // Redirect back to where they came from
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../user/shop.php'));
        exit;
    }

    $product_id = $_POST['product_id'] ?? null;

    if ($product_id) {
        // Check if already in wishlist
        $check_stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $check_stmt->bind_param("ii", $user_id, $product_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $_SESSION['success'] = 'Product is already in your wishlist.';
        } else {
            // Insert
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

// Fallback if no action matches
header('Location: ../user/index.php');
exit;
