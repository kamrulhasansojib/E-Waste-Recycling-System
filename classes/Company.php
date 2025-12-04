<?php

class Company {
    private $conn;
    private $table = "companies";

    private $company_id;
    private $company_name;
    private $email;
    private $contact;
    private $motto;
    private $address;
    private $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($company_name, $email, $password, $motto, $address) {
        // Validation
        if(empty($company_name) || empty($email) || empty($password)) {
            return array('success' => false, 'message' => 'Fill all required fields');
        }

        $this->company_name = $company_name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->motto = $motto;
        $this->address = $address;

        $sql = "INSERT INTO {$this->table} (company_name, email, password, motto, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $this->company_name, $this->email, $this->password, $this->motto, $this->address);

        if($stmt->execute()) {
            $stmt->close();
            return array('success' => true, 'message' => 'Registration successful!');
        } else {
            $stmt->close();
            return array('success' => false, 'message' => 'Email already exists or DB error');
        }
    }

    public function updateProfile($company_id, $company_name, $email, $contact, $motto, $address) {
        $this->company_id = $company_id;
        $this->company_name = $company_name;
        $this->email = $email;
        $this->contact = $contact;
        $this->motto = $motto;
        $this->address = $address;

        $sql = "UPDATE {$this->table} SET company_name=?, email=?, contact=?, motto=?, address=? WHERE company_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", $this->company_name, $this->email, $this->contact, $this->motto, $this->address, $this->company_id);

        if($stmt->execute()) {
            $stmt->close();
            return array('success' => true, 'message' => 'Profile updated successfully!');
        } else {
            $stmt->close();
            return array('success' => false, 'message' => 'Failed to update profile.');
        }
    }

    public function updatePassword($company_id, $current_pass, $new_pass, $confirm_pass) {
        $sql = "SELECT password FROM {$this->table} WHERE company_id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $company_id);
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
        $update = "UPDATE {$this->table} SET password=? WHERE company_id=?";
        $stmt2 = $this->conn->prepare($update);
        $stmt2->bind_param("si", $hashed, $company_id);

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