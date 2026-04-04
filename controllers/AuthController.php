```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/config.php";
require_once "../config/database.php";
require_once "../models/User.php";

$userModel = new User($conn);
$action = $_GET['action'] ?? '';

/**
 * @param string $url
 * @param string $type
 * @param string $msg
 */
function notify($url, $type, $msg) {
    $_SESSION[$type] = $msg;
    header("Location: $url");
    exit();
}

switch ($action) {

    case 'register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $conf  = $_POST['confirm_password'] ?? '';

            $_SESSION['old_data'] = [
                'name' => $name,
                'email' => $email
            ];

            if (empty($name) || empty($email) || empty($pass)) {
                notify("../user/register.php", "error", "Vui lòng điền đầy đủ thông tin!");
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                notify("../user/register.php", "error", "Email không đúng định dạng!");
            }

            if (strlen($pass) < 6) {
                notify("../user/register.php", "error", "Mật khẩu phải ít nhất 6 ký tự!");
            }

            if ($pass !== $conf) {
                notify("../user/register.php", "error", "Mật khẩu xác nhận không khớp!");
            }

            if ($userModel->getByEmail($email)) {
                notify("../user/register.php", "error", "Email này đã tồn tại!");
            }

            unset($_SESSION['old_data']);

            $hash = password_hash($pass, PASSWORD_BCRYPT);

            if ($userModel->create($name, $email, $hash)) {
                notify("../user/login.php", "success", "Đăng ký thành công!");
            } else {
                notify("../user/register.php", "error", "Lỗi hệ thống.");
            }
        }
        break;

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            if (empty($email) || empty($pass)) {
                notify("../user/login.php", "error", "Vui lòng nhập email và mật khẩu.");
            }

            $user = $userModel->getByEmail($email);

            if ($user && password_verify($pass, $user['password'])) {

                unset($user['password']);
                $_SESSION['user'] = $user;

                if ($remember) {
                    setcookie("user_email", $email, time() + (30 * 24 * 60 * 60), "/");
                } else {
                    if (isset($_COOKIE['user_email'])) {
                        setcookie("user_email", "", time() - 3600, "/");
                    }
                }

                header("Location: ../user/index.php");
                exit();

            } else {
                notify("../user/login.php", "error", "Email hoặc mật khẩu không chính xác.");
            }
        }
        break;

    case 'forgot_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim($_POST['email'] ?? '');
            $user = $userModel->getByEmail($email);

            if ($user) {
                notify("../user/login.php", "success", "Yêu cầu đã được gửi! Vui lòng kiểm tra email của bạn.");
            } else {
                notify("../user/login.php", "error", "Email này không tồn tại trong hệ thống!");
            }
        }
    break;

    case 'update_full':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
            $userId = $_SESSION['user']['id'];
            $name = trim($_POST['display_name'] ?? '');
            $successCount = 0;

            if (!empty($name)) {
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $userId);
                if ($stmt->execute()) {
                    $_SESSION['user']['name'] = $name;
                    $successCount++;
                }
            }
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(__DIR__) . '/assets/uploads/';
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $fileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fileName)) {
                    if ($userModel->updateAvatar($userId, $fileName)) {
                        $_SESSION['user']['avatar'] = $fileName;
                        $successCount++;
                    }
                }
            }

            if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
                $user = $userModel->getById($userId);
                if (password_verify($_POST['old_password'], $user['password'])) {
                    $newHash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
                    $userModel->updatePassword($userId, $newHash);
                } else {
                    notify("../user/my_account.php", "error", "Mật khẩu hiện tại không đúng.");
                }
            }
            notify("../user/my_account.php", "success", "Đã cập nhật thông tin và ảnh đại diện.");
        }
    break;

    case 'save_address':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user'])) {
            $userId   = $_SESSION['user']['id'];
            $type     = $_POST['type'] ?? 'shipping';
            $fullName = trim($_POST['full_name'] ?? '');
            $phone    = trim($_POST['phone'] ?? '');
            $address  = trim($_POST['address'] ?? '');

            if ($userModel->updateAddress($userId, $type, $fullName, $phone, $address)) {
                $_SESSION['user'][$type . '_name'] = $fullName;
                $_SESSION['user'][$type . '_phone'] = $phone;
                $_SESSION['user'][$type . '_address'] = $address;

                if (isset($_POST['ajax'])) { echo "success"; exit(); }
                notify("../user/my_address.php", "success", "Thành công");
            }
        }
    break;

    case 'logout':

        session_unset();
        session_destroy();

        if (isset($_COOKIE['user_email'])) {
            setcookie("user_email", "", time() - 3600, "/");
        }

        header("Location: ../user/login.php");
        exit();
    break;

    default:
        header("Location: ../user/index.php");
    exit();
}

