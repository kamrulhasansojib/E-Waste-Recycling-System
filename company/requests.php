<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$company_id = $_SESSION['company_id'] ?? 0;
$company_name = $_SESSION['company_name'] ?? 'Company';

if(isset($_POST['request_id'], $_POST['action'])){
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    $stmt_check = $conn->prepare("SELECT status, user_id, device_type, quantity FROM requests WHERE request_id=? AND company_id=?");
    $stmt_check->bind_param("ii", $request_id, $company_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if($res_check->num_rows > 0){
        $row_check = $res_check->fetch_assoc();
        $current_status = $row_check['status'];
        $user_id = $row_check['user_id'];
        $device_type = $row_check['device_type'];
        $quantity = $row_check['quantity'];

        if($current_status !== $action){
            $stmt_update = $conn->prepare("UPDATE requests SET status=? WHERE request_id=? AND company_id=?");
            $stmt_update->bind_param("sii", $action, $request_id, $company_id);
            $stmt_update->execute();

            $message = "Request #$request_id has been $action by $company_name.";
            $stmt_notify = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt_notify->bind_param("is", $user_id, $message);
            $stmt_notify->execute();

            if($action === 'Completed'){
                $stmt_gp = $conn->prepare("SELECT priority_point FROM items WHERE category=? LIMIT 1");
                $stmt_gp->bind_param("s", $device_type);
                $stmt_gp->execute();
                $res_gp = $stmt_gp->get_result();

                if($res_gp->num_rows > 0){
                    $row_gp = $res_gp->fetch_assoc();
                    $priority_point = intval($row_gp['priority_point']); 
                    $green_points = intval($quantity) * $priority_point;

                    $stmt_update_gp = $conn->prepare("UPDATE users SET green_points = green_points + ? WHERE user_id = ?");
                    $stmt_update_gp->bind_param("ii", $green_points, $user_id);
                    
                    if($stmt_update_gp->execute()){
                        $_SESSION['action_msg'] = "Request #$request_id completed. User earned $green_points green points!";
                    } else {
                        $_SESSION['action_msg'] = "Request #$request_id completed but points update failed.";
                    }
                } else {
                    $_SESSION['action_msg'] = "Request #$request_id completed but no points awarded (device type not found).";
                }
            } else {
                $_SESSION['action_msg'] = "Request #$request_id updated to $action.";
            }

        }
    }

    header("Location: requests.php");
    exit;
}

$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM items ORDER BY category ASC");
if($cat_result->num_rows > 0){
    while($row = $cat_result->fetch_assoc()){
        $categories[] = $row['category'];
    }
}

$status_filter = $_GET['status_filter'] ?? '';
$device_filter = $_GET['device_filter'] ?? '';
$search = $_GET['search'] ?? '';

$query = "SELECT r.request_id, r.device_type, r.quantity, r.pickup_date, r.status, r.device_condition, r.address, r.contact,
                 r.estimated_value, r.final_value, r.image,
                 u.name AS customer_name, u.user_id
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
if(!empty($search)){
    if(is_numeric($search)){
        $query .= " AND r.request_id=?";
        $params[] = intval($search);
        $types .= "i";
    } else {
        $query .= " AND u.name LIKE ?";
        $params[] = "%$search%";
        $types .= "s";
    }
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
    <title>Requests - Company Panel</title>
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
                <form method="GET" class="filter-form">
                    <input type="text" name="search" placeholder="Search by R.ID or Name"
                        value="<?php echo htmlspecialchars($search); ?>" />
                    <button type="submit"><i class="fas fa-search"></i></button>

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
                        <div class="card-content-wrapper">
                            <div class="card-details">
                                <h3><?php echo htmlspecialchars($row['customer_name']); ?> (ID:
                                    <?php echo $row['user_id']; ?>)</h3>
                                <p><i class="fas fa-phone"></i> <?php echo $row['contact']; ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo $row['address']; ?></p>
                                <p><i class="fas fa-recycle"></i> <?php echo $row['device_type']; ?> - Qty:
                                    <?php echo $row['quantity']; ?></p>
                                <p><i class="fas fa-hand-holding-usd"></i> Est. Value:
                                    <?php echo $row['estimated_value'] ? 'BDT '.htmlspecialchars($row['estimated_value']) : 'N/A'; ?>
                                </p>
                                <?php if($row['final_value']): ?>
                                <p><strong>Final Value: </strong> BDT <?php echo $row['final_value']; ?></p>
                                <?php endif; ?>
                                <p><i class="fas fa-calendar"></i>
                                    <?php echo date("M d, Y", strtotime($row['pickup_date'])); ?></p>
                                <p><strong>Condition: </strong> <?php echo $row['device_condition']; ?></p>
                            </div>
                            <?php if (!empty($row['image'])): $image_path = '../assets/uploads/' . htmlspecialchars($row['image']); ?>
                            <img src="<?php echo $image_path; ?>" alt="Device Image" class="item-image" />
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-actions">
                        <?php if($row['status'] === 'Pending'): ?>
                        <button class="btn-accept"
                            onclick="openFinalValueModal(<?php echo $row['request_id']; ?>, '<?php echo $row['device_type']; ?>')">Accept</button>
                        <button type="button" class="btn-reject"
                            onclick="rejectRequest(<?php echo $row['request_id']; ?>)">Reject</button>
                        <?php elseif($row['status'] === 'Accepted'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <input type="hidden" name="action" value="Completed">
                            <button type="submit" class="btn-complete">Complete</button>
                        </form>
                        <?php endif; ?>
                        <a href="view_request.php?request_id=<?php echo $row['request_id']; ?>"
                            class="btn-view">View</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>

    <div id="finalValueModal" class="modal">
        <div class="modal-content">
            <h3>Enter Final Value</h3>
            <p id="modalDeviceName"></p>
            <form id="finalValueForm" method="POST" action="../backend/process_final_value.php">
                <input type="hidden" name="request_id" id="modalRequestId">
                <input type="number" name="final_value" placeholder="Enter BDT" required>
                <div class="modal-buttons">
                    <button type="submit" class="btn-submit">Submit</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openFinalValueModal(requestId, deviceName) {
        document.getElementById('modalRequestId').value = requestId;
        document.getElementById('modalDeviceName').innerText = deviceName;
        document.getElementById('finalValueModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('finalValueModal').style.display = 'none';
    }

    function rejectRequest(requestId) {
        if (confirm("Are you sure you want to reject this request?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            form.innerHTML = `<input type="hidden" name="request_id" value="${requestId}">
                              <input type="hidden" name="action" value="Rejected">`;
            form.action = 'requests.php';
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>

</html>