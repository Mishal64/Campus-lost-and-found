<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: view_found.php");
    exit();
}

$item_id = (int)($_POST['item_id'] ?? 0);
$claimant_id = (int)$_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');
$verification_answer_1 = trim($_POST['verification_answer_1'] ?? '');
$verification_answer_2 = trim($_POST['verification_answer_2'] ?? '');
$return_to = $_POST['return_to'] ?? 'view_found.php';

$allowedPattern = '/^(view_found\.php|view_lost\.php|view_item_details\.php\?id=\d+)$/';
if (!preg_match($allowedPattern, $return_to)) {
    $return_to = 'view_found.php';
}

if ($item_id <= 0) {
    header("Location: " . $return_to . "?msg=" . urlencode("Invalid item."));
    exit();
}

if ($verification_answer_1 === '' || $verification_answer_2 === '') {
    header("Location: " . $return_to . "?msg=" . urlencode("Please answer the verification questions."));
    exit();
}

$checkSql = "SELECT id, user_id, status, type FROM items WHERE id = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("i", $item_id);
$checkStmt->execute();
$itemResult = $checkStmt->get_result();

if ($itemResult->num_rows !== 1) {
    header("Location: " . $return_to . "?msg=" . urlencode("Item not found."));
    exit();
}

$item = $itemResult->fetch_assoc();

if ($item['status'] !== 'open') {
    header("Location: " . $return_to . "?msg=" . urlencode("This item cannot be claimed."));
    exit();
}

if ((int)$item['user_id'] === $claimant_id) {
    header("Location: " . $return_to . "?msg=" . urlencode("You cannot claim your own item."));
    exit();
}

$dupSql = "SELECT id FROM claims WHERE item_id = ? AND claimant_id = ? AND status = 'pending'";
$dupStmt = $conn->prepare($dupSql);
$dupStmt->bind_param("ii", $item_id, $claimant_id);
$dupStmt->execute();
$dupResult = $dupStmt->get_result();

if ($dupResult->num_rows > 0) {
    header("Location: " . $return_to . "?msg=" . urlencode("You already have a pending claim for this item."));
    exit();
}

$proof_file = null;

if (!isset($_FILES['proof_file']) || $_FILES['proof_file']['error'] !== 0) {
    header("Location: " . $return_to . "?msg=" . urlencode("Please upload a proof file."));
    exit();
}

$target_dir = "../uploads/claim_proofs/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$proof_name = time() . "_" . basename($_FILES['proof_file']['name']);
$target_file = $target_dir . $proof_name;
$proof_extension = strtolower(pathinfo($proof_name, PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($proof_extension, $allowed_extensions, true)) {
    header("Location: " . $return_to . "?msg=" . urlencode("Only JPG, PNG, or PDF proof files are allowed."));
    exit();
}

if (!move_uploaded_file($_FILES['proof_file']['tmp_name'], $target_file)) {
    header("Location: " . $return_to . "?msg=" . urlencode("Could not upload the proof file."));
    exit();
}

$proof_file = "uploads/claim_proofs/" . $proof_name;

$insertSql = "INSERT INTO claims (item_id, claimant_id, message, verification_answer_1, verification_answer_2, proof_file, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
$insertStmt = $conn->prepare($insertSql);
if (!$insertStmt) {
    header("Location: " . $return_to . "?msg=" . urlencode("Verification database update is missing. Run config/verification-migration.sql."));
    exit();
}
$insertStmt->bind_param("iissss", $item_id, $claimant_id, $message, $verification_answer_1, $verification_answer_2, $proof_file);

if ($insertStmt->execute()) {
    header("Location: " . $return_to . "?msg=" . urlencode("Claim request sent successfully."));
    exit();
}

header("Location: " . $return_to . "?msg=" . urlencode("Error: " . $conn->error));
exit();
