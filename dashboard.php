<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php';

// Get user statistics
$user_id = $_SESSION['user_id'];
$book_count = $pdo->query("SELECT COUNT(*) FROM books WHERE user_id = $user_id")->fetchColumn();
$wishlist_count = $pdo->query("SELECT COUNT(*) FROM wishlist WHERE user_id = $user_id")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookShare Hub - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <div class="dashboard-sidebar">
            <div class="brand">
                <i class="fas fa-book-reader"></i>
                <span>BookShare Hub</span>
            </div>
            
            <div class="user-profile">
                <!-- <div class="profile-image">
                    <img src="<?php echo $_SESSION['profile_pic'] ?? 'assets/default-avatar.png'; ?>" alt="Profile">
                    <span class="status-dot"></span>
                </div> -->
                <div class="user-info">
                    <h3><?php echo $_SESSION['username']; ?></h3>
                    <span class="user-role">Book Enthusiast</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">MAIN MENU</span>
                    <a href="#dashboard" class="nav-link active" data-section="dashboard">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">LIBRARY</span>
                    <a href="#my-books" class="nav-link" data-section="my-books">
                        <i class="fas fa-books"></i>
                        <span>My Books</span>
                        <span class="badge"><?php echo $book_count; ?></span>
                    </a>
                    <a href="#add-book" class="nav-link" data-section="add-book">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Book</span>
                    </a>
                    <a href="#wishlist" class="nav-link" data-section="wishlist">
                        <i class="fas fa-heart"></i>
                        <span>Wishlist</span>
                        <span class="badge"><?php echo $wishlist_count; ?></span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">SETTINGS</span>
                    <a href="#profile" class="nav-link" data-section="profile">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="./logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="content-wrapper">
                <div class="content-area" id="main-content">
                    <!-- Default content (Dashboard) -->
                    <div class="section-header">
                        <h2>Dashboard Overview</h2>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <i class="fas fa-book"></i>
                            <h3><?php echo $book_count; ?></h3>
                            <p>Books Added</p>
                        </div>
                        <div class="stat-card">
                            <i class="fas fa-heart"></i>
                            <h3><?php echo $wishlist_count; ?></h3>
                            <p>Wishlist Items</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mainContent = document.getElementById('main-content');
            const navLinks = document.querySelectorAll('.nav-link');

            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Remove active class from all links
                    navLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');

                    const section = this.getAttribute('data-section');
                    loadSection(section);
                });
            });

            function loadSection(section) {
                // Show loading state
                mainContent.innerHTML = '<div class="loading">Loading...</div>';

                // Fetch section content
                fetch(`get_section.php?section=${section}`)
                    .then(response => response.text())
                    .then(html => {
                        mainContent.innerHTML = html;
                        
                        // Initialize form if it's add-book section
                        if(section === 'add-book') {
                            initializeAddBookForm();
                        }
                    })
                    .catch(error => {
                        mainContent.innerHTML = '<div class="error">Error loading content</div>';
                    });
            }

            function initializeAddBookForm() {
                const form = document.getElementById('addBookForm');
                if(form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        
                        fetch('process_add_book.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                alert('Book added successfully!');
                                loadSection('my-books');
                            } else {
                                alert('Error adding book: ' + data.message);
                            }
                        });
                    });
                }
            }

            // Add global function for delete
            window.deleteBook = function(bookId) {
                if (!confirm('Are you sure you want to delete this book?')) {
                    return;
                }

                fetch('delete_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ book_id: bookId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const bookCard = document.querySelector(`.book-card[data-book-id="${bookId}"]`);
                        if (bookCard) {
                            bookCard.style.animation = 'fadeOut 0.3s ease-out forwards';
                            setTimeout(() => {
                                bookCard.remove();
                                // Check if no more books
                                const booksGrid = document.querySelector('.my-books-grid');
                                if (!booksGrid.querySelector('.book-card')) {
                                    loadSection('my-books');
                                }
                            }, 300);
                        }
                        showNotification('Book deleted successfully', 'success');
                    } else {
                        showNotification(data.message || 'Error deleting book', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error deleting book', 'error');
                });
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
        });
    </script>
</body>
</html>