<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Submit E-Waste</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/requestform.css" />
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <h2>Dashboard</h2>
            <a href="user_dashboard.php"><i class="fas fa-home"></i>Dashboard</a>
            <a href="#" class="active"><i class="fas fa-file-alt"></i>My Submissions</a>
            <a href="nearby_companies.php"><i class="fas fa-building"></i>Nearby Companies</a>
            <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
            <a href="user_settings.php"><i class="fas fa-cogs"></i>Settings</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </aside>

        <main class="main-content">
            <div class="form-container">
                <h2>Submit Your E-Waste</h2>
                <p>Schedule a pickup for your electronic items</p>

                <?php
        if(isset($_SESSION['message'])){
            echo '<p class="success">'.$_SESSION['message'].'</p>';
            unset($_SESSION['message']);
        }
        ?>
                <div class="message">
                    <?php
            if(isset($_SESSION['success_message'])){
                echo '<p class="success-msg" style="color: green; font-size: 18px;">'.$_SESSION['success_message'].'</p>';
                unset($_SESSION['success_message']);
            }
            if(isset($_SESSION['error_message'])){
                echo '<p class="error-msg">'.$_SESSION['error_message'].'</p>';
                unset($_SESSION['error_message']);
            }
            ?>
                </div>
                <form id="ewasteForm" method="POST" action="../backend/process_request.php">
                    <div class="form-group">
                        <label>Device Type</label>
                        <select name="device_type" required>
                            <option value="">-- Select Device --</option>
                            <?php
                  include '../database/connection.php';
                  $result = $conn->query("SELECT DISTINCT category FROM items ORDER BY category ASC");
                  if($result->num_rows > 0){
                      while($row = $result->fetch_assoc()){
                          echo '<option value="'.$row['category'].'">'.$row['category'].'</option>';
                      }
                  } else {
                      echo '<option disabled>No items found</option>';
                  }
                  ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Company</label>
                        <select name="company_id" required>
                            <option value="">-- Select Company --</option>
                            <?php
              $result = $conn->query("SELECT company_id, company_name FROM companies");
              while($row = $result->fetch_assoc()){
                  echo '<option value="'.$row['company_id'].'">'.$row['company_name'].'</option>';
              }
              ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Condition</label>
                        <select name="device_condition" required>
                            <option value="">-- Select Condition --</option>
                            <option>Working</option>
                            <option>Old</option>
                            <option>Broken</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" min="1" required />
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="tel" name="contact" pattern="[0-9]{11}" placeholder="01xxxxxxxxx" required />
                    </div>

                    <div class="form-group">
                        <label>Pickup Date</label>
                        <input type="date" name="pickup_date" required />
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <textarea name="address" rows="3" placeholder="Enter your pickup address..."
                            required></textarea>
                    </div>

                    <button type="submit">Submit Request</button>
                </form>

            </div>
        </main>
    </div>
</body>

</html>