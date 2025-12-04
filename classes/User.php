<?php

class User {
    private $conn;
    private $table = "users";

    private $user_id;
    private $name;
    private $email;
    private $phone;
    private $address;
    private $password;
    private $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($name, $email, $password, $role, $address) {
        if(empty($name) || empty($email) || empty($password)) {
            return array('success' => false, 'message' => 'Fill all required fields');
        }

        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->role = $role;
        $this->address = $address;

        $sql = "INSERT INTO {$this->table} (name, email, role, address, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $this->name, $this->email, $this->role, $this->address, $this->password);

        if($stmt->execute()) {
            $stmt->close();
            return array('success' => true, 'message' => 'Registration successful!');
        } else {
            $stmt->close();
            return array('success' => false, 'message' => 'Email already exists or DB error');
        }
    }

    public function updateProfile($user_id, $name, $email, $phone, $address) {
        $this->user_id = $user_id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;

        $sql = "UPDATE {$this->table} SET name=?, email=?, phone=?, address=? WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssi", $this->name, $this->email, $this->phone, $this->address, $this->user_id);

        if($stmt->execute()) {
            $stmt->close();
            return array('success' => true, 'message' => 'Profile updated successfully!');
        } else {
            $stmt->close();
            return array('success' => false, 'message' => 'Failed to update profile.');
        }
    }

    public function updatePassword($user_id, $current_pass, $new_pass, $confirm_pass) {
        $sql = "SELECT password FROM {$this->table} WHERE user_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stored_pass = $result['password'];
        $stmt->close();

        if(!password_verify($current_pass, $stored_pass)) {
            return array('success' => false, 'message' => 'Current password is incorrect!');
        }

        if($new_pass !== $confirm_pass) {
            return array('success' => false, 'message' => 'New passwords do not match!');
        }

        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $update = "UPDATE {$this->table} SET password=? WHERE user_id=?";
        $stmt2 = $this->conn->prepare($update);
        $stmt2->bind_param("si", $hashed, $user_id);

        if($stmt2->execute()) {
            $stmt2->close();
            return array('success' => true, 'message' => 'Password updated successfully!');
        } else {
            $stmt2->close();
            return array('success' => false, 'message' => 'Failed to update password.');
        }
    }
}

?>