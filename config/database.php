<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "web_shopping";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}
?>