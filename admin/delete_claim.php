<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$claim_id = (int)($_GET['id'] ?? 0);
if ($claim_id <= 0) {
    header('Location: view_claims.php');
    exit();
}

$stmt = $conn->prepare("SELECT item_id, status FROM claims WHERE id = ?");
$stmt->bind_param("i", $claim_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows === 1) {
    $claim = $res->fetch_assoc();
    $item_id = (int)$claim['item_id'];
    $claim_status = $claim['status'];

    $delete = $conn->prepare("DELETE FROM claims WHERE id = ?");
    $delete->bind_param("i", $claim_id);
    $delete->execute();

    if ($claim_status === 'approved') {
        $approvedCheck = $conn->prepare("SELECT id FROM claims WHERE item_id = ? AND status = 'approved' LIMIT 1");
        $approvedCheck->bind_param("i", $item_id);
        $approvedCheck->execute();
        $approvedRes = $approvedCheck->get_result();

        if ($approvedRes->num_rows === 0) {
            $updateItem = $conn->prepare("UPDATE items SET status = 'open' WHERE id = ?");
            $updateItem->bind_param("i", $item_id);
            $updateItem->execute();
        }
    }
}

header('Location: view_claims.php');
exit();
