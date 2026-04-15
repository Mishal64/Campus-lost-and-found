<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT items.*, users.name AS posted_by
        FROM items
        JOIN users ON items.user_id = users.id
        WHERE items.type = 'found' AND items.status <> 'returned'
        ORDER BY items.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Found Items</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge</div>
        <nav class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="post_found.php">Report Found</a>
            <a href="my_claims.php">My Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Found Items</h2>
        <a class="btn btn-secondary" href="post_found.php">Post Found Item</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="msg msg-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <section class="list-grid">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <article class="card">
                <h3 class="item-title">
                    <a href="view_item_details.php?id=<?php echo (int)$row['id']; ?>" class="plain-link">
                        <?php echo htmlspecialchars($row['item_name']); ?>
                    </a>
                </h3>
                <p class="meta"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                <p class="meta"><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
                <p class="meta">
                    <strong>Status:</strong>
                    <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </p>
                <div class="actions">
                    <a class="btn btn-secondary" href="view_item_details.php?id=<?php echo (int)$row['id']; ?>">View Details</a>
                </div>
            </article>
        <?php } ?>
    </section>
</main>
</body>
</html>
