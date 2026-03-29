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

    public function updateProfile($id, $name, $phone) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $phone, $id);
        return $stmt->execute();
    }

    public function updatePassword($id, $hash) {
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hash, $id);
        return $stmt->execute();
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

    public function saveContact($name, $email, $phone, $subject, $message) {
        $stmt = $this->conn->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);
        return $stmt->execute();
    }
}