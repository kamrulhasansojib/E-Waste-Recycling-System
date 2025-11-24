<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user'){
    header("Location: ../auth/login.php");
    exit;
}

$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : "User";
$user_id = $_SESSION['user_id'];

include '../database/connection.php';

$stmt_points = $conn->prepare("SELECT green_points FROM users WHERE user_id = ?");
$stmt_points->bind_param("i", $user_id);
$stmt_points->execute();
$res_points = $stmt_points->get_result();
$green_points = 0;
if($res_points->num_rows > 0){
    $row_points = $res_points->fetch_assoc();
    $green_points = $row_points['green_points'];
}

$stmt = $conn->prepare("SELECT device_type, device_condition, quantity, pickup_date, status 
                        FROM requests 
                        WHERE user_id = ? 
                        ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total_submissions = 0;
$pending_requests = 0;
$picked_up = 0;
$recycled = 0;

$requests = [];
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $requests[] = $row;
        $total_submissions++;
        switch($row['status']){
            case 'Pending':
                $pending_requests++;
                break;
            case 'Accepted':
                $picked_up++;
                break;
            case 'Completed':
                $recycled++;
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/user_dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
                <a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a>
                <a href="../user/nearby_companies.php"><i class="fas fa-building"></i> Nearby Companies</a>
                <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
                <a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Welcome, <?php echo htmlspecialchars($user_name);?></h1>
                <div class="points">
                    <i class="fas fa-leaf"></i>
                    <span>Green Points:</span>
                    <strong><?php echo $green_points; ?></strong>
                </div>
            </header>

            <section class="stats">
                <div class="card">
                    <div class="card-icon total">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Total Submissions</h3>
                    <p><?php echo $total_submissions; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Pending Requests</h3>
                    <p><?php echo $pending_requests; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon picked">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Picked Up</h3>
                    <p><?php echo $picked_up; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon recycled">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>Recycled</h3>
                    <p><?php echo $recycled; ?></p>
                </div>
            </section>

            <section class="table-section">
                <h2><i class="fas fa-table"></i> My E-Waste Submissions</h2>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-laptop"></i> Device Type</th>
                            <th><i class="fas fa-info-circle"></i> Condition</th>
                            <th><i class="fas fa-boxes"></i> Quantity</th>
                            <th><i class="fas fa-calendar-alt"></i> Pickup Date</th>
                            <th><i class="fas fa-signal"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if(count($requests) > 0){
                            foreach($requests as $row){
                                $status_class = '';
                                $status_icon = '';
                                switch($row['status']){
                                    case 'Pending': 
                                        $status_class = 'pending'; 
                                        $status_icon = 'fas fa-clock';
                                        break;
                                    case 'Accepted': 
                                        $status_class = 'picked'; 
                                        $status_icon = 'fas fa-truck';
                                        break;
                                    case 'Completed': 
                                        $status_class = 'completed'; 
                                        $status_icon = 'fas fa-check-circle';
                                        break;
                                    case 'Rejected': 
                                        $status_class = 'rejected'; 
                                        $status_icon = 'fas fa-times-circle';
                                        break;
                                    default: 
                                        $status_class = 'pending';
                                        $status_icon = 'fas fa-clock';
                                }
                                echo '<tr>
                                        <td>'.htmlspecialchars($row['device_type']).'</td>
                                        <td>'.htmlspecialchars($row['device_condition']).'</td>
                                        <td>'.htmlspecialchars($row['quantity']).'</td>
                                        <td>'.htmlspecialchars($row['pickup_date']).'</td>
                                        <td><span class="status '.$status_class.'"><i class="'.$status_icon.'"></i> '.htmlspecialchars($row['status']).'</span></td>
                                     </tr>';
                        }
                        } else {
                        echo '<tr>
                            <td colspan="5"><i class="fas fa-inbox"></i> No submissions found.</td>
                        </tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>

</html>