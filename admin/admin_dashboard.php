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
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
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
    document.getElementById('usersApply').addEventListener('click', function() {
        const filter = document.getElementById('usersSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr'); // শুধু Users table
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    document.getElementById('companiesApply').addEventListener('click', function() {
        const filter = document.getElementById('companiesSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#companiesTable tbody tr'); // শুধু Companies table
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
    </script>
</body>

</html>