<?php
session_start();
require "../database/connection.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT name, email, phone, address FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/settings.css" />
    <style>
    .message {
        margin: 15px 0;
    }

    .success-msg {
        color: green;
        font-weight: 600;
    }

    .error-msg {
        color: red;
        font-weight: bold;
    }
    </style>
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
                <a href="../user/user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a>
                <a href="../user/nearby_companies.php"><i class="fas fa-building"></i> Nearby Companies</a>
                <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
                <a href="#" class="active"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
        </aside>

        <main class="main-content">
            <div class="header">
                <h1><i class="fas fa-cogs"></i> User Settings</h1>
                <div class="user-info">
                    <p style="font-size: 18px; color: #09543fff; font-weight:700;">
                        <?php echo htmlspecialchars($user['name']); ?></p>
                    <i class="fas fa-user-circle"></i>
                </div>
            </div>

            <div class="settings-container">
                <div class="settings-section">
                    <h2 class="section-title"><i class="fas fa-user"></i> User Profile</h2>
                    <div class="message" id="profileMessage"></div>

                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="user_name" name="name" value="<?php echo $user['name']; ?>"
                            placeholder="Enter your name" />
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" id="user_email" name="email" value="<?php echo $user['email']; ?>"
                            placeholder="Enter email" />
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="user_phone" name="phone" value="<?php echo $user['phone']; ?>"
                            placeholder="Enter phone number (optional)" />
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <textarea id="user_address" name="address"
                            placeholder="Enter your address"><?php echo $user['address']; ?></textarea>
                    </div>
                    <div class="form-group">
                        <button class="btn-update" id="updateProfileBtn"><i class="fas fa-sync-alt"></i> Save
                            Changes</button>
                    </div>
                </div>

                <div class="settings-section">
                    <h2 class="section-title"><i class="fas fa-shield-alt"></i> Account Security</h2>
                    <div class="message" id="passwordMessage"></div>

                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" id="current_pass" placeholder="Enter current password" />
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" id="new_pass" placeholder="Enter new password" />
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" id="confirm_pass" placeholder="Confirm new password" />
                        </div>
                    </div>
                    <div class="form-group">
                        <button class="btn-update" id="updatePasswordBtn"><i class="fas fa-sync-alt"></i> Update
                            Password</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    document.getElementById("updateProfileBtn").addEventListener("click", function() {
        let formData = new FormData();
        formData.append("name", document.getElementById("user_name").value);
        formData.append("email", document.getElementById("user_email").value);
        formData.append("phone", document.getElementById("user_phone").value);
        formData.append("address", document.getElementById("user_address").value);
        formData.append("update_profile", true);

        fetch("../backend/update_user.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById("profileMessage").innerHTML = data;
            })
            .catch(err => console.log(err));
    });

    document.getElementById("updatePasswordBtn").addEventListener("click", function() {
        let formData = new FormData();
        formData.append("current_pass", document.getElementById("current_pass").value);
        formData.append("new_pass", document.getElementById("new_pass").value);
        formData.append("confirm_pass", document.getElementById("confirm_pass").value);
        formData.append("update_password", true);

        fetch("../backend/update_user.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.text())
            .then(data => {
                document.getElementById("passwordMessage").innerHTML = data;
                document.getElementById("current_pass").value = "";
                document.getElementById("new_pass").value = "";
                document.getElementById("confirm_pass").value = "";
            })
            .catch(err => console.log(err));
    });
    </script>
</body>

</html>