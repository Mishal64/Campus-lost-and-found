<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$claim_id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if ($claim_id <= 0 || !in_array($action, ['approved', 'rejected', 'returned'], true)) {
    header('Location: view_claims.php');
    exit();
}

$check = $conn->prepare("SELECT id, item_id, status FROM claims WHERE id = ?");
$check->bind_param("i", $claim_id);
$check->execute();
$res = $check->get_result();

if (!$res || $res->num_rows !== 1) {
    header('Location: view_claims.php');
    exit();
}

$claim = $res->fetch_assoc();
$item_id = (int)$claim['item_id'];
$current_status = $claim['status'];

if ($action === 'returned') {
    if ($current_status !== 'approved') {
        header('Location: view_claims.php');
        exit();
    }
    $updateItem = $conn->prepare("UPDATE items SET status = 'returned' WHERE id = ?");
    $updateItem->bind_param("i", $item_id);
    $updateItem->execute();

    header('Location: view_claims.php');
    exit();
}

$updateClaim = $conn->prepare("UPDATE claims SET status = ? WHERE id = ?");
$updateClaim->bind_param("si", $action, $claim_id);
$updateClaim->execute();

if ($action === 'approved') {
    $updateItem = $conn->prepare("UPDATE items SET status = 'claimed' WHERE id = ?");
    $updateItem->bind_param("i", $item_id);
    $updateItem->execute();

    $rejectOthers = $conn->prepare("UPDATE claims SET status = 'rejected' WHERE item_id = ? AND id <> ? AND status = 'pending'");
    $rejectOthers->bind_param("ii", $item_id, $claim_id);
    $rejectOthers->execute();
} elseif ($action === 'rejected' && $current_status === 'approved') {
    $hasApproved = $conn->prepare("SELECT id FROM claims WHERE item_id = ? AND status = 'approved' LIMIT 1");
    $hasApproved->bind_param("i", $item_id);
    $hasApproved->execute();
    $approvedRes = $hasApproved->get_result();

    if ($approvedRes->num_rows === 0) {
        $updateItem = $conn->prepare("UPDATE items SET status = 'open' WHERE id = ?");
        $updateItem->bind_param("i", $item_id);
        $updateItem->execute();
    }
}

header('Location: view_claims.php');
exit();
