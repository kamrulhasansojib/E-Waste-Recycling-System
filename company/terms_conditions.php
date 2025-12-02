<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'company'){
    header("Location: ../auth/login.php");
    exit;
}

$company_name = $_SESSION['company_name'] ?? 'Company';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms & Conditions - E-TRIEVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/terms_conditions.css" />
</head>

<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-recycle"></i>
                <h2>E-TRIEVE</h2>
                <span class="admin-badge">COMPANY PANEL</span>
            </div>
            <nav>
                <a href="../company/company_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../company/requests.php"><i class="fas fa-inbox"></i> Requests</a>
                <a href="../company/completed.php"><i class="fas fa-check-circle"></i> Completed</a>
                <a href="../company/company_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../company/terms_conditions.php" class="active"><i class="fas fa-file-contract"></i> T&C</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="terms-container">
                <div class="terms-header">
                    <h1><i class="fas fa-file-contract"></i> Terms & Conditions</h1>
                    <p>Billing & Commission Policy for Partner Companies</p>
                </div>

                <div class="section">
                    <h2><i class="fas fa-info-circle"></i> Introduction</h2>
                    <p>
                        Welcome to E-TRIEVE's partner program! As a registered recycling company, you agree to the
                        following terms and conditions regarding billing, commissions, and service fees for using our
                        platform.
                    </p>
                </div>

                <div class="section">
                    <h2><i class="fas fa-dollar-sign"></i> Billing Structure</h2>
                    <p>
                        E-TRIEVE operates on a transparent and fair billing model to ensure sustainable operations while
                        providing value to our partner companies.
                    </p>

                    <div class="highlight-box">
                        <h3><i class="fas fa-calculator"></i> How Billing Works</h3>
                        <p>Your total bill is calculated based on two components: Per Request Fee and Commission.</p>
                    </div>

                    <div class="pricing-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Fee Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Per Request Fee</strong></td>
                                    <td>Charged when you accept a pickup request</td>
                                    <td><strong>25 BDT</strong> per request</td>
                                </tr>
                                <tr>
                                    <td><strong>Commission</strong></td>
                                    <td>Percentage of final transaction value (completed requests only)</td>
                                    <td><strong>5%</strong> of final value</td>
                                </tr>
                                <tr>
                                    <td colspan="2"><strong>Total Bill</strong></td>
                                    <td><strong>(Accepted Requests × 25) + (5% of Completed Requests)</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-receipt"></i> Per Request Fee (25 BDT)</h2>
                    <p>
                        A flat fee of <strong>25 BDT</strong> is charged for every pickup request you accept through our
                        platform.
                    </p>
                    <ul>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>When is it charged?</strong> As soon as you click "Accept" on a pending
                                request</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Why this fee?</strong> Covers platform maintenance, customer support, and lead
                                generation</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Applies to:</strong> All accepted requests, regardless of whether they're
                                completed or not</span>
                        </li>
                        <li>
                            <i class="fas fa-times-circle" style="color: #e74c3c;"></i>
                            <span><strong>Not charged for:</strong> Rejected or pending requests</span>
                        </li>
                    </ul>

                    <div class="warning-box">
                        <h3><i class="fas fa-exclamation-triangle"></i> Important Note</h3>
                        <p>The 25 BDT fee is non-refundable, even if the user later cancels the request or if the pickup
                            is not completed.</p>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-percent"></i> Commission (5%)</h2>
                    <p>
                        A <strong>5% commission</strong> is charged on the final transaction value of all completed
                        pickups.
                    </p>
                    <ul>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>When is it charged?</strong> Only when you mark a request as
                                "Completed"</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Calculated on:</strong> The final value you offer to the user (final_value
                                field)</span>
                        </li>
                        <li>
                            <i class="fas fa-check-circle"></i>
                            <span><strong>Example:</strong> If final value = 1000 BDT, commission = 50 BDT (5%)</span>
                        </li>
                        <li>
                            <i class="fas fa-times-circle" style="color: #e74c3c;"></i>
                            <span><strong>Not charged for:</strong> Accepted but not completed requests</span>
                        </li>
                    </ul>

                    <div class="highlight-box">
                        <h3><i class="fas fa-lightbulb"></i> Commission Calculation Example</h3>
                        <p><strong>Scenario:</strong> You completed 10 requests with a total final value of 50,000 BDT
                        </p>
                        <p><strong>Commission:</strong> 50,000 × 5% = <strong>2,500 BDT</strong></p>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-file-invoice-dollar"></i> Complete Billing Example</h2>
                    <p>Let's see how your monthly bill would be calculated:</p>

                    <div class="pricing-table">
                        <table>
                            <tbody>
                                <tr>
                                    <td><strong>Total Accepted Requests:</strong></td>
                                    <td>20 requests</td>
                                </tr>
                                <tr>
                                    <td><strong>Per Request Fee:</strong></td>
                                    <td>20 × 25 BDT = <strong>500 BDT</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Total Completed Requests:</strong></td>
                                    <td>15 requests</td>
                                </tr>
                                <tr>
                                    <td><strong>Total Final Value (Completed):</strong></td>
                                    <td>75,000 BDT</td>
                                </tr>
                                <tr>
                                    <td><strong>Commission (5%):</strong></td>
                                    <td>75,000 × 5% = <strong>3,750 BDT</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>TOTAL BILL:</strong></td>
                                    <td><strong>500 + 3,750 = 4,250 BDT</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-credit-card"></i> Payment Terms</h2>
                    <ul>
                        <li>
                            <i class="fas fa-calendar-check"></i>
                            <span><strong>Billing Cycle:</strong> Monthly (1st to last day of each month)</span>
                        </li>
                        <li>
                            <i class="fas fa-file-invoice"></i>
                            <span><strong>Invoice Generation:</strong> Invoices are generated on the 1st of every
                                month</span>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <span><strong>Payment Due:</strong> Within 15 days of invoice generation</span>
                        </li>
                        <li>
                            <i class="fas fa-mobile-alt"></i>
                            <span><strong>Payment Methods:</strong> Bank Transfer, bKash, Nagad, Rocket</span>
                        </li>
                    </ul>

                    <div class="warning-box">
                        <h3><i class="fas fa-exclamation-triangle"></i> Late Payment</h3>
                        <p>Failure to pay within the due date may result in temporary suspension of your account until
                            payment is received.</p>
                    </div>
                </div>

                <div class="section">
                    <h2><i class="fas fa-gavel"></i> Additional Terms</h2>
                    <ul>
                        <li>
                            <i class="fas fa-shield-alt"></i>
                            <span>E-TRIEVE reserves the right to modify billing terms with 30 days notice</span>
                        </li>
                        <li>
                            <i class="fas fa-ban"></i>
                            <span>Refunds are not available for accepted requests that were later cancelled by
                                users</span>
                        </li>
                        <li>
                            <i class="fas fa-chart-line"></i>
                            <span>You can view your billing breakdown anytime from your dashboard</span>
                        </li>
                        <li>
                            <i class="fas fa-handshake"></i>
                            <span>By using E-TRIEVE, you agree to these terms and conditions</span>
                        </li>
                    </ul>
                </div>

                <div class="contact-section">
                    <h3><i class="fas fa-question-circle"></i> Have Questions?</h3>
                    <p>If you have any questions about billing or need clarification, please contact our support team.
                    </p>
                    <p><i class="fas fa-envelope"></i> Email: billing@etrieve.com</p>
                    <p><i class="fas fa-phone"></i> Phone: +880-1234-567890</p>

                    <a href="company_dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>

</html>