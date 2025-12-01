<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$companies_result = $conn->query("
    SELECT 
        c.company_id,
        c.company_name,
        c.email,
        c.contact,
        c.address,
        COUNT(CASE WHEN r.status IN ('Accepted', 'Completed') THEN 1 END) as accepted_completed_count,
        COUNT(CASE WHEN r.status = 'Completed' THEN 1 END) as completed_count,
        SUM(CASE WHEN r.status = 'Completed' THEN r.final_value ELSE 0 END) as total_final_value
    FROM companies c
    LEFT JOIN requests r ON c.company_id = r.company_id
    GROUP BY c.company_id
    ORDER BY c.company_name ASC
");

$per_request_fee = 25;
$commission_rate = 5;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Company Billing - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/company_billing.css" />
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <h2>E-TRIEVE</h2>
            <span class="admin-badge">ADMIN PANEL</span>
        </div>

        <nav class="nav-menu">
            <a href="../admin/admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="../admin/users.php"><i class="fas fa-users"></i> Users</a>
            <a href="../admin/companies.php"><i class="fas fa-building"></i> Companies</a>
            <a href="../admin/company_billing.php" class="active"><i class="fas fa-file-invoice-dollar"></i> Company
                Billing</a>
            <a href="../admin/items.php"><i class="fas fa-recycle"></i> E-Waste Items</a>
            <a href="../admin/admin_settings.php"><i class="fas fa-cogs"></i> Settings</a>
            <a href="../auth/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main">
        <div class="billing-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Company Billing Overview</h1>
            <p>View billing details for all companies</p>
        </div>

        <div class="companies-grid">
            <?php if($companies_result->num_rows > 0): ?>
            <?php while($company = $companies_result->fetch_assoc()): 
                    $accepted_completed = intval($company['accepted_completed_count']);
                    $per_request_charge = $accepted_completed * $per_request_fee;
                    
                    $total_final_value = floatval($company['total_final_value']);
                    $commission = ($total_final_value * $commission_rate) / 100;
                    
                    $grand_total = $per_request_charge + $commission;
                ?>
            <div class="company-card">
                <h3>
                    <i class="fas fa-building" style="color: #065f46;"></i>
                    <?php echo htmlspecialchars($company['company_name']); ?>
                </h3>

                <div class="company-info">
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($company['email']); ?></p>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($company['contact']); ?></p>
                </div>

                <div class="billing-summary">
                    <div class="billing-row">
                        <span>Accepted/Completed:</span>
                        <strong><?php echo $accepted_completed; ?> requests</strong>
                    </div>
                    <div class="billing-row">
                        <span>Request Fees:</span>
                        <strong><?php echo number_format($per_request_charge, 2); ?> BDT</strong>
                    </div>
                    <div class="billing-row">
                        <span>Commission (5%):</span>
                        <strong><?php echo number_format($commission, 2); ?> BDT</strong>
                    </div>
                    <div class="billing-row">
                        <span>Total Due:</span>
                        <strong><?php echo number_format($grand_total, 2); ?> BDT</strong>
                    </div>
                </div>

                <a href="view_company_billing.php?company_id=<?php echo $company['company_id']; ?>"
                    class="view-details-btn">
                    <i class="fas fa-eye"></i> View Details
                </a>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="no-companies">
                <i class="fas fa-building"></i>
                <h3>No Companies Found</h3>
                <p>There are no companies registered yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>