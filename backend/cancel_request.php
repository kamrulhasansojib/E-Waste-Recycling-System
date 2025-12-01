<?php
session_start();
include '../database/connection.php';

header('Content-Type: application/json');

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user'){
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
$company_id = isset($_POST['company_id']) ? intval($_POST['company_id']) : 0;

if($request_id <= 0 || $company_id <= 0){
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$stmt = $conn->prepare("SELECT device_type FROM requests WHERE request_id = ? AND user_id = ? AND status = 'Accepted'");
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}

$req_data = $result->fetch_assoc();
$device_type = $req_data['device_type'];

$stmt_update = $conn->prepare("UPDATE requests SET status = 'Rejected' WHERE request_id = ?");
$stmt_update->bind_param("i", $request_id);

if($stmt_update->execute()){
    $message = "User has cancelled the pickup request #$request_id for $device_type";
    $stmt_notif = $conn->prepare("INSERT INTO notifications (user_id, company_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt_notif->bind_param("iis", $user_id, $company_id, $message);
    $stmt_notif->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Request cancelled successfully!'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to cancel request']);
}

$conn->close();
?>