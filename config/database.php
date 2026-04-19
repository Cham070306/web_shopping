<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "web_shopping";



$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Kết nối database thất bại: " . mysqli_connect_error());
}

// Auto-seed for high quality demo
$checkRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM products");
if ($checkRes) {
    $row = mysqli_fetch_assoc($checkRes);
    if ((int)$row['cnt'] === 0) {
        $seedFile = __DIR__ . '/../seed.php';
        if (file_exists($seedFile)) {
            require_once $seedFile;
        }
    }
}
?>