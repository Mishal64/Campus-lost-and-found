<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$edit_id = (int)($_GET['id'] ?? 0);
if ($edit_id <= 0) {
    header('Location: view_users.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';

    if ($name === '' || $email === '' || !in_array($role, ['student', 'admin'], true)) {
        $error = 'Invalid input.';
    } else {
        if ($edit_id === (int)$_SESSION['user_id'] && $role !== 'admin') {
            $error = 'You cannot remove your own admin access.';
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $role, $edit_id);
            if ($stmt->execute()) {
                $message = 'User updated successfully.';
            } else {
                $error = 'Update failed: ' . $conn->error;
            }
        }
    }
}

$fetch = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
$fetch->bind_param("i", $edit_id);
$fetch->execute();
$res = $fetch->get_result();

if (!$res || $res->num_rows !== 1) {
    header('Location: view_users.php');
    exit();
}

$user = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="view_users.php">Users</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Edit User</h2>
        <a class="btn btn-secondary" href="view_users.php">Back</a>
    </div>

    <?php if ($message !== '') { ?>
        <p class="msg msg-success"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>
    <?php if ($error !== '') { ?>
        <p class="msg msg-error"><?php echo htmlspecialchars($error); ?></p>
    <?php } ?>

    <section class="card">
        <form method="POST">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($user['name']); ?>">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($user['email']); ?>">

            <label for="role">Role</label>
            <select id="role" name="role" required>
                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>

            <div class="actions">
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
