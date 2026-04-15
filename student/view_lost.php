<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$current_user_id = (int) $_SESSION['user_id'];

function claimProofLabel(string $category): string
{
    $normalized = strtolower(trim($category));

    if (preg_match('/phone|laptop|tablet|watch|earbud|camera|charger|electronics|electronic/', $normalized)) {
        return 'Supporting Proof (photo, serial proof, or any evidence tied to the item)';
    }

    if (preg_match('/jewel|jewellery|jewelry|ring|chain|bracelet|necklace|earring|anklet/', $normalized)) {
        return 'Supporting Proof (older photo, packaging, or any evidence tied to the item)';
    }

    return 'Supporting Proof (photo, receipt, or any relevant evidence)';
}

$sql = "SELECT items.*, users.name AS posted_by
        FROM items
        JOIN users ON items.user_id = users.id
        WHERE items.type = 'lost' AND items.status <> 'returned'
        ORDER BY items.created_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
<header class="top-nav">
    <div class="top-nav-inner">
        <div class="brand">FoundBridge</div>
        <nav class="nav-links">
            <a href="../dashboard.php">Dashboard</a>
            <a href="post_lost.php">Report Lost</a>
            <a href="my_claims.php">My Claims</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="app-shell">
    <div class="page-head">
        <h2>Lost Items</h2>
        <a class="btn btn-secondary" href="post_lost.php">Post Lost Item</a>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <p class="msg msg-success"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <section class="list-grid">
        <?php while ($row = $result->fetch_assoc()) { ?>
            <article class="card">
                <h3 class="item-title"><?php echo htmlspecialchars($row['item_name']); ?></h3>
                <p class="meta"><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                <p class="meta"><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                <p class="meta"><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                <p class="meta"><strong>Date:</strong> <?php echo htmlspecialchars($row['date']); ?></p>
                <p class="meta">
                    <strong>Status:</strong>
                    <span class="pill pill-<?php echo htmlspecialchars($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </span>
                </p>
                <p class="meta"><strong>Posted By:</strong> <?php echo htmlspecialchars($row['posted_by']); ?></p>

                <?php if (!empty($row['image'])) { ?>
                    <img class="item-image" src="../<?php echo htmlspecialchars($row['image']); ?>" alt="Lost item image">
                <?php } ?>

                <?php if ((int)$row['user_id'] !== $current_user_id && $row['status'] === 'open') { ?>
                    <form method="POST" action="claim_item.php" enctype="multipart/form-data">
                        <?php $proofLabel = claimProofLabel((string)($row['category'] ?? '')); ?>
                        <input type="hidden" name="item_id" value="<?php echo (int)$row['id']; ?>">
                        <input type="hidden" name="return_to" value="view_lost.php">
                        <label for="msg-lost-<?php echo (int)$row['id']; ?>">Claim Message (optional)</label>
                        <textarea id="msg-lost-<?php echo (int)$row['id']; ?>" name="message" placeholder="Share details about this item"></textarea>
                        <label for="answer1-lost-<?php echo (int)$row['id']; ?>">
                            <?php echo htmlspecialchars($row['verification_question_1'] ?: 'How can you verify your connection to this item?'); ?>
                        </label>
                        <textarea id="answer1-lost-<?php echo (int)$row['id']; ?>" name="verification_answer_1" required></textarea>
                        <label for="answer2-lost-<?php echo (int)$row['id']; ?>">
                            <?php echo htmlspecialchars($row['verification_question_2'] ?: 'What exact detail, place, or condition can you confirm?'); ?>
                        </label>
                        <textarea id="answer2-lost-<?php echo (int)$row['id']; ?>" name="verification_answer_2" required></textarea>
                        <label for="proof-lost-<?php echo (int)$row['id']; ?>"><?php echo htmlspecialchars($proofLabel); ?></label>
                        <input id="proof-lost-<?php echo (int)$row['id']; ?>" type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required>
                        <?php if (!empty($row['proof_instructions'])) { ?>
                            <p class="meta"><strong>Proof Required:</strong> <?php echo htmlspecialchars($row['proof_instructions']); ?></p>
                        <?php } ?>
                        <div class="actions">
                            <button class="btn btn-primary" type="submit">Claim This Item</button>
                        </div>
                    </form>
                <?php } ?>

                <?php if ((int)$row['user_id'] === $current_user_id) { ?>
                    <div class="actions">
                        <a class="btn btn-secondary" href="my_claims.php">View Claim Requests</a>
                    </div>
                <?php } ?>
            </article>
        <?php } ?>
    </section>
</main>
</body>
</html>
