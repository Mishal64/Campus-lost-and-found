<?php
session_start();
require 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <div class="page-head">
            <h2>Sign In</h2>
            <a class="btn btn-secondary" href="index.php">Home</a>
        </div>
        <p class="muted">Access your FoundBridge account.</p>

        <?php if (isset($_GET['msg'])) { ?>
            <p class="msg msg-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php } ?>

        <?php if ($error !== '') { ?>
            <p class="msg msg-error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Sign In</button>
                <a class="btn btn-secondary" href="register.php">Create account</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
