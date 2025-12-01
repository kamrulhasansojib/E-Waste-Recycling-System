<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$company_id = $_SESSION['company_id'] ?? 0;

$stmt = $conn->prepare("SELECT r.request_id, r.device_type, r.quantity, r.device_condition, r.address, r.pickup_date,
                               r.final_value,
                               u.name AS customer_name
                        FROM requests r
                        JOIN users u ON r.user_id = u.user_id
                        WHERE r.company_id = ? AND r.status = 'Completed'
                        ORDER BY r.pickup_date DESC");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$completed_result = $stmt->get_result();

$stats_stmt = $conn->prepare("SELECT 
                                SUM(CASE WHEN status='Accepted' THEN 1 ELSE 0 END) AS total_accepted,
                                SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS total_recycled
                              FROM requests
                              WHERE company_id = ?");
$stats_stmt->bind_param("i", $company_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result()->fetch_assoc();
$total_accepted = $stats_result['total_accepted'] ?? 0;
$total_recycled = $stats_result['total_recycled'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Completed Pickups</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/completed.css" />
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-recycle"></i>
                <h2>E-TRIEVE</h2>
                <span class="admin-badge">COMPANY PANEL</span>
            </div>
            <nav>
                <a href="../company/company_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../company/requests.php"><i class="fas fa-inbox"></i> Requests</a>
                <a href="../company/completed.php" class="active"><i class="fas fa-check-circle"></i> Completed</a>
                <a href="../company/company_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Completed Pickups</h1>
                <div class="header-stats">
                    <div class="mini-stat">
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <h3><?php echo $total_accepted; ?></h3>
                            <p>Total Accepted</p>
                        </div>
                    </div>
                    <div class="mini-stat">
                        <i class="fas fa-recycle"></i>
                        <div>
                            <h3><?php echo $total_recycled; ?></h3>
                            <p>Total Recycled</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="completed-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Customer Name</th>
                            <th>Device</th>
                            <th>Quantity</th>
                            <th>Condition</th>
                            <th>Address</th>
                            <th>Completed Date</th>
                            <th>Final Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($completed_result->num_rows > 0): ?>
                        <?php while($row = $completed_result->fetch_assoc()): ?>
                        <tr>
                            <td>#REQ-<?php echo $row['request_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['device_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['device_condition']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row['pickup_date'])); ?></td>
                            <td><?php echo $row['final_value'] ? 'BDT ' . htmlspecialchars($row['final_value']) : 'N/A'; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="8">No completed pickups found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>