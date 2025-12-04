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

$stmt_accepted = $conn->prepare("
    SELECT r.request_id, r.device_type, r.quantity, r.pickup_date, r.status, r.final_value, r.company_id,
           c.company_name
    FROM requests r
    LEFT JOIN companies c ON r.company_id = c.company_id
    WHERE r.user_id = ? 
    AND r.status = 'Accepted'
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.company_id = r.company_id 
        AND n.message LIKE CONCAT('%request #', r.request_id, '%')
        AND n.message LIKE '%confirmed the pickup%'
    )
    ORDER BY r.created_at DESC
");
$stmt_accepted->bind_param("i", $user_id);
$stmt_accepted->execute();
$result_accepted = $stmt_accepted->get_result();
$accepted_requests = [];
if($result_accepted->num_rows > 0){
    while($row = $result_accepted->fetch_assoc()){
        $accepted_requests[] = $row;
    }
}
$accepted_count = count($accepted_requests);

$stmt = $conn->prepare("SELECT device_type, device_condition, quantity, pickup_date, status, estimated_value
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
            case 'Pending': $pending_requests++; break;
            case 'Accepted': $picked_up++; break;
            case 'Completed': $recycled++; break;
        }
    }
}

$stmt_notif = $conn->prepare("
    SELECT notification_id, message, created_at 
    FROM notifications 
    WHERE user_id = ? AND company_id IS NULL AND is_read = 0 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt_notif->bind_param("i", $user_id);
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
                <a href="../user/achievements.php"><i class="fas fa-trophy"></i> Achievements</a>
                <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
                <a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../index.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <h1>Welcome, <?php echo htmlspecialchars($user_name);?></h1>
                <div class="points">
                    <i class="fas fa-leaf"></i>
                    <span>Green Points:</span>
                    <strong><?php echo $green_points; ?></strong>
                </div>

                <div class="request-icon" onclick="openRequestModal()">
                    <i class="fas fa-handshake fa-2x"></i>
                    <?php if($accepted_count > 0){ ?>
                    <span class="badge"><?php echo $accepted_count; ?></span>
                    <?php } ?>
                </div>

                <div class="notification-bell" onclick="toggleNotifBar()">
                    <i class="fas fa-bell fa-2x"></i>
                    <?php if($unread_count > 0){ ?>
                    <span class="badge" id="notif-count"><?php echo $unread_count; ?></span>
                    <?php } ?>
                </div>
            </header>

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

            <div id="request-modal" class="modal-overlay" onclick="closeRequestModal(event)">
                <div class="modal-content" onclick="event.stopPropagation()">
                    <div class="modal-header">
                        <h2><i class="fas fa-handshake"></i> Company Accepted Requests</h2>
                        <span class="modal-close" onclick="closeRequestModal()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <?php if(count($accepted_requests) > 0){ ?>
                        <?php foreach($accepted_requests as $req){ ?>
                        <div class="request-card" id="req-card-<?php echo $req['request_id']; ?>">
                            <div class="card-row">
                                <div class="card-info-group">
                                    <div class="card-detail">
                                        <i class="fas fa-laptop"></i>
                                        <strong>Device:</strong> <?php echo htmlspecialchars($req['device_type']); ?>
                                    </div>
                                    <div class="card-detail">
                                        <i class="fas fa-building"></i>
                                        <strong>Company:</strong> <?php echo htmlspecialchars($req['company_name']); ?>
                                    </div>
                                    <div class="card-detail">
                                        <i class="fas fa-boxes"></i>
                                        <strong>Qty:</strong> <?php echo htmlspecialchars($req['quantity']); ?>
                                    </div>
                                    <div class="card-detail">
                                        <i class="fas fa-calendar-alt"></i>
                                        <strong>Pickup:</strong> <?php echo htmlspecialchars($req['pickup_date']); ?>
                                    </div>
                                    <div class="card-detail">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <strong>Final Value:</strong> BDT
                                        <?php echo $req['final_value'] ? htmlspecialchars($req['final_value']) : '0'; ?>
                                    </div>
                                </div>
                                <div class="card-actions">
                                    <button class="btn-confirm"
                                        onclick="confirmRequest(<?php echo $req['request_id']; ?>, <?php echo $req['company_id']; ?>, '<?php echo htmlspecialchars($req['company_name'], ENT_QUOTES); ?>')">
                                        <i class="fas fa-check"></i> Confirm
                                    </button>
                                    <button class="btn-cancel"
                                        onclick="cancelRequest(<?php echo $req['request_id']; ?>, <?php echo $req['company_id']; ?>)">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php } else { ?>
                        <div class="no-requests-msg">
                            <i class="fas fa-inbox"></i>
                            <p>No accepted requests at the moment.</p>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <section class="stats">
                <div class="card">
                    <div class="card-icon total"><i class="fas fa-clipboard-list"></i></div>
                    <h3>Total Submissions</h3>
                    <p><?php echo $total_submissions; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon pending"><i class="fas fa-clock"></i></div>
                    <h3>Pending Requests</h3>
                    <p><?php echo $pending_requests; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon picked"><i class="fas fa-truck"></i></div>
                    <h3>Pick Up Accept</h3>
                    <p><?php echo $picked_up; ?></p>
                </div>
                <div class="card">
                    <div class="card-icon recycled"><i class="fas fa-check-circle"></i></div>
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
                            <th><i class="fas fa-hand-holding-usd"></i> Estimated Value</th>
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
                                    case 'Pending': $status_class='pending'; $status_icon='fas fa-clock'; break;
                                    case 'Accepted': $status_class='picked'; $status_icon='fas fa-truck'; break;
                                    case 'Completed': $status_class='completed'; $status_icon='fas fa-check-circle'; break;
                                    case 'Rejected': $status_class='rejected'; $status_icon='fas fa-times-circle'; break;
                                    default: $status_class='pending'; $status_icon='fas fa-clock';
                                }
                                echo '<tr>
                                        <td>'.htmlspecialchars($row['device_type']).'</td>
                                        <td>'.htmlspecialchars($row['device_condition']).'</td>
                                        <td>'.htmlspecialchars($row['quantity']).'</td>
                                        <td>'.($row['estimated_value'] ? 'BDT '.htmlspecialchars($row['estimated_value']) : 'N/A').'</td>
                                        <td>'.htmlspecialchars($row['pickup_date']).'</td>
                                        <td><span class="status '.$status_class.'"><i class="'.$status_icon.'"></i> '.htmlspecialchars($row['status']).'</span></td>
                                     </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6"><i class="fas fa-inbox"></i> No submissions found.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <script>
    function toggleNotifBar() {
        const bar = document.getElementById('notif-bar');
        bar.classList.toggle('show');
        const badge = document.getElementById('notif-count');
        if (bar.classList.contains('show') && badge) {
            badge.style.display = 'none';
        }
    }

    function openRequestModal() {
        document.getElementById('request-modal').classList.add('show');
    }

    function closeRequestModal(event) {
        if (!event || event.target.id === 'request-modal') {
            document.getElementById('request-modal').classList.remove('show');
        }
    }

    function confirmRequest(requestId, companyId, companyName) {
        if (!confirm('Are you sure you want to confirm this pickup request?')) {
            return;
        }

        const card = document.getElementById('req-card-' + requestId);
        const buttons = card.querySelectorAll('button');
        buttons.forEach(btn => btn.disabled = true);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../backend/confirm_pickup.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(100%)';
                        card.style.height = card.offsetHeight + 'px';

                        setTimeout(() => {
                            card.style.height = '0';
                            card.style.padding = '0';
                            card.style.margin = '0';
                        }, 400);

                        setTimeout(() => {
                            card.remove();
                            if (document.querySelectorAll('.request-card').length === 0) {
                                location.reload();
                            }
                        }, 800);

                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                        buttons.forEach(btn => btn.disabled = false);
                    }
                } catch (e) {
                    alert('Server error. Please try again.');
                    buttons.forEach(btn => btn.disabled = false);
                }
            }
        };

        xhr.onerror = function() {
            alert('Network error. Please try again.');
            buttons.forEach(btn => btn.disabled = false);
        };

        xhr.send('request_id=' + requestId + '&company_id=' + companyId + '&company_name=' + encodeURIComponent(
            companyName));
    }

    function cancelRequest(requestId, companyId) {
        if (!confirm('Are you sure you want to cancel this request?')) {
            return;
        }

        const card = document.getElementById('req-card-' + requestId);
        const buttons = card.querySelectorAll('button');
        buttons.forEach(btn => btn.disabled = true);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../backend/cancel_request.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        card.style.transition = 'all 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateX(-100%)';
                        card.style.height = card.offsetHeight + 'px';

                        setTimeout(() => {
                            card.style.height = '0';
                            card.style.padding = '0';
                            card.style.margin = '0';
                        }, 400);

                        setTimeout(() => {
                            card.remove();
                            if (document.querySelectorAll('.request-card').length === 0) {
                                location.reload();
                            }
                        }, 800);

                        alert(response.message);
                    } else {
                        alert('Error: ' + response.message);
                        buttons.forEach(btn => btn.disabled = false);
                    }
                } catch (e) {
                    alert('Server error. Please try again.');
                    buttons.forEach(btn => btn.disabled = false);
                }
            }
        };

        xhr.onerror = function() {
            alert('Network error. Please try again.');
            buttons.forEach(btn => btn.disabled = false);
        };

        xhr.send('request_id=' + requestId + '&company_id=' + companyId);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeRequestModal();
        }
    });
    </script>
</body>

</html>