<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

// Check if already subscribed
$checkStmt = $conn->prepare("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$existing = $checkStmt->get_result()->fetch_assoc();

if ($existing) {
    if ($existing['is_active']) {
        echo json_encode(['success' => false, 'message' => 'This email is already subscribed!']);
    } else {
        // Re-activate
        $reactivate = $conn->prepare("UPDATE newsletter_subscribers SET is_active = 1, subscribed_at = NOW() WHERE email = ?");
        $reactivate->bind_param("s", $email);
        $reactivate->execute();
        echo json_encode(['success' => true, 'message' => 'Welcome back! You have been re-subscribed.']);
    }
    exit;
}

// Insert new subscriber
$insertStmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
$insertStmt->bind_param("s", $email);

if ($insertStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Something went wrong. Please try again.']);
}
