<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user'){
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if(!isset($_POST['request_id']) || !isset($_POST['company_id']) || !isset($_POST['company_name'])){
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'User';
$request_id = intval($_POST['request_id']);
$company_id = intval($_POST['company_id']);
$company_name = $_POST['company_name'];

include '../database/connection.php';

$stmt_verify = $conn->prepare("SELECT request_id FROM requests WHERE request_id = ? AND user_id = ? AND status = 'Accepted'");
$stmt_verify->bind_param("ii", $request_id, $user_id);
$stmt_verify->execute();
$result_verify = $stmt_verify->get_result();

if($result_verify->num_rows === 0){
    echo json_encode(['success' => false, 'message' => 'Request not found or already processed']);
    exit;
}


$notification_message = "$user_name (ID: $user_id) has accepted the pickup request #$request_id from $company_name.";

$stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, company_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
$stmt_notif->bind_param("iis", $user_id, $company_id, $notification_message);

if(!$stmt_notif->execute()){
    echo json_encode(['success' => false, 'message' => 'Request confirmed but notification failed']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Pickup request confirmed successfully!']);
$conn->close();
?>