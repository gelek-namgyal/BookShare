document.addEventListener('DOMContentLoaded', function() {
    // Load initial section
    loadSection('overview');

    // Add click handlers to all nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all links
            document.querySelectorAll('.nav-link').forEach(l => {
                l.classList.remove('active');
            });
            
            // Add active class to clicked link
            this.classList.add('active');
            
            // Load the corresponding section
            const section = this.getAttribute('data-section');
            loadSection(section);
        });
    });
});

function loadSection(section) {
    const contentArea = document.querySelector('.dashboard-content');
    
    // Show loading indicator
    contentArea.innerHTML = '<div class="loading">Loading...</div>';
    
    // Fetch the section content
    fetch(`sections/${section}.php`)
        .then(response => response.text())
        .then(html => {
            contentArea.innerHTML = html;
            
            // Initialize form if it's the add-book section
            if(section === 'add-book') {
                initializeAddBookForm();
            }
        })
        .catch(error => {
            contentArea.innerHTML = '<div class="error">Error loading content</div>';
        });
}

function initializeAddBookForm() {
    const form = document.getElementById('addBookForm');
    const coverInput = document.getElementById('cover');
    const coverPreview = document.getElementById('coverPreview');
    const description = document.getElementById('description');
    const charCount = document.querySelector('.char-count');

    if (form) {
        // Cover upload preview
        coverPreview.addEventListener('click', () => coverInput.click());
        
        coverInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    coverPreview.innerHTML = `
                        <img src="${e.target.result}" alt="Cover Preview" 
                            style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                        <p class="mt-2">${file.name}</p>
                    `;
                }
                reader.readAsDataURL(file);
            }
        });

        // Character count for description
        description.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count}/500 characters`;
            if (count > 500) {
                charCount.style.color = 'var(--danger)';
            } else {
                charCount.style.color = 'var(--text-secondary)';
            }
        });

        // Form submission
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;

            try {
                const response = await fetch('process_add_book.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Show success message
                    showNotification(data.message, 'success');
                    
                    // Reset form
                    form.reset();
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('coverPreview').style.display = 'flex';
                    
                    // Redirect to books section
                    setTimeout(() => {
                        loadSection('my-books');
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('An error occurred while adding the book', 'error');
            } finally {
                // Restore button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        });
    }
}

function resetForm() {
    const form = document.getElementById('addBookForm');
    const preview = document.getElementById('imagePreview');
    const coverPreview = document.getElementById('coverPreview');
    
    form.reset();
    preview.style.display = 'none';
    coverPreview.style.display = 'flex';
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

// Add smooth hover effect for menu items
document.querySelectorAll('.dashboard-nav a').forEach(link => {
    link.addEventListener('mouseenter', function() {
        this.style.transform = 'translateX(5px)';
    });
    
    link.addEventListener('mouseleave', function() {
        if (!this.classList.contains('active')) {
            this.style.transform = 'translateX(0)';
        }
    });
});

// Add smooth scrolling for mobile
if (window.innerWidth <= 768) {
    const sidebarLinks = document.querySelectorAll('.dashboard-nav a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
} 

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const coverPreview = document.getElementById('coverPreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            coverPreview.style.display = 'none';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function resetForm() {
    const form = document.getElementById('addBookForm');
    const preview = document.getElementById('imagePreview');
    const coverPreview = document.getElementById('coverPreview');
    
    form.reset();
    preview.style.display = 'none';
    coverPreview.style.display = 'flex';
} 

function deleteBook(bookId) {
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
            // Remove the book card from DOM
            const bookCard = document.querySelector(`.book-card[data-book-id="${bookId}"]`);
            if (bookCard) {
                bookCard.style.animation = 'fadeOut 0.3s ease-out forwards';
                setTimeout(() => {
                    bookCard.remove();
                    
                    // Check if there are no more books
                    const booksGrid = document.querySelector('.my-books-grid');
                    if (booksGrid && !booksGrid.querySelector('.book-card')) {
                        loadSection('my-books'); // Reload to show empty state
                    }
                }, 300);
            }
            
            // Show success notification
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

