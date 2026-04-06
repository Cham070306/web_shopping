<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
require_once "../models/User.php";

$db = new User($conn); 
$userData = $db->getById($_SESSION['user']['id']);

$user_id    = $_SESSION['user']['id'];
$user_name  = $userData['name'] ?? $_SESSION['user']['name'];
$user_email = $userData['email'] ?? $_SESSION['user']['email'];
$user_phone = $userData['phone'] ?? '';
$user_avatar = $userData['avatar'] ?? 'default-avatar.jpg';

$current_page = basename($_SERVER['PHP_SELF']);

?>