<?php
    session_start();
    include '../database/connection.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $user_id = $_SESSION['user_id'];
        $company_id = $_POST['company_id'];
        $device_type = $_POST['device_type'];
        $quantity = $_POST['quantity'];
        $device_condition = $_POST['device_condition'];
        $contact = $_POST['contact'];
        $pickup_date = $_POST['pickup_date'];
        $address = $_POST['address'];

        $stmt = $conn->prepare("INSERT INTO requests (user_id, company_id, device_type, quantity, device_condition, contact, pickup_date, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssss", $user_id, $company_id, $device_type, $quantity, $device_condition, $contact, $pickup_date, $address);

        if($stmt->execute()){
            $_SESSION['success_message'] = "Request submitted successfully!";
            header("Location: ../user/requestform.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Something went wrong. Try again!";
            header("Location: ../user/requestform.php");
            exit;
        }
    }
?>