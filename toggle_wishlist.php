<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bookId = $data['book_id'];
$userId = $_SESSION['user_id'];

try {
    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    $stmt->execute([$userId, $bookId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Remove from wishlist
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$userId, $bookId]);
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    } else {
        // Add to wishlist
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        $stmt->execute([$userId, $bookId]);
        echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error updating wishlist']);
}
?> 