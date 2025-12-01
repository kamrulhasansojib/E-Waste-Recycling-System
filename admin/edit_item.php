<?php
session_start();
include '../database/connection.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

if(!isset($_GET['item_id'])){
    echo "Invalid Item ID";
    exit;
}

$item_id = intval($_GET['item_id']);

$item_result = $conn->query("SELECT * FROM items WHERE item_id = $item_id");
if($item_result->num_rows === 0){
    echo "Item not found";
    exit;
}

$item = $item_result->fetch_assoc();

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['item_name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $estimated_value = $_POST['estimated_value'];
    $priority_point = $_POST['priority_point']; 

    $stmt = $conn->prepare("UPDATE items 
        SET item_name=?, category=?, description=?, estimated_value=?, priority_point=? 
        WHERE item_id=?");

    $stmt->bind_param("ssssii", $name, $category, $description, $estimated_value, $priority_point, $item_id);
    $stmt->execute();

    header("Location: items.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/view.css">
</head>

<body>
    <div class="container" style="width: 100%; max-width: 600px;">
        <a href="items.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Items
        </a>

        <div class="form-card">
            <div class="form-header">
                <div class="icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h2>Edit E-Waste Item</h2>
                <p>Update item details and information</p>
            </div>

            <div class="item-info">
                <p><strong>Item ID:</strong> #<?php echo $item['item_id']; ?></p>
            </div>

            <form method="POST">

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Item Name</label>
                    <input type="text" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-layer-group"></i> Category</label>
                    <input type="text" name="category" value="<?php echo htmlspecialchars($item['category']); ?>"
                        required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Estimated Value</label>
                    <input type="text" name="estimated_value"
                        value="<?php echo htmlspecialchars($item['estimated_value']); ?>" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-star"></i> Priority Point</label>
                    <input type="number" name="priority_point"
                        value="<?php echo htmlspecialchars($item['priority_point']); ?>" placeholder="5–20" min="1"
                        max="50" required>
                </div>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='items.php'">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Item
                    </button>
                </div>

            </form>
        </div>
    </div>
</body>

</html>