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

$stmt = $conn->prepare("SELECT image FROM items WHERE id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $item = $res->fetch_assoc();

    $deleteClaims = $conn->prepare("DELETE FROM claims WHERE item_id = ?");
    $deleteClaims->bind_param("i", $item_id);
    $deleteClaims->execute();

    $deleteItem = $conn->prepare("DELETE FROM items WHERE id = ?");
    $deleteItem->bind_param("i", $item_id);
    $deleteItem->execute();

    if (!empty($item['image'])) {
        $fullPath = '../' . $item['image'];
        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }
}

header('Location: view_items.php');
exit();
