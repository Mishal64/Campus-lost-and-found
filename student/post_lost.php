<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$message = '';

function defaultVerificationConfig(string $category): array
{
    $normalized = strtolower(trim($category));

    if (preg_match('/phone|laptop|tablet|watch|earbud|camera|charger|electronics|electronic/', $normalized)) {
        return [
            'question_1' => 'What is the serial number, IMEI, or the last 4 to 6 digits of it?',
            'question_2' => 'What lock screen, wallpaper, case, sticker, or other unique detail can you confirm?',
            'proof' => 'Ask for invoice, warranty card, or an older photo proving ownership.',
        ];
    }

    if (preg_match('/jewel|jewellery|jewelry|ring|chain|bracelet|necklace|earring|anklet/', $normalized)) {
        return [
            'question_1' => 'What engraving, stone setting, clasp, or special mark does it have?',
            'question_2' => 'What size, number of stones, or hidden detail can you confirm?',
            'proof' => 'Ask for an older photo of the item being worn or stored.',
        ];
    }

    return [
        'question_1' => 'What unique identifying detail does only the real owner know?',
        'question_2' => 'What hidden content, mark, damage, or exact detail can you confirm?',
        'proof' => 'Ask for any older photo, receipt, or other supporting proof if available.',
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = (int) $_SESSION['user_id'];
    $item_name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $date = $_POST['date'] ?? null;
    $defaults = defaultVerificationConfig($category);
    $verification_question_1 = trim($_POST['verification_question_1'] ?? $defaults['question_1']);
    $verification_question_2 = trim($_POST['verification_question_2'] ?? $defaults['question_2']);
    $proof_instructions = trim($_POST['proof_instructions'] ?? $defaults['proof']);

    $image = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];

        if (in_array($_FILES['image']['type'], $allowed_types, true)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image = "uploads/" . $file_name;
            }
        }
    }

    $sql = "INSERT INTO items
            (user_id, type, item_name, description, category, location, date, image, verification_question_1, verification_question_2, proof_instructions)
            VALUES (?, 'lost', ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $message = "Error: verification database update is missing. Run config/verification-migration.sql.";
    } else {
    $stmt->bind_param(
        "isssssssss",
        $user_id,
        $item_name,
        $description,
        $category,
        $location,
        $date,
        $image,
        $verification_question_1,
        $verification_question_2,
        $proof_instructions
    );

        if ($stmt->execute()) {
            $message = "Lost item posted successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Lost Item</title>
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
    <div class="page-head">
        <h2>Report Lost Item</h2>
        <a class="btn btn-secondary" href="../dashboard.php">Back</a>
    </div>

    <?php if ($message !== ''): ?>
        <p class="msg <?php echo str_starts_with($message, 'Error') ? 'msg-error' : 'msg-success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <section class="card">
        <form method="POST" enctype="multipart/form-data">
            <label for="name">Item Name</label>
            <input id="name" type="text" name="name" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>

            <label for="category">Category</label>
            <input id="category" type="text" name="category">

            <label for="location">Location</label>
            <input id="location" type="text" name="location">

            <label for="date">Date Lost</label>
            <input id="date" type="date" name="date">

            <label for="image">Upload Image (optional)</label>
            <input id="image" type="file" name="image" accept="image/*">

            <label for="verification_question_1">Private Verification Question 1</label>
            <input id="verification_question_1" type="text" name="verification_question_1" placeholder="Example: What is the serial number or engraving?">

            <label for="verification_question_2">Private Verification Question 2</label>
            <input id="verification_question_2" type="text" name="verification_question_2" placeholder="Example: What unique mark or hidden detail does it have?">

            <label for="proof_instructions">Proof To Ask From Claimant</label>
            <input id="proof_instructions" type="text" name="proof_instructions" placeholder="Example: Upload an older photo, invoice, or warranty card">

            <div class="actions">
                <button type="submit" class="btn btn-primary">Submit Report</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
