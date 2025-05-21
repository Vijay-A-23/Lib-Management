-- Admin table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id VARCHAR(50) NOT NULL UNIQUE,
    book_name VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Trigger to update updated_at (not strictly needed due to ON UPDATE above, but included for structure)
DELIMITER //

CREATE TRIGGER update_books_timestamp
BEFORE UPDATE ON books
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END;
//

DELIMITER ;

-- Borrowed books table
CREATE TABLE IF NOT EXISTS borrowed_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_date TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
);

-- Create default admin account
INSERT INTO admins (name, email, password) VALUES 
('Admin User', 'admin@library.com', '$2y$10$Vh9oCkSt0JfLgGkiNSp.nOjzK4QpOETQIzO6CZfkn5iysz3ztAQ3a');
-- Default admin password is 'admin123'

-- Sample books data
INSERT INTO books (book_id, book_name, author, total_copies, available_copies) VALUES
('ISBN-001', 'To Kill a Mockingbird', 'Harper Lee', 5, 5),
('ISBN-002', '1984', 'George Orwell', 3, 3),
('ISBN-003', 'The Great Gatsby', 'F. Scott Fitzgerald', 4, 4),
('ISBN-004', 'Pride and Prejudice', 'Jane Austen', 2, 2),
('ISBN-005', 'The Catcher in the Rye', 'J.D. Salinger', 3, 3),
('ISBN-006', 'Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', 6, 6),
('ISBN-007', 'The Lord of the Rings', 'J.R.R. Tolkien', 2, 2),
('ISBN-008', 'The Hobbit', 'J.R.R. Tolkien', 3, 3),
('ISBN-009', 'Animal Farm', 'George Orwell', 4, 4),
('ISBN-010', 'Brave New World', 'Aldous Huxley', 2, 2);

-- Create indexes for faster searching
CREATE INDEX idx_books_name ON books(book_name);
CREATE INDEX idx_books_author ON books(author);
CREATE INDEX idx_borrowed_user ON borrowed_books(user_id);
CREATE INDEX idx_borrowed_book ON borrowed_books(book_id);
CREATE INDEX idx_borrowed_status ON borrowed_books(return_date);
