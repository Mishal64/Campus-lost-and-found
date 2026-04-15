<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
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
            items.status AS item_status,
            items.type AS item_type,
            items.verification_question_1,
            items.verification_question_2,
            items.proof_instructions,
            claimant.name AS claimant_name,
            claimant.email AS claimant_email,
            owner.name AS owner_name
        FROM claims
        JOIN items ON claims.item_id = items.id
        JOIN users AS claimant ON claims.claimant_id = claimant.id
        JOIN users AS owner ON items.user_id = owner.id
        ORDER BY claims.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Claims</title>
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
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>All Claims</h2>
        <a class="btn btn-secondary" href="dashboard.php">Back</a>
    </div>

    <section class="card">
        <?php if ($result && $result->num_rows > 0) { ?>
            <table>
                <tr>
                    <th>Claim ID</th>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Owner</th>
                    <th>Claimant</th>
                    <th>Message</th>
                    <th>Verification</th>
                    <th>Claim Status</th>
                    <th>Item Status</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>
                            <a href="view_claim_details.php?id=<?php echo (int)$row['claim_id']; ?>">
                                <?php echo (int)$row['claim_id']; ?>
                            </a>
                        </td>
                        <td>
                            <a href="view_claim_details.php?id=<?php echo (int)$row['claim_id']; ?>">
                                <?php echo htmlspecialchars($row['item_name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['item_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['owner_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['claimant_name']); ?> (<?php echo htmlspecialchars($row['claimant_email']); ?>)</td>
                        <td><?php echo nl2br(htmlspecialchars($row['message'] ?? '')); ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['verification_question_1'] ?: 'Answer 1'); ?>:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['verification_answer_1'] ?? '')); ?><br><br>
                            <strong><?php echo htmlspecialchars($row['verification_question_2'] ?: 'Answer 2'); ?>:</strong><br>
                            <?php echo nl2br(htmlspecialchars($row['verification_answer_2'] ?? '')); ?><br><br>
                            <?php if (!empty($row['proof_instructions'])) { ?>
                                <strong>Proof Requested:</strong><br>
                                <?php echo htmlspecialchars($row['proof_instructions']); ?><br><br>
                            <?php } ?>
                            <?php if (!empty($row['proof_file'])) { ?>
                                <a href="../<?php echo htmlspecialchars($row['proof_file']); ?>" target="_blank" rel="noopener">Open proof</a>
                            <?php } else { ?>
                                <span class="muted">No proof</span>
                            <?php } ?>
                        </td>
                        <td>
                            <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>">
                                <?php echo htmlspecialchars($row['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="pill pill-<?php echo htmlspecialchars($row['item_status']); ?>">
                                <?php echo htmlspecialchars($row['item_status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a class="btn btn-primary" href="view_claim_details.php?id=<?php echo (int)$row['claim_id']; ?>">View Details</a>
                                <?php if ($row['status'] === 'pending') { ?>
                                    <a class="btn btn-primary" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=approved">Approve</a>
                                    <a class="btn btn-danger" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=rejected">Reject</a>
                                <?php } elseif ($row['status'] === 'approved' && $row['item_status'] !== 'returned') { ?>
                                    <a class="btn btn-secondary" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=returned">Mark Returned</a>
                                    <a class="btn btn-danger" href="update_claim.php?id=<?php echo (int)$row['claim_id']; ?>&action=rejected">Reject</a>
                                <?php } else { ?>
                                    <span class="muted">No action</span>
                                <?php } ?>
                                <a class="btn btn-danger" href="delete_claim.php?id=<?php echo (int)$row['claim_id']; ?>" onclick="return confirm('Delete this claim?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else { ?>
            <p class="muted">No claims found.</p>
        <?php } ?>
    </section>
</main>
</body>
</html>
