<div class="section-header">
    <h2>Add New Book</h2>
</div>

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