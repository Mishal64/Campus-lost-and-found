<?php
declare(strict_types=1);

require __DIR__ . '/../config/database.php';

function assertCondition(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    assertCondition($conn instanceof mysqli, 'Database connection is not available.');

    $ownerQuery = "SELECT id, item_name, type, status, category, location, date
                   FROM items
                   ORDER BY created_at DESC
                   LIMIT 1";
    $ownerResult = $conn->query($ownerQuery);
    assertCondition($ownerResult !== false, 'Failed to query latest item.');
    assertCondition($ownerResult->num_rows > 0, 'No items found to smoke test recent posts.');

    $item = $ownerResult->fetch_assoc();
    assertCondition(isset($item['id']), 'Latest item query did not return an item id.');
    assertCondition(in_array($item['type'], ['lost', 'found'], true), 'Item type is outside expected values.');
    assertCondition(in_array($item['status'], ['open', 'claimed', 'returned'], true), 'Item status is outside expected values.');

    $recentStmt = $conn->prepare(
        "SELECT id, item_name, type, status, category, location, date
         FROM items
         WHERE user_id = (
            SELECT user_id
            FROM items
            WHERE id = ?
         )
         ORDER BY created_at DESC
         LIMIT 5"
    );
    assertCondition($recentStmt !== false, 'Failed to prepare recent posts query.');
    $recentStmt->bind_param('i', $item['id']);
    $recentStmt->execute();
    $recentResult = $recentStmt->get_result();
    assertCondition($recentResult->num_rows > 0, 'Recent posts query returned no rows for the owner.');

    while ($row = $recentResult->fetch_assoc()) {
        assertCondition(isset($row['id']), 'Recent posts row is missing id.');
        assertCondition($row['item_name'] !== '', 'Recent posts row is missing item name.');
    }

    $detailStmt = $conn->prepare(
        "SELECT items.id
         FROM items
         WHERE items.id = ?
         AND items.user_id = (
            SELECT user_id
            FROM items
            WHERE id = ?
         )"
    );
    assertCondition($detailStmt !== false, 'Failed to prepare owned item detail query.');
    $detailStmt->bind_param('ii', $item['id'], $item['id']);
    $detailStmt->execute();
    $detailResult = $detailStmt->get_result();
    assertCondition($detailResult->num_rows === 1, 'Owned item detail query did not return exactly one row.');

    $otherUserStmt = $conn->prepare(
        "SELECT users.id
         FROM users
         WHERE users.id <> (
            SELECT user_id
            FROM items
            WHERE id = ?
         )
         LIMIT 1"
    );
    assertCondition($otherUserStmt !== false, 'Failed to prepare non-owner lookup.');
    $otherUserStmt->bind_param('i', $item['id']);
    $otherUserStmt->execute();
    $otherUserResult = $otherUserStmt->get_result();

    if ($otherUserResult->num_rows === 1) {
        $otherUser = $otherUserResult->fetch_assoc();
        $blockedStmt = $conn->prepare("SELECT id FROM items WHERE id = ? AND user_id = ?");
        assertCondition($blockedStmt !== false, 'Failed to prepare non-owner access check.');
        $blockedStmt->bind_param('ii', $item['id'], $otherUser['id']);
        $blockedStmt->execute();
        $blockedResult = $blockedStmt->get_result();
        assertCondition($blockedResult->num_rows === 0, 'Non-owner should not be able to resolve the owned item detail query.');
    }

    echo "Dashboard smoke test passed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Dashboard smoke test failed: " . $e->getMessage() . "\n");
    exit(1);
}
