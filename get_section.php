<?php
session_start();
require_once 'config.php';

$section = $_GET['section'] ?? 'dashboard';
$user_id = $_SESSION['user_id'];

switch($section) {
    case 'my-books':
        echo '<div class="section-header">
                <h2>My Books Collection</h2>
                <p>Manage your shared books</p>
              </div>';
        
        // Fetch user's books with counts
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   COALESCE((SELECT COUNT(*) FROM wishlist w WHERE w.book_id = b.id), 0) as wishlist_count,
                   COALESCE((SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id), 0) as review_count
            FROM books b 
            WHERE b.user_id = ? 
            ORDER BY b.created_at DESC
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        $books = $stmt->fetchAll();

        if (empty($books)) {
            echo '<div class="no-books-message">
                    <i class="fas fa-book"></i>
                    <h3>No Books Added Yet</h3>
                    <p>Start sharing your books with the community!</p>
                    <button onclick="loadSection(\'add-book\')" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Book
                    </button>
                  </div>';
        } else {
            echo '<div class="my-books-grid">';
            foreach ($books as $book) {
                echo '<div class="book-card" data-book-id="'.$book['id'].'">
                        <div class="book-cover">
                            <img src="uploads/'.htmlspecialchars($book['cover']).'" 
                                 alt="'.htmlspecialchars($book['title']).'">
                            <div class="book-actions">
                                <button onclick="deleteBook('.$book['id'].')" class="delete-btn">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="book-info">
                            <h3>'.htmlspecialchars($book['title']).'</h3>
                            <p class="author">by '.htmlspecialchars($book['author']).'</p>
                            <p class="category">'.htmlspecialchars($book['category']).'</p>
                            <div class="book-stats">
                                <span><i class="fas fa-heart"></i> '.$book['wishlist_count'].'</span>
                                <span><i class="fas fa-star"></i> '.$book['review_count'].'</span>
                            </div>
                        </div>
                      </div>';
            }
            echo '</div>';
        }
        break;

    case 'wishlist':
        $stmt = $pdo->prepare("
            SELECT b.* FROM books b 
            JOIN wishlist w ON b.id = w.book_id 
            WHERE w.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $wishlist = $stmt->fetchAll();
        
        if(empty($wishlist)) {
            echo '<div class="empty-state">
                    <i class="fas fa-heart"></i>
                    <h3>Your Wishlist is Empty</h3>
                    <p>Browse books and add them to your wishlist.</p>
                </div>';
        } else {
            echo '<div class="section-header">
                    <h2>My Wishlist</h2>
                </div>
                <div class="book-grid">';
            foreach($wishlist as $book) {
                echo '<div class="book-card">
                        <img src="uploads/'.$book['cover'].'" alt="'.$book['title'].'">
                        <div class="book-info">
                            <h3>'.$book['title'].'</h3>
                            <p>'.$book['author'].'</p>
                            <button class="btn btn-danger" onclick="removeFromWishlist('.$book['id'].')">
                                Remove from Wishlist
                            </button>
                        </div>
                    </div>';
            }
            echo '</div>';
        }
        break;

    case 'add-book':
        echo '<div class="section-header">
                <h2>Add New Book</h2>
                <p class="section-description">Share your book with the community</p>
              </div>
              
              <div class="form-container">
                <form id="addBookForm" class="add-book-form" enctype="multipart/form-data" method="POST">
                    <div class="form-grid">
                        <div class="form-left">
                            <div class="form-group">
                                <label for="title">Book Title <span class="required">*</span></label>
                                <input type="text" id="title" name="title" required 
                                    placeholder="Enter book title">
                            </div>
                            
                            <div class="form-group">
                                <label for="author">Author <span class="required">*</span></label>
                                <input type="text" id="author" name="author" required 
                                    placeholder="Enter author name">
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category <span class="required">*</span></label>
                                <select id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="fiction">Fiction</option>
                                    <option value="non-fiction">Non-Fiction</option>
                                    <option value="mystery">Mystery & Thriller</option>
                                    <option value="romance">Romance</option>
                                    <option value="sci-fi">Science Fiction</option>
                                    <option value="fantasy">Fantasy</option>
                                    <option value="biography">Biography</option>
                                    <option value="history">History</option>
                                    <option value="children">Children</option>
                                    <option value="young-adult">Young Adult</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description">Description <span class="required">*</span></label>
                                <textarea id="description" name="description" required 
                                    placeholder="Enter book description" rows="4"></textarea>
                                <span class="char-count">0/500 characters</span>
                            </div>
                        </div>

                        <div class="form-right">
                            <div class="form-group">
                                <label for="cover">Book Cover <span class="required">*</span></label>
                                <div class="cover-upload-container">
                                    <input type="file" id="cover" name="cover" accept="image/*" required
                                        class="cover-input" onchange="previewImage(this)">
                                    <div class="cover-preview" id="coverPreview">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload or drag and drop</p>
                                        <span class="file-info">Maximum file size: 5MB (JPG, PNG)</span>
                                    </div>
                                    <img id="imagePreview" src="#" alt="Preview" style="display: none;">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="isbn">ISBN (Optional)</label>
                                <input type="text" id="isbn" name="isbn" 
                                    placeholder="Enter ISBN number">
                            </div>
                            
                            <div class="form-group">
                                <label for="publication_year">Publication Year (Optional)</label>
                                <input type="number" id="publication_year" name="publication_year" 
                                    min="1800" max="2024" placeholder="YYYY">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="button" class="btn btn-secondary" onclick="resetForm()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Book
                        </button>
                    </div>
                </form>
              </div>';
        break;

    // Add more cases for other sections
}
?> 