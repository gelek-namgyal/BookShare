<?php
function isInWishlist($userId, $bookId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$userId, $bookId]);
    return $stmt->fetchColumn() > 0;
}
?> 