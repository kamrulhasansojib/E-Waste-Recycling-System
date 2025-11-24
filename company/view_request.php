<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

if(!isset($_GET['request_id'])){
    header("Location: requests.php");
    exit;
}

$request_id = intval($_GET['request_id']);
$company_id = $_SESSION['company_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT r.*, 
           u.name as customer_name, 
           u.email as customer_email, 
           u.phone as customer_phone,
           u.address as customer_address,
           u.green_points
    FROM requests r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.request_id = ? AND r.company_id = ?
");
$stmt->bind_param("ii", $request_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    die("Request not found or access denied");
}

$request = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request #<?php echo $request['request_id']; ?> - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view.css">

</head>

<body>
    <div class="container" style="max-width: 750px;">
        <a href="requests.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Requests
        </a>

        <div class="request-card" style=" padding: 50px;">
            <div class="request-header">
                <h1>
                    <i class="fas fa-file-alt"></i>
                    Request #<?php echo $request['request_id']; ?>
                </h1>
                <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                    <?php echo $request['status']; ?>
                </span>
            </div>

            <div class="customer-section">
                <div class="section-title">
                    <i class="fas fa-user"></i>
                    Customer Information
                </div>
                <div class="customer-header">
                    <div class="customer-avatar">
                        <?php echo strtoupper(substr($request['customer_name'], 0, 1)); ?>
                    </div>
                    <div class="customer-info">
                        <h3><?php echo htmlspecialchars($request['customer_name']); ?></h3>
                        <p class="points">
                            <i class="fas fa-star"></i>
                            <?php echo $request['green_points']; ?> Green Points
                        </p>
                    </div>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <div class="value"><?php echo htmlspecialchars($request['customer_email']); ?></div>
                    </div>
                    <div class="info-item">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <div class="value"><?php echo htmlspecialchars($request['customer_phone']); ?></div>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <label><i class="fas fa-map-marker-alt"></i> Customer Address</label>
                        <div class="value"><?php echo htmlspecialchars($request['customer_address']); ?></div>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <div class="section-title">
                    <i class="fas fa-recycle"></i>
                    Pickup Details
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <label><i class="fas fa-laptop"></i> Device Type</label>
                        <div class="value"><?php echo htmlspecialchars($request['device_type']); ?></div>
                    </div>
                    <div class="info-item">
                        <label><i class="fas fa-hashtag"></i> Quantity</label>
                        <div class="value"><?php echo $request['quantity']; ?> items</div>
                    </div>
                    <div class="info-item">
                        <label><i class="fas fa-info-circle"></i> Condition</label>
                        <div class="value"><?php echo htmlspecialchars($request['device_condition']); ?></div>
                    </div>
                    <div class="info-item">
                        <label><i class="fas fa-calendar-alt"></i> Pickup Date</label>
                        <div class="value"><?php echo date('F j, Y', strtotime($request['pickup_date'])); ?></div>
                    </div>
                    <div class="info-item">
                        <label><i class="fas fa-phone-alt"></i> Contact Number</label>
                        <div class="value"><?php echo htmlspecialchars($request['contact']); ?></div>
                    </div>
                    <div class="info-item" style="grid-column: 1 / -1;">
                        <label><i class="fas fa-map-marker-alt"></i> Pickup Address</label>
                        <div class="value"><?php echo htmlspecialchars($request['address']); ?></div>
                    </div>
                </div>
            </div>

            <div class="timeline">
                <div class="timeline-title">
                    <i class="fas fa-clock"></i>
                    Timeline
                </div>
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-plus"></i>
                    </div>
                    <div class="timeline-content">
                        <p>Request Created</p>
                        <span><?php echo date('F j, Y g:i A', strtotime($request['created_at'])); ?></span>
                    </div>
                </div>
                <?php if($request['status'] !== 'Pending'): ?>
                <div class="timeline-item">
                    <div class="timeline-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="timeline-content">
                        <p>Status: <?php echo $request['status']; ?></p>
                        <span>Updated by company</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="action-buttons">
                <?php if($request['status'] === 'Pending'): ?>
                <form method="POST" action="requests.php" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                    <input type="hidden" name="action" value="Accepted">
                    <button type="submit" class="btn btn-accept">
                        <i class="fas fa-check"></i>
                        Accept Request
                    </button>
                </form>
                <form method="POST" action="requests.php" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                    <input type="hidden" name="action" value="Rejected">
                    <button type="submit" class="btn btn-reject">
                        <i class="fas fa-times"></i>
                        Reject Request
                    </button>
                </form>
                <?php elseif($request['status'] === 'Accepted'): ?>
                <form method="POST" action="requests.php" style="display:inline;">
                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                    <input type="hidden" name="action" value="Completed">
                    <button type="submit" class="btn btn-complete">
                        <i class="fas fa-check-circle"></i>
                        Mark as Completed
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>