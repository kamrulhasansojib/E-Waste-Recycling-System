<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

if(!isset($_GET['company_id'])){
    header("Location: companies.php");
    exit;
}

$company_id = intval($_GET['company_id']);

$company = $conn->query("SELECT * FROM companies WHERE company_id=$company_id")->fetch_assoc();
if(!$company){
    die("Company not found");
}

$requests = $conn->query("SELECT r.*, u.name as user_name, u.email as user_email 
                          FROM requests r 
                          LEFT JOIN users u ON r.user_id = u.user_id 
                          WHERE r.company_id=$company_id 
                          ORDER BY r.created_at DESC");

$total_pickups = $conn->query("SELECT COUNT(*) as total FROM requests WHERE company_id=$company_id")->fetch_assoc()['total'];
$completed_pickups = $conn->query("SELECT COUNT(*) as completed FROM requests WHERE company_id=$company_id AND status='Completed'")->fetch_assoc()['completed'];
$accepted_pickups = $conn->query("SELECT COUNT(*) as accepted FROM requests WHERE company_id=$company_id AND status='Accepted'")->fetch_assoc()['accepted'];
$total_items = $conn->query("SELECT COALESCE(SUM(quantity),0) as total FROM requests WHERE company_id=$company_id AND status='Completed'")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Company - <?php echo htmlspecialchars($company['company_name']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view.css">
</head>

<body>
    <div class="container">
        <a href="companies.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Companies
        </a>

        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($company['company_name'], 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($company['company_name']); ?></h1>
                    <p class="company-id">Company ID: #<?php echo $company['company_id']; ?></p>
                    <?php if(!empty($company['motto'])): ?>
                    <p class="motto">"<?php echo htmlspecialchars($company['motto']); ?>"</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Total Request</h3>
                    <div class="stat-number"><?php echo $total_pickups; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Completed</h3>
                    <div class="stat-number"><?php echo $completed_pickups; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Accepted</h3>
                    <div class="stat-number"><?php echo $accepted_pickups; ?></div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-recycle"></i>
                    <h3>Items Collected</h3>
                    <div class="stat-number"><?php echo $total_items; ?></div>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <div class="value"><?php echo htmlspecialchars($company['email']); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-phone"></i> Contact</label>
                    <div class="value"><?php echo htmlspecialchars($company['contact'] ?? 'N/A'); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <div class="value"><?php echo htmlspecialchars($company['address']); ?></div>
                </div>
                <div class="info-item">
                    <label><i class="fas fa-calendar"></i> Joined Date</label>
                    <div class="value"><?php echo date('F j, Y', strtotime($company['created_at'])); ?></div>
                </div>
            </div>
        </div>

        <div class="requests-section">
            <h2 class="section-title ">
                <i class="fas fa-clipboard-list"></i>
                Assigned Pickups
            </h2>

            <?php if($requests->num_rows > 0): ?>
            <?php while($r = $requests->fetch_assoc()): ?>
            <div class="request-card">
                <div class="request-header">
                    <h4><?php echo htmlspecialchars($r['device_type']); ?></h4>
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
                        <i class="fas fa-dollar-sign"></i>
                        <div class="detail-content">
                            <label>Estimated Value</label>
                            <div class="detail-value">
                                <?php 
                                if(!empty($r['estimated_value'])){
                                    if(is_numeric($r['estimated_value'])){
                                        echo '$'.number_format($r['estimated_value'], 2);
                                    } else {
                                        echo htmlspecialchars($r['estimated_value']);
                                    }
                                } else {
                                    echo 'N/A';
                                }
                                ?>
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
                            <label>Pickup Address</label>
                            <div class="detail-value"><?php echo htmlspecialchars($r['address']); ?></div>
                        </div>
                    </div>

                    <div class="user-info">
                        <h5><i class="fas fa-user"></i> Requested By</h5>
                        <div class="requests-details">
                            <div class="detail-item">
                                <i class="fas fa-user-circle"></i>
                                <div class="detail-content">
                                    <label>Name</label>
                                    <div class="detail-value"><?php echo htmlspecialchars($r['user_name'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <div class="detail-content">
                                    <label>Email</label>
                                    <div class="detail-value"><?php echo htmlspecialchars($r['user_email'] ?? 'N/A'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="no-requests">
                <i class="fas fa-inbox"></i>
                <p>No pickups assigned yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>