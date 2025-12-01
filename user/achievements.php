<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'user'){
    header("Location: ../auth/login.php");
    exit;
}

$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : "User";
$user_id = $_SESSION['user_id'];

include '../database/connection.php';

$stmt_points = $conn->prepare("SELECT green_points FROM users WHERE user_id = ?");
$stmt_points->bind_param("i", $user_id);
$stmt_points->execute();
$res_points = $stmt_points->get_result();
$green_points = 0;
if($res_points->num_rows > 0){
    $row_points = $res_points->fetch_assoc();
    $green_points = $row_points['green_points'];
}

$badges = [
    [
        'name' => 'Eco Beginner',
        'min_points' => 0,
        'max_points' => 99,
        'icon' => 'fas fa-seedling',
        'color' => '#90EE90',
        'description' => 'Start your green journey!'
    ],
    [
        'name' => 'Green Contributor',
        'min_points' => 100,
        'max_points' => 299,
        'icon' => 'fas fa-leaf',
        'color' => '#32CD32',
        'description' => 'Making a difference!'
    ],
    [
        'name' => 'Eco Warrior',
        'min_points' => 300,
        'max_points' => 599,
        'icon' => 'fas fa-tree',
        'color' => '#228B22',
        'description' => 'Fighting for the planet!'
    ],
    [
        'name' => 'Sustainability Champion',
        'min_points' => 600,
        'max_points' => 999,
        'icon' => 'fas fa-award',
        'color' => '#FFD700',
        'description' => 'Leading the green revolution!'
    ],
    [
        'name' => 'Earth Guardian',
        'min_points' => 1000,
        'max_points' => PHP_INT_MAX,
        'icon' => 'fas fa-globe-americas',
        'color' => '#FF6347',
        'description' => 'Ultimate environmental hero!'
    ]
];

