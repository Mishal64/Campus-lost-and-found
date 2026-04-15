<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$item_id = (int)($_GET['id'] ?? 0);
$status = $_GET['status'] ?? '';

if ($item_id <= 0 || !in_array($status, ['open', 'claimed', 'returned'], true)) {
    header('Location: view_items.php');
    exit();
}

$stmt = $conn->prepare("UPDATE items SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $item_id);
$stmt->execute();

header('Location: view_items.php');
exit();
