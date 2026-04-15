<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$item_id = (int)($_GET['id'] ?? 0);
if ($item_id <= 0) {
    header('Location: view_items.php');
    exit();
}

$sql = "SELECT items.*, users.name AS owner_name, users.email AS owner_email
        FROM items
        JOIN users ON items.user_id = users.id
        WHERE items.id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: view_items.php');
    exit();
}

$item = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="view_users.php">Users</a>
            <a href="view_items.php">Items</a>
            <a href="view_claims.php">Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Item Details</h2>
        <a class="btn btn-secondary" href="view_items.php">Back to Items</a>
    </div>

    <section class="card">
        <h3 class="item-title"><?php echo htmlspecialchars($item['item_name']); ?></h3>
        <p class="meta"><strong>Type:</strong> <?php echo htmlspecialchars($item['type']); ?></p>
        <p class="meta"><strong>Status:</strong> <span class="pill pill-<?php echo htmlspecialchars($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span></p>
        <p class="meta"><strong>Category:</strong> <?php echo htmlspecialchars($item['category'] ?? ''); ?></p>
        <p class="meta"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($item['description'] ?? '')); ?></p>
        <p class="meta"><strong>Location:</strong> <?php echo htmlspecialchars($item['location'] ?? ''); ?></p>
        <p class="meta"><strong>Date:</strong> <?php echo htmlspecialchars($item['date'] ?? ''); ?></p>
        <p class="meta"><strong>Posted By:</strong> <?php echo htmlspecialchars($item['owner_name']); ?> (<?php echo htmlspecialchars($item['owner_email']); ?>)</p>
        <p class="meta"><strong>Created At:</strong> <?php echo htmlspecialchars($item['created_at']); ?></p>

        <?php if (!empty($item['image'])) { ?>
            <img class="item-image" src="../<?php echo htmlspecialchars($item['image']); ?>" alt="Item image">
        <?php } ?>

        <hr>
        <h3>Verification Setup</h3>
        <p class="meta"><strong>Question 1:</strong> <?php echo htmlspecialchars($item['verification_question_1'] ?: 'Not set'); ?></p>
        <p class="meta"><strong>Question 2:</strong> <?php echo htmlspecialchars($item['verification_question_2'] ?: 'Not set'); ?></p>
        <p class="meta"><strong>Proof Instructions:</strong> <?php echo htmlspecialchars($item['proof_instructions'] ?: 'Not set'); ?></p>
    </section>
</main>
</body>
</html>
