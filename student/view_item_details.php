<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$item_id = (int)($_GET['id'] ?? 0);

function claimProofLabel(string $category): string
{
    $normalized = strtolower(trim($category));

    if (preg_match('/phone|laptop|tablet|watch|earbud|camera|charger|electronics|electronic/', $normalized)) {
        return 'Ownership Proof (invoice, warranty card, serial proof, or an older photo)';
    }

    if (preg_match('/jewel|jewellery|jewelry|ring|chain|bracelet|necklace|earring|anklet/', $normalized)) {
        return 'Ownership Proof (older photo of the jewelry, box, or receipt)';
    }

    return 'Supporting Proof (older photo, receipt, or any proof you have)';
}

if ($item_id <= 0) {
    header('Location: ../dashboard.php');
    exit();
}

$itemSql = "SELECT items.*, 
                   users.name AS owner_name,
                   users.email AS owner_email,
                   COUNT(claims.id) AS total_claims,
                   SUM(CASE WHEN claims.status = 'pending' THEN 1 ELSE 0 END) AS pending_claims,
                   SUM(CASE WHEN claims.status = 'approved' THEN 1 ELSE 0 END) AS approved_claims
            FROM items
            JOIN users ON items.user_id = users.id
            LEFT JOIN claims ON claims.item_id = items.id
            WHERE items.id = ?
            GROUP BY items.id
            LIMIT 1";

$itemStmt = $conn->prepare($itemSql);
$itemStmt->bind_param('i', $item_id);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

if ($itemResult->num_rows !== 1) {
    header('Location: view_found.php');
    exit();
}

$item = $itemResult->fetch_assoc();
$browsePage = $item['type'] === 'found' ? 'view_found.php' : 'view_lost.php';
$isOwner = (int)$item['user_id'] === $user_id;
$isClaimableFoundItem = $item['type'] === 'found' && !$isOwner && $item['status'] === 'open';
$pageTitle = $isOwner ? 'My Post Details' : 'Item Details';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge</div>
        <nav class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="<?php echo htmlspecialchars($browsePage); ?>">
                <?php echo $item['type'] === 'found' ? 'Found Items' : 'Lost Items'; ?>
            </a>
            <a href="my_claims.php">My Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <div>
            <h2><?php echo htmlspecialchars($pageTitle); ?></h2>
            <p class="muted section-copy">
                <?php echo $isOwner
                    ? 'A focused view of your item post, status, and related claim activity.'
                    : 'Review the full item details and submit your claim with proof from this page.'; ?>
            </p>
        </div>
        <a class="btn btn-secondary" href="<?php echo htmlspecialchars($browsePage); ?>">Back to List</a>
    </div>

    <section class="card item-detail-shell">
        <div class="item-detail-top">
            <div class="item-detail-copy">
                <div class="item-detail-kicker">
                    <span class="pill pill-<?php echo htmlspecialchars($item['type']); ?>"><?php echo htmlspecialchars($item['type']); ?></span>
                    <span class="pill pill-<?php echo htmlspecialchars($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                </div>
                <h3 class="item-title item-detail-title"><?php echo htmlspecialchars($item['item_name']); ?></h3>
                <p class="meta item-detail-description"><?php echo nl2br(htmlspecialchars($item['description'] ?? 'No description provided.')); ?></p>
            </div>

            <?php if (!empty($item['image'])) { ?>
                <div class="item-detail-visual">
                    <img class="item-image item-detail-image" src="../<?php echo htmlspecialchars($item['image']); ?>" alt="Item image">
                </div>
            <?php } ?>
        </div>

        <div class="detail-stats">
            <article class="detail-stat">
                <span class="detail-stat-value"><?php echo (int)$item['total_claims']; ?></span>
                <span class="detail-stat-label">Total Claims</span>
            </article>
            <article class="detail-stat">
                <span class="detail-stat-value"><?php echo (int)$item['pending_claims']; ?></span>
                <span class="detail-stat-label">Pending Review</span>
            </article>
            <article class="detail-stat">
                <span class="detail-stat-value"><?php echo (int)$item['approved_claims']; ?></span>
                <span class="detail-stat-label">Approved</span>
            </article>
        </div>

        <div class="detail-grid">
            <div class="detail-panel">
                <h3>Post Information</h3>
                <p class="meta"><strong>Category:</strong> <?php echo htmlspecialchars($item['category'] ?: 'Not specified'); ?></p>
                <p class="meta"><strong>Location:</strong> <?php echo htmlspecialchars($item['location'] ?: 'Not specified'); ?></p>
                <p class="meta"><strong>Date:</strong> <?php echo htmlspecialchars($item['date'] ?: 'Not specified'); ?></p>
                <p class="meta"><strong>Created At:</strong> <?php echo htmlspecialchars($item['created_at']); ?></p>
                <p class="meta"><strong>Owner:</strong> <?php echo htmlspecialchars($item['owner_name']); ?> (<?php echo htmlspecialchars($item['owner_email']); ?>)</p>
            </div>

            <div class="detail-panel">
                <h3>Verification Setup</h3>
                <p class="meta"><strong>Question 1:</strong> <?php echo htmlspecialchars($item['verification_question_1'] ?: 'Not set'); ?></p>
                <p class="meta"><strong>Question 2:</strong> <?php echo htmlspecialchars($item['verification_question_2'] ?: 'Not set'); ?></p>
                <p class="meta"><strong>Proof Instructions:</strong> <?php echo htmlspecialchars($item['proof_instructions'] ?: 'Not set'); ?></p>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] !== ''): ?>
            <p class="msg msg-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
        <?php endif; ?>

        <?php if ($isClaimableFoundItem) { ?>
            <div class="detail-panel">
                <h3>Claim This Item</h3>
                <form method="POST" action="claim_item.php" enctype="multipart/form-data">
                    <?php $proofLabel = claimProofLabel((string)($item['category'] ?? '')); ?>
                    <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                    <input type="hidden" name="return_to" value="view_item_details.php?id=<?php echo (int)$item['id']; ?>">

                    <label for="message">Claim Message (optional)</label>
                    <textarea id="message" name="message" placeholder="Explain why this item is yours"></textarea>

                    <label for="answer1">
                        <?php echo htmlspecialchars($item['verification_question_1'] ?: 'What detail can you confirm about this item?'); ?>
                    </label>
                    <textarea id="answer1" name="verification_answer_1" required></textarea>

                    <label for="answer2">
                        <?php echo htmlspecialchars($item['verification_question_2'] ?: 'What hidden mark, content, or identifier can you confirm?'); ?>
                    </label>
                    <textarea id="answer2" name="verification_answer_2" required></textarea>

                    <label for="proof_file"><?php echo htmlspecialchars($proofLabel); ?></label>
                    <input id="proof_file" type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required>

                    <?php if (!empty($item['proof_instructions'])) { ?>
                        <p class="meta"><strong>Proof Required:</strong> <?php echo htmlspecialchars($item['proof_instructions']); ?></p>
                    <?php } ?>

                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Claim This Item</button>
                    </div>
                </form>
            </div>
        <?php } ?>

        <div class="actions item-detail-actions">
            <?php if ($isOwner) { ?>
                <a class="btn btn-primary" href="my_claims.php">Review Claim Requests</a>
            <?php } ?>
            <a class="btn btn-secondary" href="<?php echo htmlspecialchars($browsePage); ?>">
                Browse <?php echo $item['type'] === 'found' ? 'Found' : 'Lost'; ?> Items
            </a>
        </div>
    </section>
</main>
</body>
</html>
