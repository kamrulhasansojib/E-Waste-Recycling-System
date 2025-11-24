<?php
session_start();
require "../database/connection.php";

if(!isset($_SESSION['user_id'])){
    echo "<p class='error-msg'>Please login first!</p>";
    exit;
}

$user_id = $_SESSION['user_id'];

if(isset($_POST['update_profile'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE users SET name=?, email=?, phone=?, address=? WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $email, $phone, $address, $user_id);

    if($stmt->execute()){
        echo "<p class='success-msg'>Profile updated successfully!</p>";
    } else {
        echo "<p class='error-msg'>Failed to update profile.</p>";
    }
    exit;
}

if(isset($_POST['update_password'])){
    $current = $_POST['current_pass'];
    $new = $_POST['new_pass'];
    $confirm = $_POST['confirm_pass'];

    $sql = "SELECT password FROM users WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
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
    $update = "UPDATE users SET password=? WHERE user_id=?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("si", $hashed, $user_id);
    $stmt2->execute();

    echo "<p class='success-msg'>Password updated successfully!</p>";
    exit;
}
?>