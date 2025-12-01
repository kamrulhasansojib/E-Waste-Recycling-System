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
            <div class="logo">
                <i class="fas fa-recycle"></i>
                <h2>E-TRIEVE</h2>
                <span class="admin-badge">USER PANEL</span>
            </div>
            <nav>
                <a href="../user/user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a>
                <a href="../user/nearby_companies.php"><i class="fas fa-building"></i> Nearby Companies</a>
                <a href="../user/achievements.php"><i class="fas fa-trophy"></i> Achievements</a>
                <a href="../user/user_history.php" class="active"><i class=" fas fa-history"></i> All History</a>
                <a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
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
                        <div class="card-content-wrapper">
                            <div class="card-details">
                                <p><i class="fas fa-building"></i> <?php echo $row['company_name'] ?? 'N/A'; ?></p>
                                <p><i class="fas fa-laptop"></i> <?php echo htmlspecialchars($row['device_type']); ?>
                                </p>
                                <p><i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($row['address'] ?? 'N/A'); ?></p>
                                <p><i class="fas fa-boxes"> </i> Quantity:
                                    <?php echo htmlspecialchars($row['quantity']); ?></p>

                                <p><i class="fas fa-clock"></i>
                                    <?php echo date("M d, Y", strtotime($row['created_at'])); ?></p>
                                <p><i class="fas fa-hand-holding-usd"></i>
                                    <?php echo $row['estimated_value'] ? 'BDT '.htmlspecialchars($row['estimated_value']) : 'N/A'; ?>
                                </p>
                                <p><i class="fas fa-info-circle"></i> Condition:
                                    <?php echo htmlspecialchars($row['device_condition']); ?></p>
                            </div>



                            <?php if(!empty($row['image'])): 
                                    $image_path = '../assets/uploads/' . htmlspecialchars($row['image']);
                                ?>
                            <div class="item-image">
                                <img src="<?php echo $image_path; ?>" alt="Device Image">
                            </div>
                            <?php endif; ?>
                        </div>
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