<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['request_id']) || !isset($_POST['company_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$request_id = intval($_POST['request_id']);
$company_id = intval($_POST['company_id']);
$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : 'Company';

include '../database/connection.php';

$conn->begin_transaction();

try {
    $stmt_check = $conn->prepare("SELECT request_id FROM requests WHERE request_id = ? AND user_id = ? AND status = 'Accepted'");
    $stmt_check->bind_param("ii", $request_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception('Request not found');
    }
    
    $stmt_check_notif = $conn->prepare("SELECT notification_id FROM notifications WHERE company_id = ? AND message LIKE CONCAT('%request #', ?, '%') AND message LIKE '%confirmed the pickup%'");
    $stmt_check_notif->bind_param("ii", $company_id, $request_id);
    $stmt_check_notif->execute();
    $result_notif = $stmt_check_notif->get_result();
    
    if ($result_notif->num_rows > 0) {
        throw new Exception('Request already confirmed');
    }
    
    $user_message = "You have confirmed the pickup request from " . $company_name . ". The company will proceed with the pickup.";
    $stmt_notif_user = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt_notif_user->bind_param("is", $user_id, $user_message);
    
    if (!$stmt_notif_user->execute()) {
        throw new Exception('Failed to create user notification');
    }
    
    $company_message = "User has confirmed the pickup for request #" . $request_id . ". Please proceed with pickup.";
    $stmt_notif_company = $conn->prepare("INSERT INTO notifications (company_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt_notif_company->bind_param("is", $company_id, $company_message);
    
    if (!$stmt_notif_company->execute()) {
        throw new Exception('Failed to create company notification');
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Pickup confirmed successfully!'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>