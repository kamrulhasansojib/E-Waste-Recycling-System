<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$company_id = $_SESSION['company_id'] ?? 0;
$company_name = $_SESSION['company_name'] ?? 'Company';

if(isset($_POST['request_id']) && isset($_POST['action'])){
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; 

    $stmt = $conn->prepare("UPDATE requests SET status=? WHERE request_id=? AND company_id=?");
    $stmt->bind_param("sii", $action, $request_id, $company_id);
    $stmt->execute();

    $_SESSION['action_msg'] = "Request #$request_id updated to $action.";
    header("Location: company_dashboard.php"); 
    exit;
}

$pending_count = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE company_id='$company_id' AND status='Pending'")->fetch_assoc()['total'];
$accepted_count = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE company_id='$company_id' AND status='Accepted'")->fetch_assoc()['total'];
$completed_count = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE company_id='$company_id' AND status='Completed'")->fetch_assoc()['total'];

$query = "SELECT r.request_id, r.device_type, r.quantity, r.pickup_date, r.status, u.name AS customer_name
          FROM requests r
          JOIN users u ON r.user_id = u.user_id
          WHERE r.company_id = ?
          ORDER BY r.request_id DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Company Dashboard - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/compay_dashboard.css" />
</head>

<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>E-TRIEVE</h2>
            <ul>
                <li><a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../company/requests.php"><i class="fas fa-inbox"></i> Requests</a></li>
                <li><a href="../company/completed.php"><i class="fas fa-check-circle"></i> Completed</a></li>
                <li><a href="../company/company_settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($company_name); ?></h1>
                <div class="user-info">
                    <span>Admin</span>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3498db">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_count; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #2ecc71">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $accepted_count; ?></h3>
                        <p>Accepted Pickups</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_count; ?></h3>
                        <p>Completed Pickups</p>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Recent Pickup Requests</h2>
                </div>
                <div class="table-container">
                    <table class="requests-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Customer Name</th>
                                <th>Device Type</th>
                                <th>Quantity</th>
                                <th>Pickup Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#REQ-<?php echo $row['request_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo $row['device_type']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                                <td><?php echo $row['pickup_date']; ?></td>
                                <td><span
                                        class="status <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                                </td>
                                <td>
                                    <?php if($row['status'] === 'Pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="request_id"
                                            value="<?php echo $row['request_id']; ?>">
                                        <input type="hidden" name="action" value="Accepted">
                                        <button type="submit" class="btn-action btn-accept">Accept</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="request_id"
                                            value="<?php echo $row['request_id']; ?>">
                                        <input type="hidden" name="action" value="Rejected">
                                        <button type="submit" class="btn-action btn-reject">Reject</button>
                                    </form>
                                    <?php else: ?>
                                    <a href="view_request.php?request_id=<?php echo $row['request_id']; ?>"
                                        class="btn-view" style="text-decoration: none; color: white;">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>

</html>