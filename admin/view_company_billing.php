<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'){
    header("Location: ../auth/login.php");
    exit;
}

include '../database/connection.php';

$company_id = intval($_GET['company_id'] ?? 0);

$company_result = $conn->query("SELECT * FROM companies WHERE company_id = $company_id");
if($company_result->num_rows == 0){
    header("Location: company_billing.php");
    exit;
}
$company = $company_result->fetch_assoc();

$requests_result = $conn->query("
    SELECT 
        r.request_id,
        r.device_type,
        r.quantity,
        r.estimated_value,
        r.final_value,
        r.status,
        r.pickup_date,
        r.created_at,
        u.name as customer_name,
        u.user_id
    FROM requests r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.company_id = $company_id
    ORDER BY r.created_at DESC
");

$per_request_fee = 25;
$commission_rate = 5;

$accepted_completed_requests = [];
$completed_requests = [];
$other_requests = [];

while($req = $requests_result->fetch_assoc()){
    if($req['status'] == 'Accepted' || $req['status'] == 'Completed'){
        $accepted_completed_requests[] = $req;
    }
    if($req['status'] == 'Completed'){
        $completed_requests[] = $req;
    }
    if($req['status'] == 'Pending' || $req['status'] == 'Rejected'){
        $other_requests[] = $req;
    }
}

$total_accepted_completed = count($accepted_completed_requests);
$per_request_charge = $total_accepted_completed * $per_request_fee;

$total_final_value = 0;
foreach($completed_requests as $req){
    $total_final_value += floatval($req['final_value']);
}
$commission = ($total_final_value * $commission_rate) / 100;
$grand_total = $per_request_charge + $commission;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Company Billing Details - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/view_company_billing.css" />

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
        <div class="company-header">
            <div class="company-info">
                <h2><i class="fas fa-building"></i> <?php echo htmlspecialchars($company['company_name']); ?></h2>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($company['email']); ?></p>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($company['contact']); ?></p>
                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['address']); ?></p>
            </div>
            <a href="company_billing.php" class="back-btn-header">
                <i class="fas fa-arrow-left"></i> Back to Billing
            </a>
        </div>

        <div class="billing-cards">
            <div class="billing-card card-blue">
                <i class="fas fa-receipt icon"></i>
                <h4>Accepted/Completed Requests</h4>
                <p class="amount"><?php echo $total_accepted_completed; ?></p>
                <small>Per Request Fee: <?php echo number_format($per_request_charge, 2); ?> BDT</small>
            </div>

            <div class="billing-card card-green">
                <i class="fas fa-check-circle icon"></i>
                <h4>Completed Requests</h4>
                <p class="amount"><?php echo count($completed_requests); ?></p>
                <small>Total Final Value: <?php echo number_format($total_final_value, 2); ?> BDT</small>
            </div>

            <div class="billing-card card-orange">
                <i class="fas fa-percent icon"></i>
                <h4>Commission (5%)</h4>
                <p class="amount"><?php echo number_format($commission, 2); ?> BDT</p>
                <small>From completed requests</small>
            </div>

            <div class="billing-card card-purple">
                <i class="fas fa-dollar-sign icon"></i>
                <h4>Total Amount Due</h4>
                <p class="amount"><?php echo number_format($grand_total, 2); ?> BDT</p>
                <small>Request Fee + Commission</small>
            </div>
        </div>

        <div class="section-title">
            <h3><i class="fas fa-check-double"></i> Accepted/Completed Requests (Charged 25 BDT each)</h3>
        </div>

        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Customer</th>
                        <th>Device Type</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Final Value</th>
                        <th>5% Commission</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($accepted_completed_requests) > 0): ?>
                    <?php foreach($accepted_completed_requests as $req): 
                            $req_commission = 0;
                            if($req['status'] == 'Completed' && $req['final_value']){
                                $req_commission = (floatval($req['final_value']) * 5) / 100;
                            }
                        ?>
                    <tr>
                        <td><strong>#<?php echo $req['request_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($req['customer_name']); ?> (ID: <?php echo $req['user_id']; ?>)
                        </td>
                        <td><?php echo htmlspecialchars($req['device_type']); ?></td>
                        <td><?php echo $req['quantity']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                <?php echo $req['status']; ?>
                            </span>
                        </td>
                        <td class="amount-cell">
                            <?php echo $req['final_value'] ? number_format($req['final_value'], 2) . ' BDT' : 'N/A'; ?>
                        </td>
                        <td>
                            <?php if($req_commission > 0): ?>
                            <span class="commission-cell"><?php echo number_format($req_commission, 2); ?> BDT</span>
                            <?php else: ?>
                            <span style="color: #999;">0.00 BDT</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                            No accepted or completed requests found.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(count($other_requests) > 0): ?>
        <div class="section-title">
            <h3><i class="fas fa-info-circle"></i> Other Requests (Not Charged)</h3>
        </div>

        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Customer</th>
                        <th>Device Type</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($other_requests as $req): ?>
                    <tr>
                        <td><strong>#<?php echo $req['request_id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($req['customer_name']); ?> (ID: <?php echo $req['user_id']; ?>)
                        </td>
                        <td><?php echo htmlspecialchars($req['device_type']); ?></td>
                        <td><?php echo $req['quantity']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($req['status']); ?>">
                                <?php echo $req['status']; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($req['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>

</html>