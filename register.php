<?php
require 'config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: login.php?msg=" . urlencode("Registration successful. Please log in."));
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="auth-wrap">
    <div class="card auth-card">
        <div class="page-head">
            <h2>Create Account</h2>
            <a class="btn btn-secondary" href="index.php">Home</a>
        </div>
        <p class="muted">Register to report or claim items on campus.</p>

        <?php if ($error !== '') { ?>
            <p class="msg msg-error"><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>

        <form method="POST">
            <label for="name">Full Name</label>
            <input id="name" type="text" name="name" required>

            <label for="email">Email</label>
            <input id="email" type="email" name="email" required>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <div class="actions">
                <button type="submit" class="btn btn-primary">Register</button>
                <a class="btn btn-secondary" href="login.php">Already have an account</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
