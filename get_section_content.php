<?php
session_start();
require_once 'config.php';

$section = $_GET['section'] ?? 'overview';
$user_id = $_SESSION['user_id'];

switch($section) {
    case 'overview':
        // Get user statistics
        $book_count = $pdo->query("SELECT COUNT(*) FROM books WHERE user_id = $user_id")->fetchColumn();
        $wishlist_count = $pdo->query("SELECT COUNT(*) FROM wishlist WHERE user_id = $user_id")->fetchColumn();
        $review_count = $pdo->query("SELECT COUNT(*) FROM reviews WHERE user_id = $user_id")->fetchColumn();
        
        echo '<section class="dashboard-section">
                <h2>Dashboard Overview</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-book"></i>
                        <h3>'.$book_count.'</h3>
                        <p>Books Added</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-heart"></i>
                        <h3>'.$wishlist_count.'</h3>
                        <p>Wishlist Items</p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-star"></i>
                        <h3>'.$review_count.'</h3>
                        <p>Reviews Written</p>
                    </div>
                </div>
            </section>';
        break;

    case 'add-book':
        echo '<section class="dashboard-section">
                <h2>Add New Book</h2>
                <form id="addBookForm" class="add-book-form">
                    <div class="form-group">
                        <label for="title">Book Title</label>
                        <input type="text" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" required>
                    </div>
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="fiction">Fiction</option>
                            <option value="non-fiction">Non-Fiction</option>
                            <option value="mystery">Mystery</option>
                            <option value="romance">Romance</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="cover">Book Cover</label>
                        <input type="file" id="cover" name="cover" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Book</button>
                </form>
            </section>';
        break;

    case 'my-books':
        $stmt = $pdo->prepare("SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $books = $stmt->fetchAll();

        echo '<section class="dashboard-section">
                <h2>My Books</h2>';
        
        if(empty($books)) {
            echo '<div class="empty-state">
                    <i class="fas fa-books"></i>
                    <h3>No Books Added Yet</h3>
                    <p>Start sharing your literary collection by adding your first book.</p>
                    <button onclick="loadSectionContent(\'add-book\')" class="btn btn-primary">
                        Add Your First Book
                    </button>
                </div>';
        } else {
            echo '<div class="book-grid">';
            foreach($books as $book) {
                echo '<div class="book-card">
                        <img src="uploads/'.$book['cover'].'" alt="'.$book['title'].'">
                        <div class="book-info">
                            <h3>'.$book['title'].'</h3>
                            <p>'.$book['author'].'</p>
                            <div class="book-actions">
                                <button onclick="editBook('.$book['id'].')" class="btn btn-small">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="deleteBook('.$book['id'].')" class="btn btn-small btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>';
            }
            echo '</div>';
        }
        echo '</section>';
        break;

    case 'wishlist':
        $stmt = $pdo->prepare("
            SELECT b.* FROM books b 
            JOIN wishlist w ON b.id = w.book_id 
            WHERE w.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $wishlist = $stmt->fetchAll();

        echo '<section class="dashboard-section">
                <h2>My Wishlist</h2>';
        
        if(empty($wishlist)) {
            echo '<div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>Your Wishlist is Empty</h3>
                    <p>Browse the community books and add some to your wishlist.</p>
                    <a href="index.php#books" class="btn btn-primary">Browse Books</a>
                </div>';
        } else {
            echo '<div class="book-grid">';
            foreach($wishlist as $book) {
                echo '<div class="book-card">
                        <img src="uploads/'.$book['cover'].'" alt="'.$book['title'].'">
                        <div class="book-info">
                            <h3>'.$book['title'].'</h3>
                            <p>'.$book['author'].'</p>
                            <button onclick="removeFromWishlist('.$book['id'].')" class="btn btn-small btn-danger">
                                <i class="fas fa-heart-broken"></i> Remove
                            </button>
                        </div>
                    </div>';
            }
            echo '</div>';
        }
        echo '</section>';
        break;
}
?> 