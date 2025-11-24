<?php
session_start();
include '../database/connection.php';

$admin_email = 'kamrul.hasan4.cse@ulab.edu.bd';
$admin_password = 'admin123'; 

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)){
        $_SESSION['login_error'] = "Please fill all fields!";
        header("Location: login.php");
        exit;
    }

    if($email === $admin_email && $password === $admin_password){
        $_SESSION['role'] = 'admin';
        $_SESSION['email'] = $admin_email;
        header("Location: ../admin/admin_dashboard.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $userResult = $stmt->get_result();

    if($userResult->num_rows > 0){
        $user = $userResult->fetch_assoc();

        if(password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            header("Location: ../user/user_dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid password!";
            header("Location: login.php");
            exit;
        }
    }

    $stmt2 = $conn->prepare("SELECT * FROM companies WHERE email = ?");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    $companyResult = $stmt2->get_result();

    if($companyResult->num_rows > 0){
        $company = $companyResult->fetch_assoc();

        if(password_verify($password, $company['password'])){
            $_SESSION['company_id'] = $company['company_id'];
            $_SESSION['role'] = $company['role'];
            $_SESSION['email'] = $company['email'];
            $_SESSION['company_name'] = $company['company_name'];
            header("Location: ../company/company_dashboard.php");
            exit;
        } else {
            $_SESSION['login_error'] = "Invalid password!";
            header("Location: login.php");
            exit;
        }
    }

    $_SESSION['login_error'] = "Email not found!";
    header("Location: login.php");
    exit;

} else {
    $_SESSION['login_error'] = "Invalid request!";
    header("Location: login.php");
    exit;
}
?>