<?php
session_start();
require_once "../config/database.php";

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Kiểm tra quyền Admin
$user = $_SESSION['user'] ?? null;
if (!$user || $user['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($action === 'customer_detail') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    // Lấy thông tin user
    $stmtUser = $conn->prepare("SELECT id, name, email, phone, role, is_active, avatar, created_at, updated_at FROM users WHERE id = ?");
    $stmtUser->bind_param("i", $id);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();

    if (!$userData) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Lấy danh sách order của user này
    $stmtOrders = $conn->prepare("SELECT id, order_code, total, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmtOrders->bind_param("i", $id);
    $stmtOrders->execute();
    $orders = $stmtOrders->get_result()->fetch_all(MYSQLI_ASSOC);

    // Tính tổng stat
    $order_count = count($orders);
    $total_spent = array_sum(array_column($orders, 'total'));

    echo json_encode([
        'success'     => true,
        'user'        => $userData,
        'orders'      => $orders,
        'order_count' => $order_count,
        'total_spent' => $total_spent
    ]);
    exit;
}

if ($action === 'toggle_user') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $is_active = intval($_POST['is_active'] ?? 0); // 1 or 0

    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid User ID']);
        exit;
    }

    // Tránh khóa chính mình
    if ($user_id === $user['id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot lock your own account']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_active, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Action not found']);
