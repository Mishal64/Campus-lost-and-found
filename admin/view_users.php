<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Users</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="view_items.php">Items</a>
            <a href="view_claims.php">Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>All Users</h2>
        <a class="btn btn-secondary" href="dashboard.php">Back</a>
    </div>

    <section class="card">
        <?php if ($result && $result->num_rows > 0) { ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo (int)$row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <?php if ((int)$row['id'] === (int)$_SESSION['user_id']) { ?>
                                <span class="muted">Current account</span>
                            <?php } else { ?>
                                <div class="actions">
                                    <a class="btn btn-secondary" href="edit_user.php?id=<?php echo (int)$row['id']; ?>">Edit</a>
                                    <?php if ($row['role'] === 'student') { ?>
                                        <a class="btn btn-primary" href="update_user_role.php?id=<?php echo (int)$row['id']; ?>&role=admin">Make Admin</a>
                                    <?php } else { ?>
                                        <a class="btn btn-secondary" href="update_user_role.php?id=<?php echo (int)$row['id']; ?>&role=student">Make Student</a>
                                    <?php } ?>
                                    <a class="btn btn-danger" href="delete_user.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p class="muted">No users found.</p>
        <?php } ?>
    </section>
</main>
</body>
</html>
