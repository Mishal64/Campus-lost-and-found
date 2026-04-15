<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT items.id, items.item_name, items.type, items.status, items.created_at, users.name AS owner_name
        FROM items
        JOIN users ON items.user_id = users.id
        ORDER BY items.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Items</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="view_users.php">Users</a>
            <a href="view_claims.php">Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>All Items</h2>
        <a class="btn btn-secondary" href="dashboard.php">Back</a>
    </div>

    <section class="card">
        <?php if ($result && $result->num_rows > 0) { ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Posted By</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td>
                            <a href="view_item_details.php?id=<?php echo (int)$row['id']; ?>">
                                <?php echo htmlspecialchars($row['item_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td>
                            <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-primary" href="view_item_details.php?id=<?php echo (int)$row['id']; ?>">View Details</a>
                                <?php if ($row['status'] !== 'returned') { ?>
                                    <a class="btn btn-secondary" href="update_item_status.php?id=<?php echo (int)$row['id']; ?>&status=returned">Mark Returned</a>
                                <?php } else { ?>
                                    <a class="btn btn-secondary" href="update_item_status.php?id=<?php echo (int)$row['id']; ?>&status=open">Reopen</a>
                                <?php } ?>
                                <a class="btn btn-danger" href="delete_item.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this item?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p class="muted">No items found.</p>
        <?php } ?>
    </section>
</main>
</body>
</html>
