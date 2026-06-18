<?php
session_start();
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = getDB(); 

    $name = $db->real_escape_string($_POST['name']);
    $email = $db->real_escape_string($_POST['email']);
    $phone = $db->real_escape_string($_POST['phone']);
    $subject = $db->real_escape_string($_POST['subject']);
    $message = $db->real_escape_string($_POST['message']);

    $sql = "INSERT INTO contacts (name, email, phone, subject, message) 
            VALUES ('$name', '$email', '$phone', '$subject', '$message')";

    if ($db->query($sql)) {
        $_SESSION['success'] = "Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm.";
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra, vui lòng thử lại sau.";
    }

    header("Location: ../user/contact.php");
    exit();
}