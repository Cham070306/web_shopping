<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $email, $password) {
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $name, $email, $password);
        return $stmt->execute();
    }

    public function updateAvatar($id, $avatar) {
        $stmt = $this->conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatar, $id);
        return $stmt->execute();
    }

    public function updatePassword($email, $password) {
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$password, $email]);
}

    public function updateAddress($userId, $type, $name, $phone, $address) {
        $check = $this->conn->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND type = ?");
        $check->bind_param("is", $userId, $type);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $stmt = $this->conn->prepare("UPDATE user_addresses SET full_name=?, phone=?, address=? WHERE user_id=? AND type=?");
            $stmt->bind_param("sssis", $name, $phone, $address, $userId, $type);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO user_addresses (full_name, phone, address, user_id, type) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $name, $phone, $address, $userId, $type);
        }
        return $stmt->execute();
    }
    public function getAddress($userId, $type) {
        $stmt = $this->conn->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND type = ?");
        $stmt->bind_param("is", $userId, $type);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function saveContact($name, $email, $phone, $subject, $message) {
        $stmt = $this->conn->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        return $stmt->execute();
    }
    public function updatePasswordByEmail($email, $password) {
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ss", $password, $email);
    return $stmt->execute();
}
}