<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

if(isset($_GET['delete'])){
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM requests WHERE company_id = $delete_id");
    $conn->query("DELETE FROM companies WHERE company_id = $delete_id");
    header("Location: companies.php");
    exit;
}

$search = "";
$query = "
    SELECT c.company_id, c.company_name, c.email, c.contact, c.address, c.created_at,
           COUNT(r.request_id) AS total_pickups
    FROM companies c
    LEFT JOIN requests r 
           ON c.company_id = r.company_id AND r.status='Completed'
    WHERE 1
";

if(!empty($_GET['search'])){
    $search = $conn->real_escape_string($_GET['search']);
    $query .= " AND (c.company_name LIKE '%$search%' OR c.email LIKE '%$search%' OR c.contact LIKE '%$search%' OR c.address LIKE '%$search%')";
}

$query .= " GROUP BY c.company_id ORDER BY c.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin - Companies Page</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/global_admin.css" />
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <h2>E-TRIEVE</h2>
            <span class="admin-badge">ADMIN PANEL</span>
        </div>
        <nav class="nav-menu">
            <a href="../admin/admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="../admin/users.php"><i class="fas fa-users"></i><span>Users</span></a>
            <a href="../admin/items.php"><i class="fas fa-recycle"></i><span>E-Waste Items</span></a>
            <a href="../admin/companies.php" class="active"><i class="fas fa-building"></i><span>Companies</span></a>
            <a href="../admin/admin_settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="welcome-text">
                <h1>All Companies</h1>
                <p>Manage company data, pickups and total collected items.</p>
            </div>
            <div class="topbar-icons">
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-building"></i>
                    <h2>Companies List</h2>
                </div>
                <form class="table-actions" method="GET">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search companies..."
                            value="<?php echo htmlspecialchars($search); ?>" />
                    </div>
                    <button class="btn-export" type="submit">
                        <i class="fas fa-search"></i> Apply
                    </button>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-building"></i> Company Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-phone"></i> Contact</th>
                        <th><i class="fas fa-recycle"></i> Total Picked Up</th>
                        <th><i class="fas fa-calendar-alt"></i> Joined Date</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact'] ?? 'N/A'); ?></td>
                        <td><?php echo $row['total_pickups']; ?> pickups</td>
                        <td><?php echo date("Y-m-d", strtotime($row['created_at'])); ?></td>
                        <td class="btn-action">
                            <a href="view_company.php?company_id=<?php echo $row['company_id']; ?>"
                                class="action-btn btn-view" title="View">
                                <i class="fas fa-eye"></i> View
                            </a>

                            <a href="companies.php?delete=<?php echo $row['company_id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this company and its related requests?');"
                                class="action-btn btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No companies found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>