$show_certificate = $green_points >= 500;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rewards & Certificates</title>
    <link rel="stylesheet" href="../assets/css/achivments.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
                <a href="user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a>
                <a href="../user/nearby_companies.php"><i class="fas fa-building"></i> Nearby Companies</a>
                <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
                <a href="../user/achievements.php" class="active"><i class="fas fa-trophy"></i> Achievements</a>
                <a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
        </aside>

        <main class="main-content">
            <div class="rewards-container">
                <div class="points-header">
                    <h1><i class="fas fa-trophy"></i> Your Green Journey</h1>
                    <p class="points-label">Your Current Green Points</p>
                    <div class="points-display">
                        <i class="fas fa-leaf"></i> <?php echo number_format($green_points); ?>
                    </div>
                    <p style="font-size: 1.1em;">Keep collecting and unlock amazing rewards!</p>
                </div>

                <div class="badges-section">
                    <h2 class="section-title">
                        <i class="fas fa-medal"></i> Achievement Badges
                    </h2>

                    <div class="badges-grid">
                        <?php foreach($badges as $badge): 
                            $is_unlocked = $green_points >= $badge['min_points'];
                            $is_current = $green_points >= $badge['min_points'] && $green_points <= $badge['max_points'];
                        ?>
                        <div class="badge-card <?php echo $is_unlocked ? 'unlocked' : 'locked'; ?>">
                            <span class="unlock-status <?php echo $is_unlocked ? 'unlocked' : 'locked'; ?>">
                                <?php echo $is_unlocked ? '✓ UNLOCKED' : '🔒 LOCKED'; ?>
                            </span>
                            <div class="badge-icon" style="color: <?php echo $badge['color']; ?>">
                                <i class="<?php echo $badge['icon']; ?>"></i>
                            </div>
                            <div class="badge-name"><?php echo $badge['name']; ?></div>
                            <div class="badge-range">
                                <?php 
                                    if($badge['max_points'] == PHP_INT_MAX) {
                                        echo number_format($badge['min_points']) . '+ Points';
                                    } else {
                                        echo number_format($badge['min_points']) . ' - ' . number_format($badge['max_points']) . ' Points';
                                    }
                                    ?>
                            </div>
                            <div class="badge-description"><?php echo $badge['description']; ?></div>

                            <?php if(!$is_unlocked): 
                                    $points_to_unlock = $badge['min_points'] - $green_points;
                                    $progress_percent = min(100, ($green_points / $badge['min_points']) * 100);
                                ?>
                            <div class="progress-bar" style="margin-top: 20px;">
                                <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%">
                                    <?php echo round($progress_percent); ?>%
                                </div>
                            </div>
                            <p style="margin-top: 10px; color: #e74c3c; font-weight: bold;">
                                <?php echo number_format($points_to_unlock); ?> points needed
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="certificate-section">
                    <h2 class="section-title">
                        <i class="fas fa-certificate"></i> Your Certificate of Achievement
                    </h2>

                    <?php if($show_certificate): ?>
                    <div class="certificate-container">
                        <div class="certificate-badge">✓ VERIFIED</div>

                        <i class="fas fa-leaf certificate-ornament ornament-tl"></i>
                        <i class="fas fa-leaf certificate-ornament ornament-tr"></i>
                        <i class="fas fa-leaf certificate-ornament ornament-bl"></i>
                        <i class="fas fa-leaf certificate-ornament ornament-br"></i>

                        <div class="certificate-header">
                            <div class="certificate-logo">
                                <i class="fas fa-award"></i>
                            </div>
                            <h1 class="certificate-title">Certificate of Achievement</h1>
                            <p class="certificate-subtitle">E-Waste Management Excellence</p>
                        </div>

                        <div class="certificate-body">
                            <p class="certificate-text">This is to certify that</p>
                            <h2 class="recipient-name"><?php echo htmlspecialchars($user_name); ?></h2>
                            <p class="certificate-text">
                                has demonstrated outstanding commitment to environmental sustainability<br>
                                by earning <strong
                                    style="color: #27ae60; font-size: 1.3em;"><?php echo number_format($green_points); ?>
                                    Green Points</strong><br>
                                through responsible e-waste recycling and management.
                            </p>
                            <p class="certificate-text" style="margin-top: 40px; color: #7f8c8d;">
                                <i class="fas fa-calendar-alt"></i> Issued on:
                                <strong><?php echo date('F d, Y'); ?></strong>
                            </p>
                        </div>

                        <div class="certificate-footer">
                            <div class="signature-line">
                                <hr>
                                <p class="signature-label">E-TRIEVE Team</p>
                                <p class="signature-sublabel">Environmental Director</p>
                            </div>
                            <div class="signature-line">
                                <hr>
                                <p class="signature-label">Certificate ID</p>
                                <p class="signature-sublabel">
                                    ET-<?php echo date('Y'); ?>-<?php echo str_pad($user_id, 6, '0', STR_PAD_LEFT); ?>
                                </p>
                            </div>
                        </div>

                        <div style="text-align: center;">
                            <a href="#" class="download-btn" onclick="window.print(); return false;">
                                <i class="fas fa-download"></i> Download Certificate
                            </a>
                        </div>
                    </div>
                    <?php else: 
                        $points_needed = 500 - $green_points;
                        $progress_percent = ($green_points / 500) * 100;
                    ?>
                    <div class="no-certificate">
                        <i class="fas fa-lock"></i>
                        <h3>Certificate Locked</h3>

                        <div class="points-needed-box">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo number_format($points_needed); ?> More Points Needed!
                        </div>

                        <p style="font-size: 1.2em; margin-top: 20px;">
                            You need <strong style="color: #e74c3c;"><?php echo number_format($points_needed); ?> more
                                points</strong> to unlock your certificate.
                        </p>
                        <p style="margin-top: 20px; color: #3498db; font-size: 1.1em;">
                            <i class="fas fa-lightbulb"></i> Keep recycling e-waste to earn more green points!
                        </p>

                        <div class="progress-bar" style="max-width: 600px; margin: 40px auto;">
                            <div class="progress-fill" style="width: <?php echo $progress_percent; ?>%">
                                <?php echo round($progress_percent); ?>% Complete
                            </div>
                        </div>

                        <p style="margin-top: 30px; color: #27ae60; font-size: 1.1em;">
                            <i class="fas fa-trophy"></i> Current Progress: <?php echo number_format($green_points); ?>
                            / 500 Points
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>

</html>