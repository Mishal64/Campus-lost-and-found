<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)($_GET['id'] ?? 0);
$role = $_GET['role'] ?? '';
$current_admin_id = (int)$_SESSION['user_id'];

if ($user_id <= 0 || !in_array($role, ['student', 'admin'], true)) {
    header('Location: view_users.php');
    exit();
}

if ($user_id === $current_admin_id && $role !== 'admin') {
    header('Location: view_users.php');
    exit();
}

$stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
$stmt->bind_param("si", $role, $user_id);
$stmt->execute();

header('Location: view_users.php');
exit();
