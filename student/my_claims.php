<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

$receivedSql = "SELECT
            claims.id AS claim_id,
            claims.status,
            claims.message,
            claims.verification_answer_1,
            claims.verification_answer_2,
            claims.proof_file,
            claims.created_at,
            users.name AS claimant_name,
            users.email AS claimant_email,
            items.id AS item_id,
            items.item_name,
            items.status AS item_status,
            items.verification_question_1,
            items.verification_question_2,
            items.proof_instructions
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users ON claims.claimant_id = users.id
        WHERE items.user_id = ?
        ORDER BY claims.created_at DESC";

$receivedStmt = $conn->prepare($receivedSql);
$receivedStmt->bind_param("i", $user_id);
$receivedStmt->execute();
$receivedResult = $receivedStmt->get_result();

$submittedSql = "SELECT
            claims.id AS claim_id,
            claims.status,
            claims.message,
            claims.verification_answer_1,
            claims.verification_answer_2,
            claims.proof_file,
            claims.created_at,
            items.item_name,
            items.status AS item_status,
            owners.name AS owner_name,
            owners.email AS owner_email,
            items.verification_question_1,
            items.verification_question_2,
            items.proof_instructions
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users AS owners ON items.user_id = owners.id
        WHERE claims.claimant_id = ?
        ORDER BY claims.created_at DESC";

$submittedStmt = $conn->prepare($submittedSql);
$submittedStmt->bind_param("i", $user_id);
$submittedStmt->execute();
$submittedResult = $submittedStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Claims</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge</div>
        <nav class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="view_lost.php">Lost Items</a>
            <a href="view_found.php">Found Items</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <section>
        <div class="page-head">
            <h2>Claim Requests For My Items</h2>
        </div>
        <?php if ($receivedResult->num_rows === 0): ?>
            <p class="muted">No claims yet.</p>
        <?php endif; ?>

        <div class="list-grid">
            <?php while ($row = $receivedResult->fetch_assoc()) { ?>
                <article class="card">
                    <h3 class="item-title"><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p class="meta"><strong>Claimed By:</strong> <?php echo htmlspecialchars($row['claimant_name']); ?> (<?php echo htmlspecialchars($row['claimant_email']); ?>)</p>
                    <p class="meta"><strong>Message:</strong> <?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></p>
                    <p class="meta"><strong><?php echo htmlspecialchars($row['verification_question_1'] ?: 'Verification Answer 1'); ?>:</strong> <?php echo nl2br(htmlspecialchars($row['verification_answer_1'] ?? '')); ?></p>
                    <p class="meta"><strong><?php echo htmlspecialchars($row['verification_question_2'] ?: 'Verification Answer 2'); ?>:</strong> <?php echo nl2br(htmlspecialchars($row['verification_answer_2'] ?? '')); ?></p>
                    <?php if (!empty($row['proof_instructions'])) { ?>
                        <p class="meta"><strong>Proof Requested:</strong> <?php echo htmlspecialchars($row['proof_instructions']); ?></p>
                    <?php } ?>
                    <?php if (!empty($row['proof_file'])) { ?>
                        <p class="meta"><strong>Proof File:</strong> <a href="../<?php echo htmlspecialchars($row['proof_file']); ?>" target="_blank" rel="noopener">View uploaded proof</a></p>
                    <?php } ?>
                    <p class="meta">
                        <strong>Status:</strong>
                        <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                    </p>
                    <p class="meta">
                        <strong>Item State:</strong>
                        <span class="pill pill-<?php echo htmlspecialchars($row['item_status']); ?>"><?php echo htmlspecialchars($row['item_status']); ?></span>
                    </p>
                    <p class="meta"><strong>Requested At:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>

                    <?php if ($row['status'] === 'pending') { ?>
                        <div class="actions">
                            <a class="btn btn-primary" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=approved">Approve</a>
                            <a class="btn btn-danger" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=rejected">Reject</a>
                        </div>
                    <?php } elseif ($row['status'] === 'approved' && $row['item_status'] !== 'returned') { ?>
                        <div class="actions">
                            <a class="btn btn-secondary" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=returned">Mark Returned</a>
                        </div>
                    <?php } ?>
                </article>
            <?php } ?>
        </div>
    </section>

    <section>
        <div class="page-head">
            <h2>Claims I Submitted</h2>
        </div>
        <?php if ($submittedResult->num_rows === 0): ?>
            <p class="muted">You have not submitted any claims.</p>
        <?php endif; ?>

        <div class="list-grid">
            <?php while ($row = $submittedResult->fetch_assoc()) { ?>
                <article class="card">
                    <h3 class="item-title"><?php echo htmlspecialchars($row['item_name']); ?></h3>
                    <p class="meta"><strong>Posted By:</strong> <?php echo htmlspecialchars($row['owner_name']); ?></p>
                    <p class="meta"><strong>Your Message:</strong> <?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></p>
                    <p class="meta"><strong><?php echo htmlspecialchars($row['verification_question_1'] ?: 'Verification Answer 1'); ?>:</strong> <?php echo nl2br(htmlspecialchars($row['verification_answer_1'] ?? '')); ?></p>
                    <p class="meta"><strong><?php echo htmlspecialchars($row['verification_question_2'] ?: 'Verification Answer 2'); ?>:</strong> <?php echo nl2br(htmlspecialchars($row['verification_answer_2'] ?? '')); ?></p>
                    <?php if (!empty($row['proof_file'])) { ?>
                        <p class="meta"><strong>Your Proof File:</strong> <a href="../<?php echo htmlspecialchars($row['proof_file']); ?>" target="_blank" rel="noopener">Open uploaded proof</a></p>
                    <?php } ?>
                    <p class="meta">
                        <strong>Status:</strong>
                        <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                    </p>
                    <p class="meta">
                        <strong>Item State:</strong>
                        <span class="pill pill-<?php echo htmlspecialchars($row['item_status']); ?>"><?php echo htmlspecialchars($row['item_status']); ?></span>
                    </p>
                    <p class="meta"><strong>Requested At:</strong> <?php echo htmlspecialchars($row['created_at']); ?></p>
                    <?php if ($row['status'] === 'approved') { ?>
                        <p class="meta"><strong>Contact Email:</strong> <?php echo htmlspecialchars($row['owner_email']); ?></p>
                    <?php } else { ?>
                        <p class="meta"><strong>Contact Email:</strong> Available after approval.</p>
                    <?php } ?>
                </article>
            <?php } ?>
        </div>
    </section>
</main>
</body>
</html>
