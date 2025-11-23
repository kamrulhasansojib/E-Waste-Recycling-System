<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$user_id = $_SESSION['user_id'];

$query = "SELECT r.*, c.company_name 
          FROM requests r
          LEFT JOIN companies c ON r.company_id = c.company_id
          WHERE r.user_id = ?
          ORDER BY r.request_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Request History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/history.css">
</head>

<body>

    <div class="dashboard-container">

        <aside class="sidebar">
            <h2>E-TRIEVE</h2>
            <ul>
                <li><a href="../user/user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a></li>
                <li><a href="../user/nearby_companies.php"><i class="fas fa-building"></i> Nearby Companies</a></li>
                <li><a href="#" class="active"><i class="fas fa-history"></i> My History</a></li>
                <li><a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <h1>My Request History</h1>

            <div class="history-grid">

                <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>

                <div class="history-card">
                    <div class="card-header">
                        <span class="req-id">#REQ-<?php echo $row['request_id']; ?></span>
                        <span class="status <?php echo strtolower($row['status']); ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </div>

                    <div class="card-body">
                        <p><strong>Company:</strong>
                            <?php echo $row['company_name'] ?? 'N/A'; ?>
                        </p>

                        <p><strong>Device:</strong>
                            <?php echo $row['device_type']; ?>
                        </p>

                        <p><strong>Condition:</strong>
                            <?php echo $row['device_condition']; ?>
                        </p>

                        <p><strong>Quantity:</strong>
                            <?php echo $row['quantity']; ?>
                        </p>

                        <p><strong>Pickup Date:</strong>
                            <?php echo date("M d, Y", strtotime($row['pickup_date'])); ?>
                        </p>

                        <p><strong>Requested On:</strong>
                            <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <?php endwhile; ?>

                <?php else: ?>
                <p class="no-data">No request history found.</p>
                <?php endif; ?>

            </div>
        </main>

    </div>

</body>

</html>