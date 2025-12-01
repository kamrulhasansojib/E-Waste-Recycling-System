<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
include '../database/connection.php';

$sql = "SELECT company_name, address, email, motto FROM companies ORDER BY company_name ASC";
$result = $conn->query($sql);
$companies = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>User Dashboard - Nearby Companies</title>
    <link rel="stylesheet" href="../assets/css/nearby_companies.css" />
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
                <a href="../user/user_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="../user/requestform.php"><i class="fas fa-file-alt"></i> My Submissions</a>
                <a href="../user/nearby_companies.php" class="active"><i class="fas fa-building"></i> Nearby
                    Companies</a>
                <a href="../user/achievements.php"><i class="fas fa-trophy"></i> Achievements</a>
                <a href="../user/user_history.php"><i class="fas fa-history"></i> All History</a>
                <a href="../user/user_settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
            <a href="../Home.html" class="back-btn"><i class="fa-solid fa-house"></i></a>
        </aside>

        <main class="main-content">
            <h1>Nearby Companies</h1>

            <div class="search-container">
                <input type="text" id="searchBar" placeholder="Search by address..." />
                <button id="actionBtn" class="search-btn" onclick="handleButtonClick()">Search</button>
            </div>

            <div id="search-result" class="company-list"></div>

            <div id="companyList" class="company-list">
                <?php if (count($companies) > 0): ?>
                <?php foreach ($companies as $company): ?>
                <div class="company-card">
                    <h3><?= htmlspecialchars($company['company_name']) ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> Address: <?= htmlspecialchars($company['address']) ?></p>
                    <p><i class="fas fa-envelope" style="color: #065f46;"></i>
                        <?= htmlspecialchars($company['email']) ?></p>
                    <p>Motto: <?= htmlspecialchars($company['motto']) ?></p>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <p>No companies found.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    const companies = <?= json_encode($companies) ?>;

    let isSearching = false;

    function handleButtonClick() {
        if (isSearching) {
            clearSearch();
        } else {
            searchCompanies();
        }
    }

    function searchCompanies() {
        const query = document.getElementById('searchBar').value.toLowerCase().trim();
        const searchResult = document.getElementById('search-result');
        const companyList = document.getElementById('companyList');
        const actionBtn = document.getElementById('actionBtn');

        if (query === '') {
            return;
        }

        const filtered = companies.filter(c =>
            c.address.toLowerCase().includes(query)
        );

        companyList.classList.add('hidden');
        searchResult.classList.add('active');

        isSearching = true;
        actionBtn.textContent = 'Clear';

        if (filtered.length > 0) {
            searchResult.innerHTML = filtered.map(c => `
                    <div class="company-card">
                        <h3>${escapeHtml(c.company_name)}</h3>
                        <p><i class="fas fa-map-marker-alt"></i> Address: ${escapeHtml(c.address)}</p>
                        <p><i class="fas fa-envelope" style="color: #065f46;"></i> ${escapeHtml(c.email)}</p>
                        <p>Motto: ${escapeHtml(c.motto)}</p>
                    </div>
                `).join('');
        } else {
            searchResult.innerHTML = '<p class="no-result">No companies found for this address.</p>';
        }
    }

    function clearSearch() {
        const actionBtn = document.getElementById('actionBtn');

        document.getElementById('searchBar').value = '';
        document.getElementById('search-result').classList.remove('active');
        document.getElementById('search-result').innerHTML = '';
        document.getElementById('companyList').classList.remove('hidden');

        isSearching = false;
        actionBtn.textContent = 'Search';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    document.getElementById('searchBar').addEventListener('keyup', function(e) {
        if (e.key === 'Enter' && !isSearching) searchCompanies();
    });
    </script>
</body>

</html>