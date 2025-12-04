<?php
    session_start();
    include '../database/connection.php';
    require_once '../classes/User.php';
    require_once '../classes/Company.php';

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $address = $_POST['address'];
        $password = $_POST['password'];

        if($role == "user"){
            $user = new User($conn);
            $result = $user->register($name, $email, $password, $role, $address);
            
            if($result['success']){
                $_SESSION['reg_success'] = $result['message'];
            } else {
                $_SESSION['reg_error'] = $result['message'];
            }

        } else {
            $motto = isset($_POST['companyName']) ? $_POST['companyName'] : NULL;
            $company = new Company($conn);
            $result = $company->register($name, $email, $password, $motto, $address);
            
            if($result['success']){
                $_SESSION['reg_success'] = $result['message'];
            } else {
                $_SESSION['reg_error'] = $result['message'];
            }
        }

        $conn->close();
        header("Location: register.php");
        exit;

    } else {
        $_SESSION['reg_error'] = "Invalid request";
        header("Location: register.php");
        exit;
    }
?>