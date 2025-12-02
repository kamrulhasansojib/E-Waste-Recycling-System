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
$rejected_count = $conn->query("SELECT COUNT(*) AS total FROM requests WHERE company_id='$company_id' AND status='Rejected'")->fetch_assoc()['total'];

$stmt_notif = $conn->prepare("
    SELECT notification_id, message, created_at 
    FROM notifications 
    WHERE company_id = ? AND is_read = 0 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt_notif->bind_param("i", $company_id);
$stmt_notif->execute();
$res_notif = $stmt_notif->get_result();
$notifications = [];
$unread_count = 0;
if($res_notif->num_rows > 0){
    while($row = $res_notif->fetch_assoc()){
        $notifications[] = $row;
        $unread_count++;
    }
}

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
            <div class="logo">
                <i class="fas fa-recycle"></i>
                <h2>E-TRIEVE</h2>
                <span class="admin-badge">COMPANY PANEL</span>
            </div>
            <nav>
                <a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../company/requests.php"><i class="fas fa-inbox"></i> Requests</a>
                <a href="../company/completed.php"><i class="fas fa-check-circle"></i> Completed</a>
                <a href="../company/company_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../company/terms_conditions.php"><i class="fas fa-file-contract"></i> T&C</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($company_name); ?></h1>
                <div class="header-right">
                    <div class="notification-bell" onclick="toggleNotifBar()">
                        <i class="fas fa-bell fa-2x"></i>
                        <?php if($unread_count > 0){ ?>
                        <span class="badge" id="notif-count"><?php echo $unread_count; ?></span>
                        <?php } ?>
                    </div>
                    <div class="user-info">
                        <span>Admin</span>
                        <i class="fas fa-user-circle"></i>
                    </div>
                </div>
            </div>

            <div id="notif-overlay" onclick="toggleNotifBar()"></div>

            <div id="notif-bar">
                <h3>Notifications <span class="close-btn" onclick="toggleNotifBar()">×</span></h3>
                <?php
                if(count($notifications) > 0){
                    foreach($notifications as $n){
                        echo '<div class="notif-item">'.htmlspecialchars($n['message']).'<br><small>'.htmlspecialchars($n['created_at']).'</small></div>';
                    }
                } else {
                    echo '<p>No new notifications.</p>';
                }
                ?>
            </div>

            <div class="stats">
                <div class="card">
                    <div class="card-icon total" style="background: #3498db">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h3>Pending Requests</h3>
                    <p><?php echo $pending_count; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon pending" style="background: #2ecc71">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Accepted Pickups</h3>
                    <p><?php echo $accepted_count; ?></p>
                </div>

                <div class="card">
                    <div class="card-icon recycled" style="background: #e74c3c">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Completed Pickups</h3>
                    <p><?php echo $completed_count; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon rejected" style="background: #f39c12">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h3>Rejected Requests</h3>
                    <p><?php echo $rejected_count; ?></p>
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
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
    function toggleNotifBar() {
        const bar = document.getElementById('notif-bar');
        const overlay = document.getElementById('notif-overlay');
        const badge = document.getElementById('notif-count');

        bar.classList.toggle('show');
        overlay.classList.toggle('show');

        if (bar.classList.contains('show') && badge) {
            badge.style.display = 'none';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const bar = document.getElementById('notif-bar');
            const overlay = document.getElementById('notif-overlay');
            if (bar.classList.contains('show')) {
                bar.classList.remove('show');
                overlay.classList.remove('show');
            }
        }
    });
    </script>

</body>

</html>