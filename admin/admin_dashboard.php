<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_companies = $conn->query("SELECT COUNT(*) as count FROM companies")->fetch_assoc()['count'];
$total_ewaste = $conn->query("SELECT COALESCE(SUM(quantity),0) as total FROM requests WHERE status='Completed'")->fetch_assoc()['total'];
$accepted_requests = $conn->query("SELECT COUNT(*) as count FROM requests WHERE status='Accepted'")->fetch_assoc()['count'];

$stmt_notif = $conn->query("
    SELECT 
        n.notification_id,
        n.message,
        n.created_at,
        n.user_id,
        n.company_id,
        u.name as user_name,
        c.company_name,
        r.request_id,
        r.device_type,
        r.status,
        sender_user.name as sender_user_name,
        sender_company.company_name as sender_company_name
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.user_id
    LEFT JOIN companies c ON n.company_id = c.company_id
    LEFT JOIN requests r ON (n.user_id = r.user_id AND n.company_id = r.company_id)
    LEFT JOIN users sender_user ON (n.company_id IS NOT NULL AND r.user_id = sender_user.user_id)
    LEFT JOIN companies sender_company ON (n.company_id IS NULL AND r.company_id = sender_company.company_id)
    WHERE n.is_read = 0
    GROUP BY n.notification_id
    ORDER BY n.created_at DESC
    LIMIT 20
");

$notifications = [];
$unread_count = 0;
if($stmt_notif->num_rows > 0){
    while($row = $stmt_notif->fetch_assoc()){
        $formatted_msg = '';
        
        if(!empty($row['company_id']) && !empty($row['user_name'])){
            $company_name = $row['company_name'] ?: 'Unknown Company';
            $formatted_msg = "User <strong>" . htmlspecialchars($row['user_name']) . " (" . $row['user_id'] . ")</strong> has cancelled request with <strong>" . htmlspecialchars($company_name) . " (" . $row['company_id'] . ")</strong>";
            if(!empty($row['device_type'])){
                $formatted_msg .= " for " . htmlspecialchars($row['device_type']);
            }
        }
        elseif(empty($row['company_id']) && !empty($row['sender_company_name'])){
            $user_name = $row['user_name'] ?: 'Unknown User';
            $formatted_msg = "Company <strong>" . htmlspecialchars($row['sender_company_name']) . "</strong> " . strtolower($row['message']) . " for User <strong>" . htmlspecialchars($user_name) . " (" . $row['user_id'] . ")</strong>";
        }
        else {
            $formatted_msg = htmlspecialchars($row['message']);
        }
        
        $row['formatted_message'] = $formatted_msg;
        $notifications[] = $row;
        $unread_count++;
    }
}

$users_result = $conn->query("
    SELECT 
        u.user_id, 
        u.name, 
        u.email, 
        u.green_points, 
        COALESCE(SUM(r.quantity),0) as total_items,
        COALESCE(SUM(CASE WHEN r.status='Completed' THEN r.quantity ELSE 0 END),0) as completed_pickups
    FROM users u
    LEFT JOIN requests r ON u.user_id = r.user_id
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
");

$companies_result = $conn->query("
    SELECT c.company_id, c.company_name, c.email, c.contact, c.address, c.created_at,
           COALESCE(SUM(CASE WHEN r.status IN ('Accepted','Completed') THEN 1 ELSE 0 END),0) as total_pickups,
           COALESCE(SUM(CASE WHEN r.status='Completed' THEN 1 ELSE 0 END),0) as completed
    FROM companies c
    LEFT JOIN requests r ON c.company_id = r.company_id
    GROUP BY c.company_id
    ORDER BY c.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css" />
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <h2>E-TRIEVE</h2>
            <span class="admin-badge">ADMIN PANEL</span>
        </div>

        <nav class="nav-menu">
            <a href="#" class="active"><i class="fas fa-home"></i> Dashboard</a>
            <a href="../admin/users.php"><i class="fas fa-users"></i> Users</a>
            <a href="../admin/companies.php"><i class="fas fa-building"></i> Companies</a>
            <a href="../admin/items.php"><i class="fas fa-recycle"></i> E-Waste Items</a>
            <a href="../admin/company_billing.php"><i class="fas fa-file-invoice-dollar"></i>Company Billing</a>
            <a href="../admin/admin_settings.php"><i class="fas fa-cogs"></i> Settings</a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="welcome-text">
                <h1>Admin Dashboard</h1>
                <p>Welcome back! Here's what's happening today.</p>
            </div>
            <div class="topbar-icons">
                <div class="notification-bell" onclick="toggleNotifBar()">
                    <i class="fas fa-bell"></i>
                    <?php if($unread_count > 0){ ?>
                    <span class="badge" id="notif-count"><?php echo $unread_count; ?></span>
                    <?php } ?>
                </div>
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>


        <div id="notif-overlay" onclick="toggleNotifBar()"></div>

        <div id="notif-bar">
            <h3>All Activities <span class="close-btn" onclick="toggleNotifBar()">×</span></h3>
            <?php if(count($notifications) > 0){ ?>
            <?php foreach($notifications as $n){ ?>
            <div class="notif-item">
                <div class="notif-msg"><?php echo $n['formatted_message']; ?></div>
                <small><i class="far fa-clock"></i> <?php echo htmlspecialchars($n['created_at']); ?></small>
            </div>
            <?php } ?>
            <?php } else { ?>
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No new activities</p>
            </div>
            <?php } ?>
        </div>

        <div class="cards">
            <div class="card card-blue">
                <div class="card-icon"><i class="fas fa-users"></i></div>
                <div class="card-content">
                    <h3>Total Users</h3>
                    <p class="card-number"><?php echo $total_users; ?></p>
                </div>
            </div>

            <div class="card card-green">
                <div class="card-icon"><i class="fas fa-trash-alt"></i></div>
                <div class="card-content">
                    <h3>Total E-Waste</h3>
                    <p class="card-number"><?php echo $total_ewaste; ?></p>
                </div>
            </div>

            <div class="card card-orange">
                <div class="card-icon"><i class="fas fa-clock"></i></div>
                <div class="card-content">
                    <h3>Accept Requests</h3>
                    <p class="card-number"><?php echo $accepted_requests; ?></p>
                </div>
            </div>

            <div class="card card-purple">
                <div class="card-icon"><i class="fas fa-building"></i></div>
                <div class="card-content">
                    <h3>Total Companies</h3>
                    <p class="card-number"><?php echo $total_companies; ?></p>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-users"></i>
                    <h2>Users & Reward Points</h2>
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="usersSearch" placeholder="Search users..." />
                    </div>
                    <button class="btn-export" id="usersApply">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </div>

            <table id="usersTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> User Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-box"></i> Submitted Items</th>
                        <th><i class="fas fa-check-circle"></i> Completed Pickups</th>
                        <th><i class="fas fa-star"></i> Reward Points</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users_result->num_rows > 0): ?>
                    <?php while($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="user-cell"><span><?php echo htmlspecialchars($user['name']); ?></span></div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="badge badge-info"><?php echo $user['total_items']; ?></span></td>
                        <td><span class="badge badge-success"><?php echo $user['completed_pickups']; ?></span></td>
                        <td><span class="points"><?php echo $user['green_points']; ?></span></td>
                        <td>
                            <a href="view_user.php?user_id=<?php echo $user['user_id']; ?>" class="action-btn btn-view">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-building"></i>
                    <h2>Companies & Pickups</h2>
                </div>
                <div class="table-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="companiesSearch" placeholder="Search companies..." />
                    </div>
                    <button class="btn-export" id="companiesApply">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </div>
            </div>

            <table id="companiesTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-building"></i> Company Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                        <th><i class="fas fa-box"></i> Total Pickups</th>
                        <th><i class="fas fa-check-circle"></i> Completed</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($companies_result->num_rows > 0): ?>
                    <?php while($company = $companies_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="company-cell">
                                <span><?php echo htmlspecialchars($company['company_name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($company['email']); ?></td>
                        <td><?php echo htmlspecialchars($company['address']); ?></td>
                        <td><?php echo $company['total_pickups']; ?></td>
                        <td><?php echo $company['completed']; ?></td>
                        <td>
                            <a href="view_company.php?company_id=<?php echo $company['company_id']; ?>"
                                class="action-btn btn-view" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6">No companies found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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

    document.getElementById('usersApply').addEventListener('click', function() {
        const filter = document.getElementById('usersSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.getElementById('companiesApply').addEventListener('click', function() {
        const filter = document.getElementById('companiesSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#companiesTable tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
    </script>
</body>

</html>