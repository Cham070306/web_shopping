<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";

$action = $_GET['action'] ?? '';

if ($action === 'create') {

    // ===== 1. CHECK LOGIN =====
    if (!isset($_SESSION['user'])) {
        header("Location: ../user/login.php");
        exit;
    }

    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $rating     = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment    = trim($_POST['comment'] ?? '');
    $user_id    = (int)$_SESSION['user']['id'];

    // ===== 2. VALIDATE =====
    if ($product_id <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
        $_SESSION['review_error'] = "Vui lòng nhập đầy đủ thông tin đánh giá.";
        header("Location: ../user/product_detail.php?id=" . $product_id);
        exit;
    }

    // ===== 3. DANH SÁCH TỪ CẤM =====
    $bannedWords = [
        'đụ','địt','đéo','dm','dmm','cc','cặc','lồn',
        'ngu','khùng','chó','súc vật',
        'fuck','shit','bitch','asshole','idiot'
    ];

    // chuẩn hóa text
    $normalized = mb_strtolower($comment, 'UTF-8');

    // loại bỏ ký tự đặc biệt để tránh né luật
    $normalized = str_replace(
        ['.', ',', '-', '_', '!', '?', '@', '#', '$', '%', '^', '&', '*', '(', ')'],
        ' ',
        $normalized
    );

    // ===== 4. CHECK TỪ CẤM =====
    foreach ($bannedWords as $word) {
        if (mb_stripos($normalized, $word, 0, 'UTF-8') !== false) {
            $_SESSION['review_error'] = "Bình luận của bạn chứa nội dung không phù hợp nên không thể đăng.";
            header("Location: ../user/product_detail.php?id=" . $product_id);
            exit;
        }
    }

    // ===== 5. CHECK ĐÃ REVIEW CHƯA =====
    $check = $conn->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? LIMIT 1");
    $check->bind_param("ii", $product_id, $user_id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();

    if ($exists) {
        $_SESSION['review_error'] = "Bạn đã đánh giá sản phẩm này rồi.";
        header("Location: ../user/product_detail.php?id=" . $product_id);
        exit;
    }

    // ===== 6. INSERT (AUTO APPROVE) =====
    $stmt = $conn->prepare("
        INSERT INTO reviews (product_id, user_id, rating, comment, is_approved, created_at)
        VALUES (?, ?, ?, ?, 1, NOW())
    ");
    $stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $_SESSION['review_success'] = "Đánh giá của bạn đã được đăng.";
    } else {
        $_SESSION['review_error'] = "Có lỗi xảy ra, vui lòng thử lại.";
    }

    header("Location: ../user/product_detail.php?id=" . $product_id);
    exit;
}