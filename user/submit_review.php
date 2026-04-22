<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";
require_once "../models/Review.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$rating     = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment    = trim($_POST['comment'] ?? '');
$user_id    = (int)$_SESSION['user']['id'];

if ($product_id <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    $_SESSION['review_error'] = "Vui lòng nhập đầy đủ thông tin đánh giá.";
    header("Location: product_detail.php?id=" . $product_id);
    exit();
}

$reviewModel = new Review($conn);

if ($reviewModel->hasUserReviewed($product_id, $user_id)) {
    $_SESSION['review_error'] = "Bạn đã đánh giá sản phẩm này rồi.";
    header("Location: product_detail.php?id=" . $product_id);
    exit();
}

$bannedWords = require "../config/banned_words.php";

$normalizedComment = mb_strtolower($comment, 'UTF-8');
$normalizedComment = str_replace(
    ['.', ',', '-', '_', '!', '?', '@', '#', '$', '%', '^', '&', '*', '(', ')', '[', ']', '{', '}', ':', ';', '"', "'", '/', '\\', '|', '+', '=', '~', '`'],
    ' ',
    $normalizedComment
);

foreach ($bannedWords as $word) {
    if (mb_stripos($normalizedComment, $word, 0, 'UTF-8') !== false) {
        $_SESSION['review_error'] = "Bình luận của bạn chứa nội dung không phù hợp nên không thể đăng.";
        header("Location: product_detail.php?id=" . $product_id);
        exit();
    }
}

$success = $reviewModel->create($product_id, $user_id, $rating, $comment, 1);

if ($success) {
    $_SESSION['review_success'] = "Đánh giá của bạn đã được đăng.";
} else {
    $_SESSION['review_error'] = "Không thể đăng đánh giá lúc này. Vui lòng thử lại.";
}

header("Location: product_detail.php?id=" . $product_id);
exit();