<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bookId = $data['book_id'] ?? null;

if (!$bookId) {
    echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    exit;
}

try {
    // First verify the book belongs to the user
    $stmt = $pdo->prepare("SELECT cover FROM books WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookId, $_SESSION['user_id']]);
    $book = $stmt->fetch();

    if (!$book) {
        throw new Exception('Book not found or you don\'t have permission to delete it');
    }

    // Delete the book cover file
    $coverPath = "uploads/" . $book['cover'];
    if (file_exists($coverPath)) {
        unlink($coverPath);
    }

    // Delete the book from database
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ? AND user_id = ?");
    $stmt->execute([$bookId, $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Book deleted successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 