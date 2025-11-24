<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

if(!isset($_GET['user_id'])){
    header("Location: users.php");
    exit;
}

$user_id = intval($_GET['user_id']);

$user = $conn->query("SELECT * FROM users WHERE user_id=$user_id")->fetch_assoc();
if(!$user){
    die("User not found");
}

// Updated query to include company name
$requests = $conn->query("SELECT r.*, c.company_name 
                          FROM requests r 
                          LEFT JOIN companies c ON r.company_id = c.company_id 
                          WHERE r.user_id=$user_id 
                          ORDER BY r.created_at DESC");

$total_items = $conn->query("SELECT COALESCE(SUM(quantity),0) as total FROM requests WHERE user_id=$user_id")->fetch_assoc()['total'];

$completed_pickups = $conn->query("SELECT COALESCE(SUM(quantity),0) as completed FROM requests WHERE user_id=$user_id AND status='Completed'")->fetch_assoc()['completed'];

$pending_requests = $conn->query("SELECT COUNT(*) as count FROM requests WHERE user_id=$user_id AND status='Pending'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User - <?php echo htmlspecialchars($user['name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view.css">
</head>

<body>
    <div class="container">
        <a href="admin_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                    <p class="user-id">User ID: #<?php echo $user['user_id']; ?></p>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-star"></i>
                    <h3>Green Points</h3>
                    <div class="stat-number"><?php echo $user['green_points']; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-box"></i>
                    <h3>Total Items</h3>
                    <div class="stat-number"><?php echo $total_items; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Completed</h3>
                    <div class="stat-number"><?php echo $completed_pickups; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending</h3>
                    <div class="stat-number"><?php echo $pending_requests; ?></div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-phone"></i> Phone</label>
                    <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <div class="value"><?php echo htmlspecialchars($user['address']); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-calendar"></i> Member Since</label>
                    <div class="value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="requests-section">
            <h2 class="section-title">
                <i class="fas fa-clipboard-list"></i>
                Request History
            </h2>

            <?php if($requests->num_rows > 0): ?>
            <?php while($r = $requests->fetch_assoc()): ?>
            <div class="request-card">
                <div class="request-header">
                    <h4>
                        <?php echo htmlspecialchars($r['device_type']); ?>
                    </h4>
                    <span class="status-badge status-<?php echo strtolower($r['status']); ?>">
                        <?php echo htmlspecialchars($r['status']); ?>
                    </span>
                </div>

                <div class="request-details">
                    <div class="detail-item">
                        <i class="fas fa-hashtag"></i>
                        <div class="detail-content">
                            <label>Quantity</label>
                            <div class="detail-value"><?php echo $r['quantity']; ?> items</div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-info-circle"></i>
                        <div class="detail-content">
                            <label>Condition</label>
                            <div class="detail-value"><?php echo htmlspecialchars($r['device_condition']); ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-building"></i>
                        <div class="detail-content">
                            <label>Company</label>
                            <div class="detail-value">
                                <?php echo $r['company_name'] ? htmlspecialchars($r['company_name']) : 'Not Assigned'; ?>
                            </div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="detail-content">
                            <label>Pickup Date</label>
                            <div class="detail-value"><?php echo date('M j, Y', strtotime($r['pickup_date'])); ?></div>
                        </div>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-phone-alt"></i>
                        <div class="detail-content">
                            <label>Contact</label>
                            <div class="detail-value"><?php echo htmlspecialchars($r['contact']); ?></div>
                        </div>
                    </div>
                    <div class="detail-item" style="grid-column: 1 / -1;">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="detail-content">
                            <label>Address</label>
                            <div class="detail-value"><?php echo htmlspecialchars($r['address']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="no-requests">
                <i class="fas fa-inbox"></i>
                <p>No requests submitted yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>