<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$userCount = 0;
$itemCount = 0;
$claimCount = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($result) {
    $userCount = (int)($result->fetch_assoc()['total'] ?? 0);
}

$result = $conn->query("SELECT COUNT(*) AS total FROM items");
if ($result) {
    $itemCount = (int)($result->fetch_assoc()['total'] ?? 0);
}

$result = $conn->query("SELECT COUNT(*) AS total FROM claims");
if ($result) {
    $claimCount = (int)($result->fetch_assoc()['total'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="../dashboard.php">User Dashboard</a>
            <a href="view_users.php">Users</a>
            <a href="view_items.php">Items</a>
            <a href="view_claims.php">Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Admin Overview</h2>
    </div>

    <section class="card-grid">
        <article class="stat-card">
            <p class="value"><?php echo $userCount; ?></p>
            <p class="label">Registered Users</p>
        </article>
        <article class="stat-card">
            <p class="value"><?php echo $itemCount; ?></p>
            <p class="label">Posted Items</p>
        </article>
        <article class="stat-card">
            <p class="value"><?php echo $claimCount; ?></p>
            <p class="label">Claim Requests</p>
        </article>
    </section>

    <section class="card">
        <h3>Admin Actions</h3>
        <div class="actions">
            <a class="btn btn-primary" href="view_users.php">View Users</a>
            <a class="btn btn-primary" href="view_items.php">View Items</a>
            <a class="btn btn-primary" href="view_claims.php">View Claims</a>
        </div>
    </section>
</main>
</body>
</html>
