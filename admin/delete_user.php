<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)($_GET['id'] ?? 0);
$current_admin_id = (int)$_SESSION['user_id'];

if ($user_id <= 0 || $user_id === $current_admin_id) {
    header('Location: view_users.php');
    exit();
}

$deleteClaimsByUser = $conn->prepare("DELETE FROM claims WHERE claimant_id = ?");
$deleteClaimsByUser->bind_param("i", $user_id);
$deleteClaimsByUser->execute();

$ownedItems = $conn->prepare("SELECT id FROM items WHERE user_id = ?");
$ownedItems->bind_param("i", $user_id);
$ownedItems->execute();
$itemsRes = $ownedItems->get_result();
while ($item = $itemsRes->fetch_assoc()) {
    $itemId = (int)$item['id'];
    $deleteClaimsByItem = $conn->prepare("DELETE FROM claims WHERE item_id = ?");
    $deleteClaimsByItem->bind_param("i", $itemId);
    $deleteClaimsByItem->execute();
}

$deleteItems = $conn->prepare("DELETE FROM items WHERE user_id = ?");
$deleteItems->bind_param("i", $user_id);
$deleteItems->execute();

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

header('Location: view_users.php');
exit();
