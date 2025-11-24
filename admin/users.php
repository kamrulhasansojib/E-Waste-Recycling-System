<?php
session_start();
require "../database/connection.php";

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

if(isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE user_id = $delete_id");
    header("Location: users.php");
    exit;
}

$search = "";
$sql = "SELECT user_id, name, email, address, green_points, created_at FROM users WHERE role='user'";

if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR address LIKE '%$search%')";
}

$sql .= " ORDER BY user_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - Users Page</title>
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
            <a href="admin_dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="users.php" class="active"><i class="fas fa-users"></i><span>Users</span></a>
            <a href="items.php"><i class="fas fa-recycle"></i><span>E-Waste Items</span></a>
            <a href="companies.php"><i class="fas fa-building"></i><span>Companies</span></a>
            <a href="admin_settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="welcome-text">
                <h1>All Users</h1>
                <p>Manage all registered users and their addresses & reward points.</p>
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
                    <i class="fas fa-users"></i>
                    <h2>Users Details</h2>
                </div>
                <div class="table-actions">
                    <form method="GET">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" placeholder="Search users..."
                                value="<?php echo htmlspecialchars($search); ?>" />
                        </div>
                        <button type="submit" class="btn-export"><i class="fas fa-search"></i> Apply</button>
                    </form>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> User Name</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                        <th><i class="fas fa-star"></i> Reward Points</th>
                        <th><i class="fas fa-calendar-alt"></i> Registration Date</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): ?>
                    <?php while($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['address']); ?></td>
                        <td><?php echo $user['green_points'] ?? 0; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td class="btn-action">
                            <a href="view_user.php?user_id=<?php echo $user['user_id']; ?>" class="action-btn btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>

                            <a href="users.php?delete=<?php echo $user['user_id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this user?');"
                                class="action-btn btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No users found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>