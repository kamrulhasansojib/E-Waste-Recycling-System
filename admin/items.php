<?php
session_start();
include '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO items (item_name, category, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['item_name'], $_POST['category'], $_POST['description']);
    $stmt->execute();
    header("Location: items.php");
    exit;
}

if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM items WHERE item_id = $delete_id");
    header("Location: items.php");
    exit;
}

$search = "";
$query = "SELECT * FROM items WHERE 1";

if (!empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query .= " AND (item_name LIKE '%$search%' OR category LIKE '%$search%')";
}

$query .= " ORDER BY item_id DESC";
$items = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Admin - E-Waste Items</title>
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
            <a href="../admin/items.php" class="active"><i class="fas fa-recycle"></i><span>E-Waste Items</span></a>
            <a href="../admin/companies.php"><i class="fas fa-building"></i><span>Companies</span></a>
            <a href="../admin/admin_settings.php"><i class="fas fa-cogs"></i><span>Settings</span></a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
        </nav>
    </div>

    <div class="main">
        <div class="topbar">
            <div class="welcome-text">
                <h1>E-Waste Items</h1>
                <p>Manage all electronic waste items and add new items.</p>
            </div>
            <div class="topbar-icons">
                <div class="user-profile">
                    <i class="fas fa-user-circle"></i>
                    <span>Admin</span>
                </div>
            </div>
        </div>

        <div class="form-container">
            <div class="form-header">
                <i class="fas fa-plus-circle"></i>
                <h2>Register New E-Waste Item</h2>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Item Name</label>
                    <input type="text" name="item_name" placeholder="Enter item name" required />
                </div>

                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Category</label>
                    <input type="text" name="category" placeholder="Enter category" required />
                </div>

                <div class="form-group full">
                    <label><i class="fas fa-file-alt"></i> Description</label>
                    <textarea name="description" placeholder="Short description..."></textarea>
                </div>

                <button class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Add Item
                </button>
            </form>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-recycle"></i>
                    <h2>All E-Waste Items</h2>
                </div>

                <form class="table-actions" method="GET">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search items..." value="<?php echo $search; ?>" />
                    </div>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> Item ID</th>
                        <th><i class="fas fa-box"></i> Item Name</th>
                        <th><i class="fas fa-layer-group"></i> Category</th>
                        <th><i class="fas fa-file-alt"></i> Description</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($items)) { ?>
                    <tr>
                        <td><?php echo $row['item_id']; ?></td>
                        <td><?php echo $row['item_name']; ?></td>
                        <td><?php echo $row['category']; ?></td>
                        <td><?php echo $row['description']; ?></td>

                        <td class="btn-action">
                            <a href="edit_item.php?item_id=<?php echo $row['item_id']; ?>" class="action-btn btn-edit">
                                <i class="fas fa-pen"></i> Edit
                            </a>
                            <a href="items.php?delete=<?php echo $row['item_id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this item?');"
                                class="action-btn btn-delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>