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

    $stmt_check = $conn->prepare("SELECT status, user_id, quantity FROM requests WHERE request_id=? AND company_id=?");
    $stmt_check->bind_param("ii", $request_id, $company_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if($res_check->num_rows > 0){
        $row_check = $res_check->fetch_assoc();
        $current_status = $row_check['status'];
        $user_id = $row_check['user_id'];
        $quantity = $row_check['quantity'];

        if($current_status !== $action){
            $stmt = $conn->prepare("UPDATE requests SET status=? WHERE request_id=? AND company_id=?");
            $stmt->bind_param("sii", $action, $request_id, $company_id);
            $stmt->execute();

            if($action === "Completed" && $current_status !== "Completed"){
                $points = $quantity * 10;
                $stmt_points = $conn->prepare("UPDATE users SET green_points = COALESCE(green_points,0) + ? WHERE user_id=?");
                $stmt_points->bind_param("ii", $points, $user_id);
                $stmt_points->execute();
            }
        }
    }

    $_SESSION['action_msg'] = "Request #$request_id updated to $action.";
    header("Location: requests.php");
    exit;
}

$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM items");
if($cat_result->num_rows > 0){
    while($row = $cat_result->fetch_assoc()){
        $categories[] = $row['category'];
    }
}

$status_filter = $_GET['status_filter'] ?? '';
$device_filter = $_GET['device_filter'] ?? '';

$query = "SELECT r.request_id, r.device_type, r.quantity, r.pickup_date, r.status, r.device_condition, r.address, r.contact,
                 u.name AS customer_name
          FROM requests r
          JOIN users u ON r.user_id = u.user_id
          WHERE r.company_id = ?";
$params = [$company_id];
$types = "i";

if(!empty($status_filter)){
    $query .= " AND r.status=?";
    $params[] = $status_filter;
    $types .= "s";
}

if(!empty($device_filter)){
    $query .= " AND r.device_type=?";
    $params[] = $device_filter;
    $types .= "s";
}

$query .= " ORDER BY r.request_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Requests - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/requests.css" />
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
                <a href="#" class="active"><i class="fas fa-inbox"></i> Requests</a>
                <a href="../company/completed.php"><i class="fas fa-check-circle"></i> Completed</a>
                <a href="../company/company_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <h1>Pickup Requests</h1>

            <div class="search-filter-bar">
                <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap;">
                    <select name="status_filter" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Pending" <?php if($status_filter=="Pending") echo "selected"; ?>>Pending</option>
                        <option value="Accepted" <?php if($status_filter=="Accepted") echo "selected"; ?>>Accepted
                        </option>
                        <option value="Completed" <?php if($status_filter=="Completed") echo "selected"; ?>>Completed
                        </option>
                        <option value="Rejected" <?php if($status_filter=="Rejected") echo "selected"; ?>>Rejected
                        </option>
                    </select>
                    <select name="device_filter" onchange="this.form.submit()">
                        <option value="">All Devices</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php if($device_filter==$cat) echo "selected"; ?>>
                            <?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="requests-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="request-card">
                    <div class="card-header">
                        <span class="request-id">#REQ-<?php echo $row['request_id']; ?></span>
                        <span
                            class="status <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span>
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($row['customer_name']); ?></h3>
                        <p><i class="fas fa-phone"></i> <?php echo $row['contact']; ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo $row['address']; ?></p>
                        <p><i class="fas fa-recycle"></i> <?php echo $row['device_type']; ?> - Qty:
                            <?php echo $row['quantity']; ?></p>
                        <p><i class="fas fa-calendar"></i> <?php echo date("M d, Y", strtotime($row['pickup_date'])); ?>
                        </p>
                        <p><strong>Condition:</strong> <?php echo $row['device_condition']; ?></p>
                    </div>
                    <div class="card-actions">
                        <?php if($row['status'] === 'Pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <input type="hidden" name="action" value="Accepted">
                            <button type="submit" class="btn-accept">Accept</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <input type="hidden" name="action" value="Rejected">
                            <button type="submit" class="btn-reject">Reject</button>
                        </form>
                        <?php elseif($row['status'] === 'Accepted'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <input type="hidden" name="action" value="Completed">
                            <button type="submit" class="btn-accept">Complete</button>
                        </form>
                        <a href="view_request.php?request_id=<?php echo $row['request_id']; ?>" class="btn-view"
                            style="padding: 6px 26px; border: 2px solid #065f46; color: #065f46 ;border-radius:8px;  text-decoration: none; font-weight: 600; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);">View</a>

                        <?php else: ?>
                        <a href="view_request.php?request_id=<?php echo $row['request_id']; ?>" class="btn-view"
                            style="padding: 6px 26px; border: 2px solid #065f46; color: #065f46 ;border-radius:8px;  text-decoration: none; font-weight: 600; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3); ">View</a>

                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>

</html>