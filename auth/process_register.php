<?php
    session_start();
    include '../database/connection.php';

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $name = $_POST['name'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $address = $_POST['address'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        if(empty($name) || empty($email) || empty($password)){
            $_SESSION['reg_error'] = "Fill all required fields";
            header("Location: register.php");
            exit;
        }

        if($role == "user"){
            $sql = "INSERT INTO users (name,email,role,address,password) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss",$name,$email,$role,$address,$password);
        } else {
            $motto = isset($_POST['companyName']) ? $_POST['companyName'] : NULL;
            $sql = "INSERT INTO companies (company_name,email,password,motto,address) VALUES (?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss",$name,$email,$password,$motto,$address);
        }

        if($stmt->execute()){
            $_SESSION['reg_success'] = "Registration successful!";
        } else {
            $_SESSION['reg_error'] = "Email already exists or DB error";
        }

        $stmt->close();
        $conn->close();
        header("Location: register.php");
        exit;

    } else {
        $_SESSION['reg_error'] = "Invalid request";
        header("Location: register.php");
        exit;
    }
?>