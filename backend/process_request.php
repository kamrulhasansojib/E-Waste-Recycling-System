<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $user_id = $_SESSION['user_id'];
    $company_id = $_POST['company_id'];
    $device_type = $_POST['device_type'];
    $estimated_value = $_POST['estimated_value'];
    $quantity = $_POST['quantity'];
    $device_condition = $_POST['device_condition'];
    $contact = $_POST['contact'];
    $pickup_date = $_POST['pickup_date'];
    $address = $_POST['address'];


    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){

        $allowed_ext = ['jpg', 'jpeg', 'png'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $original_name = $_FILES['image']['name'];

        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if(!in_array($ext, $allowed_ext)){
            $_SESSION['error_message'] = "Only JPG, JPEG & PNG allowed.";
            header("Location: ../user/requestform.php");
            exit;
        }

        $file_name = time() . "_" . uniqid() . "." . $ext;
        $target_dir = "../assets/uploads/";
        $target_file = $target_dir . $file_name;

        if(!move_uploaded_file($file_tmp, $target_file)){
            $_SESSION['error_message'] = "Failed to upload image.";
            header("Location: ../user/requestform.php");
            exit;
        }

    } else {
        $_SESSION['error_message'] = "Please upload an image.";
        header("Location: ../user/requestform.php");
        exit;
    }


    $stmt = $conn->prepare("
        INSERT INTO requests 
        (user_id, company_id, device_type, estimated_value, image, quantity, device_condition, contact, pickup_date, address) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisssissss",
        $user_id,
        $company_id,
        $device_type,
        $estimated_value,
        $file_name,
        $quantity,
        $device_condition,
        $contact,
        $pickup_date,
        $address
    );

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