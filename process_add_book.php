<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

try {
    // Handle file upload
    $targetDir = "uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $cover = $_FILES['cover'];
    $fileName = time() . '_' . basename($cover['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Validate file
    $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowTypes)) {
        throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed.');
    }

    if ($cover['size'] > 5000000) { // 5MB max
        throw new Exception('File is too large. Maximum size is 5MB.');
    }

    // Move uploaded file
    if (!move_uploaded_file($cover['tmp_name'], $targetFilePath)) {
        throw new Exception('Failed to upload file.');
    }

    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO books (
            user_id, title, author, category, 
            description, cover
        ) VALUES (
            ?, ?, ?, ?, 
            ?, ?
        )
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['title'],
        $_POST['author'],
        $_POST['category'],
        $_POST['description'],
        $fileName
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Book added successfully!',
        'book_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?> 