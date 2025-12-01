<?php
include '../database/connection.php';

if(isset($_POST['device_type']) && !empty($_POST['device_type'])){
    $device_type = trim($_POST['device_type']);

    $stmt = $conn->prepare("SELECT estimated_value FROM items WHERE category = ? LIMIT 1");
    $stmt->bind_param("s", $device_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        echo htmlspecialchars($row['estimated_value']);
    } else {
        echo "N/A";
    }
} else {
    echo "N/A";
}
?>