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

$sql = "SELECT
            claims.id AS claim_id,
            claims.status,
            claims.message,
            claims.verification_answer_1,
            claims.verification_answer_2,
            claims.proof_file,
            claims.created_at,
            items.id AS item_id,
            items.item_name,
            items.description,
            items.category,
            items.location,
            items.date,
            items.image,
            items.status AS item_status,
            items.type AS item_type,
            items.verification_question_1,
            items.verification_question_2,
            items.proof_instructions,
            claimant.name AS claimant_name,
            claimant.email AS claimant_email,
            owner.name AS owner_name,
            owner.email AS owner_email
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users AS claimant ON claims.claimant_id = claimant.id
        JOIN users AS owner ON items.user_id = owner.id
        WHERE claims.id = ?
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $claim_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header('Location: view_claims.php');
    exit();
}

$claim = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Details</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge Admin</div>
        <nav class="nav-links">
            <a href="dashboard.php">Admin Dashboard</a>
            <a href="view_users.php">Users</a>
            <a href="view_items.php">Items</a>
            <a href="view_claims.php">Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Claim Details</h2>
        <a class="btn btn-secondary" href="view_claims.php">Back to Claims</a>
    </div>

    <section class="card">
        <h3 class="item-title"><?php echo htmlspecialchars($claim['item_name']); ?></h3>
        <p class="meta"><strong>Claim ID:</strong> <?php echo (int)$claim['claim_id']; ?></p>
        <p class="meta"><strong>Item Type:</strong> <?php echo htmlspecialchars($claim['item_type']); ?></p>
        <p class="meta"><strong>Claim Status:</strong> <span class="pill pill-<?php echo htmlspecialchars($claim['status']); ?>"><?php echo htmlspecialchars($claim['status']); ?></span></p>
        <p class="meta"><strong>Item Status:</strong> <span class="pill pill-<?php echo htmlspecialchars($claim['item_status']); ?>"><?php echo htmlspecialchars($claim['item_status']); ?></span></p>
        <p class="meta"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($claim['description'] ?? '')); ?></p>
        <p class="meta"><strong>Category:</strong> <?php echo htmlspecialchars($claim['category'] ?? ''); ?></p>
        <p class="meta"><strong>Location:</strong> <?php echo htmlspecialchars($claim['location'] ?? ''); ?></p>
        <p class="meta"><strong>Date:</strong> <?php echo htmlspecialchars($claim['date'] ?? ''); ?></p>
        <p class="meta"><strong>Owner:</strong> <?php echo htmlspecialchars($claim['owner_name']); ?> (<?php echo htmlspecialchars($claim['owner_email']); ?>)</p>
        <p class="meta"><strong>Claimant:</strong> <?php echo htmlspecialchars($claim['claimant_name']); ?> (<?php echo htmlspecialchars($claim['claimant_email']); ?>)</p>
        <p class="meta"><strong>Claim Message:</strong> <?php echo nl2br(htmlspecialchars($claim['message'] ?? '')); ?></p>
        <p class="meta"><strong>Requested At:</strong> <?php echo htmlspecialchars($claim['created_at']); ?></p>

        <?php if (!empty($claim['image'])) { ?>
            <img class="item-image" src="../<?php echo htmlspecialchars($claim['image']); ?>" alt="Claim item image">
        <?php } ?>

        <hr>
        <h3>Verification Answers</h3>
        <p class="meta"><strong><?php echo htmlspecialchars($claim['verification_question_1'] ?: 'Answer 1'); ?>:</strong> <?php echo nl2br(htmlspecialchars($claim['verification_answer_1'] ?? '')); ?></p>
        <p class="meta"><strong><?php echo htmlspecialchars($claim['verification_question_2'] ?: 'Answer 2'); ?>:</strong> <?php echo nl2br(htmlspecialchars($claim['verification_answer_2'] ?? '')); ?></p>
        <p class="meta"><strong>Proof Requested:</strong> <?php echo htmlspecialchars($claim['proof_instructions'] ?: 'Not set'); ?></p>
        <p class="meta">
            <strong>Uploaded Proof:</strong>
            <?php if (!empty($claim['proof_file'])) { ?>
                <a href="../<?php echo htmlspecialchars($claim['proof_file']); ?>" target="_blank" rel="noopener">Open proof file</a>
            <?php } else { ?>
                No proof uploaded.
            <?php } ?>
        </p>

        <div class="actions">
            <?php if ($claim['status'] === 'pending') { ?>
                <a class="btn btn-primary" href="update_claim.php?id=<?php echo (int)$claim['claim_id']; ?>&action=approved">Approve</a>
                <a class="btn btn-danger" href="update_claim.php?id=<?php echo (int)$claim['claim_id']; ?>&action=rejected">Reject</a>
            <?php } elseif ($claim['status'] === 'approved' && $claim['item_status'] !== 'returned') { ?>
                <a class="btn btn-secondary" href="update_claim.php?id=<?php echo (int)$claim['claim_id']; ?>&action=returned">Mark Returned</a>
                <a class="btn btn-danger" href="update_claim.php?id=<?php echo (int)$claim['claim_id']; ?>&action=rejected">Reject</a>
            <?php } ?>
        </div>
    </section>
</main>
</body>
</html>
