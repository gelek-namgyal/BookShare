<?php 
session_start();
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookShare Hub - Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-book-reader"></i>
                <span>BookShare Hub</span>
            </a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="#books">Books</a>
                <a href="#categories">Categories</a>
                <a href="#about">About</a>
                
                <?php
                if(isset($_SESSION['user_id'])) {
                    // Show user menu when logged in
                    echo '
                    <div class="user-menu">
                        <img src="default-avatar.png" alt="Profile" class="profile-img">
                        <div class="dropdown-menu">
                            <a href="dashboard.php">My Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>';
                } else {
                    // Show auth buttons when logged out
                    echo '
                    <div class="auth-buttons">
                        <a href="login.php" class="auth-btn login-btn">Login</a>
                        <a href="register.php" class="auth-btn register-btn">Register</a>
                    </div>';
                }
                ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Share Your Literary Journey</h1>
            <p>Discover, share, and discuss books with readers worldwide</p>
            <div class="hero-buttons">
                <a href="#featured" class="btn btn-primary">Explore Books</a>
                <a href="#share" class="btn btn-secondary">Share a Book</a>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="search-section">
        <div class="container">
            <form class="search-form">
                <input type="text" placeholder="Search books, authors, or genres...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </section>

    <!-- Featured Books -->
    <section class="featured-books" id="featured">
        <div class="container">
            <h2>Featured Books</h2>
            <div class="book-grid">
                <?php
                // Corrected SQL query to fetch books with user information
                try {
                    $stmt = $pdo->prepare("
                        SELECT 
                            b.*, 
                            u.username,
                            (SELECT COUNT(*) FROM reviews r WHERE r.book_id = b.id) as review_count,
                            (SELECT COUNT(*) FROM wishlist w WHERE w.book_id = b.id) as wishlist_count
                        FROM books b 
                        INNER JOIN users u ON b.user_id = u.id 
                        ORDER BY b.created_at DESC
                    ");
                    $stmt->execute();
                    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch(PDOException $e) {
                    echo "Error: " . $e->getMessage();
                    $books = [];
                }
                ?>
                <?php if(empty($books)): ?>
                    <div class="no-books">
                        <i class="fas fa-books"></i>
                        <h3>No Books Available</h3>
                        <p>Be the first to share a book!</p>
                    </div>
                <?php else: ?>
                    <?php foreach($books as $book): ?>
                        <div class="book-card">
                            <div class="book-cover">
                                <img src="uploads/<?php echo htmlspecialchars($book['cover']); ?>" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <?php if(isset($_SESSION['user_id'])): ?>
                                    <button class="wishlist-btn" 
                                            onclick="toggleWishlist(<?php echo $book['id']; ?>)"
                                            data-book-id="<?php echo $book['id']; ?>">
                                        <i class="fas fa-heart <?php echo isInWishlist($_SESSION['user_id'], $book['id']) ? 'active' : ''; ?>"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="book-info">
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                <p class="category"><?php echo htmlspecialchars($book['category']); ?></p>
                                
                                <div class="book-meta">
                                    <span>
                                        <i class="fas fa-user"></i> 
                                        <?php echo htmlspecialchars($book['username']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-star"></i> 
                                        <?php echo $book['review_count']; ?> reviews
                                    </span>
                                    <span>
                                        <i class="fas fa-heart"></i> 
                                        <?php echo $book['wishlist_count']; ?>
                                    </span>
                                </div>

                                <div class="book-actions">
                                    <a href="book-details.php?id=<?php echo $book['id']; ?>" 
                                       class="btn btn-primary">
                                        View Details
                                    </a>
                                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] != $book['user_id']): ?>
                                        <button class="btn btn-secondary" 
                                                onclick="contactOwner(<?php echo $book['user_id']; ?>, '<?php echo htmlspecialchars($book['username']); ?>')">
                                            Contact Owner
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="container">
            <h2>Popular Categories</h2>
            <div class="category-grid">
                <a href="#" class="category-card">
                    <i class="fas fa-magic"></i>
                    <h3>Fantasy</h3>
                    <p>1.2k Books</p>
                </a>
                <a href="#" class="category-card">
                    <i class="fas fa-heart"></i>
                    <h3>Romance</h3>
                    <p>890 Books</p>
                </a>
                <!-- Add more categories -->
            </div>
        </div>
    </section>

    <!-- Community Section -->
    <section class="community">
        <div class="container">
            <h2>Join Our Community</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>5K+</h3>
                    <p>Active Readers</p>
                </div>
                <div class="stat-card">
                    <h3>10K+</h3>
                    <p>Books Shared</p>
                </div>
                <div class="stat-card">
                    <h3>2K+</h3>
                    <p>Daily Discussions</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Reviews -->
    <section class="recent-reviews">
        <div class="container">
            <h2>Recent Reviews</h2>
            <div class="review-grid">
                <!-- Add review cards -->
            </div>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h2>Stay Updated</h2>
            <p>Subscribe to our newsletter for the latest book recommendations</p>
            <form class="newsletter-form">
                <input type="email" placeholder="Enter your email">
                <button type="submit" class="btn">Subscribe</button>
            </form>
        </div>


    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About BookShare</h3>
                    <p>A community platform for book lovers to share and discover new reads.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#featured">Featured Books</a></li>
                        <li><a href="#categories">Categories</a></li>
                        <li><a href="#community">Community</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 BookShare Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
    function toggleWishlist(bookId) {
        if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
            window.location.href = 'login.php';
            return;
        }

        fetch('toggle_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ book_id: bookId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const wishlistBtn = document.querySelector(`[data-book-id="${bookId}"] i`);
                wishlistBtn.classList.toggle('active');
                showNotification(data.message, 'success');
            } else {
                showNotification(data.message, 'error');
            }
        });
    }

    function contactOwner(userId) {
        // Open chat/message modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Contact Book Owner</h3>
                <form id="contactForm">
                    <textarea placeholder="Write your message..." required></textarea>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        `;
        document.body.appendChild(modal);
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    </script>

</body>
</html>