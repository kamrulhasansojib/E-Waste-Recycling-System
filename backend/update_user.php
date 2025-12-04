<?php
    session_start();
    require "../database/connection.php";
    require_once "../classes/User.php";

    if(!isset($_SESSION['user_id'])){
        echo "<p class='error-msg'>Please login first!</p>";
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $user = new User($conn);

    if(isset($_POST['update_profile'])){
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];

        $result = $user->updateProfile($user_id, $name, $email, $phone, $address);
        
        if($result['success']){
            echo "<p class='success-msg'>{$result['message']}</p>";
        } else {
            echo "<p class='error-msg'>{$result['message']}</p>";
        }
        exit;
    }

    if(isset($_POST['update_password'])){
        $current = $_POST['current_pass'];
        $new = $_POST['new_pass'];
        $confirm = $_POST['confirm_pass'];

        $result = $user->updatePassword($user_id, $current, $new, $confirm);
        
        if($result['success']){
            echo "<p class='success-msg'>{$result['message']}</p>";
        } else {
            echo "<p class='error-msg'>{$result['message']}</p>";
        }
        exit;
    }
?>