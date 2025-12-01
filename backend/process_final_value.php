<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$company_id = $_SESSION['company_id'] ?? 0;
$company_name = $_SESSION['company_name'] ?? 'Company';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_id'], $_POST['final_value'])){
    
    $request_id = intval($_POST['request_id']);
    $final_value = intval($_POST['final_value']);

    $stmt_check = $conn->prepare("SELECT user_id FROM requests WHERE request_id=? AND company_id=?");
    $stmt_check->bind_param("ii", $request_id, $company_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if($result_check->num_rows > 0){
        $row = $result_check->fetch_assoc();
        $user_id = $row['user_id'];
        
        $stmt_update = $conn->prepare("UPDATE requests SET final_value=?, status='Accepted' WHERE request_id=? AND company_id=?");
        $stmt_update->bind_param("iii", $final_value, $request_id, $company_id);
        
        if($stmt_update->execute()){
            $message = "$company_name has offered BDT $final_value for your request #$request_id. Please Review.";
            $stmt_notify = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt_notify->bind_param("is", $user_id, $message);
            $stmt_notify->execute();
            
            $_SESSION['action_msg'] = "Request #$request_id accepted with final value BDT $final_value.";
        } else {
            $_SESSION['action_msg'] = "Failed to update final value.";
        }
    } else {
        $_SESSION['action_msg'] = "Invalid request or access denied.";
    }

} else {
    $_SESSION['action_msg'] = "Invalid data submitted.";
}

header("Location: ../company/requests.php");
exit;
?>