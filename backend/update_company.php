<?php
    session_start();
    require "../database/connection.php";

    if(!isset($_SESSION['company_id'])){
        echo "<p class='error-msg'>Please login first!</p>";
        exit;
    }

    $company_id = $_SESSION['company_id'];

    if(isset($_POST['update_profile'])){
        $company_name = $_POST['company_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $motto = $_POST['motto'] ?? '';
        $address = $_POST['address'] ?? '';

        $sql = "UPDATE companies SET company_name=?, email=?, contact=?, motto=?, address=? WHERE company_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $company_name, $email, $contact, $motto, $address, $company_id);

        if($stmt->execute()){
            echo "<p class='success-msg'>Profile updated successfully!</p>";
        } else {
            echo "<p class='error-msg'>Failed to update profile.</p>";
        }
        exit;
    }

    if(isset($_POST['update_password'])){
        $current = $_POST['current_pass'] ?? '';
        $new = $_POST['new_pass'] ?? '';
        $confirm = $_POST['confirm_pass'] ?? '';

        $sql = "SELECT password FROM companies WHERE company_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $stored_pass = $stmt->get_result()->fetch_assoc()['password'];

        if(!password_verify($current, $stored_pass)){
            echo "<p class='error-msg'>Current password is incorrect!</p>";
            exit;
        }

        if($new !== $confirm){
            echo "<p class='error-msg'>New passwords do not match!</p>";
            exit;
        }

        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = "UPDATE companies SET password=? WHERE company_id=?";
        $stmt2 = $conn->prepare($update);
        $stmt2->bind_param("si", $hashed, $company_id);
        if($stmt2->execute()){
            echo "<p class='success-msg'>Password updated successfully!</p>";
        } else {
            echo "<p class='error-msg'>Failed to update password.</p>";
        }
        exit;
    }
?>