
-- Database creation
CREATE DATABASE IF NOT EXISTS digital_library;
USE digital_library;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(100) NOT NULL,
    user_type ENUM('user', 'admin') DEFAULT 'user',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user (username: admin, password: admin123)
INSERT INTO users (username, email, phone, password, user_type) VALUES 
('admin', 'admin@library.com', '9999999999', 'admin123', 'admin');

-- Books table
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    genre VARCHAR(50) NOT NULL,
    format ENUM('Physical', 'E-Book', 'Both') NOT NULL,
    available_copies INT DEFAULT 1,
    description TEXT,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- E-books table
CREATE TABLE ebooks (
    ebook_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    genre VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    description TEXT,
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Borrowed books table
CREATE TABLE borrowed_books (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status ENUM('borrowed', 'renewed', 'returned', 'overdue') DEFAULT 'borrowed',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- User e-books (tracking which e-books users have accessed)
CREATE TABLE user_ebooks (
    user_ebook_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ebook_id INT NOT NULL,
    access_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_favorite BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(ebook_id) ON DELETE CASCADE
);

-- Favorites table
CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT,
    ebook_id INT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (ebook_id) REFERENCES ebooks(ebook_id) ON DELETE CASCADE
);

-- Transactions table
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    borrow_id INT,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('Credit/Debit Card', 'UPI') NOT NULL,
    payment_status ENUM('pending', 'successful') DEFAULT 'pending',
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    upi_id VARCHAR(50),
    card_number VARCHAR(20),
    transaction_details TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (borrow_id) REFERENCES borrowed_books(borrow_id) ON DELETE SET NULL
);

-- System settings table for payment settings
CREATE TABLE system_settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default system settings
INSERT INTO system_settings (setting_name, setting_value) VALUES
('upi_id', 'library@upi'),
('qr_image_path', 'images/default_qr.png');

-- Sample data for books
INSERT INTO books (title, author, isbn, genre, format, available_copies, description) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Fiction', 'Both', 5, 'A story of wealth, love and tragedy in the Roaring Twenties.'),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Fiction', 'Physical', 3, 'A classic of American literature about racial injustice.'),
('1984', 'George Orwell', '9780451524935', 'Fiction', 'Both', 7, 'A dystopian novel about totalitarianism and surveillance.'),
('The Hobbit', 'J.R.R. Tolkien', '9780547928227', 'Fantasy', 'Physical', 2, 'A fantasy novel about the adventure of Bilbo Baggins.'),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 'Romance', 'Both', 4, 'A romantic novel about manners and marriage in 19th century England.'),
('The Catcher in the Rye', 'J.D. Salinger', '9780316769488', 'Fiction', 'Physical', 3, 'A novel about teenage alienation and angst.'),
('Harry Potter and the Sorcerer''s Stone', 'J.K. Rowling', '9780590353427', 'Fantasy', 'Both', 8, 'The first book in the Harry Potter series.'),
('The Lord of the Rings', 'J.R.R. Tolkien', '9780618640157', 'Fantasy', 'Physical', 2, 'An epic high fantasy novel.'),
('The Alchemist', 'Paulo Coelho', '9780062315007', 'Fiction', 'Both', 6, 'A philosophical novel about following your dreams.'),
('The Da Vinci Code', 'Dan Brown', '9780307474278', 'Mystery', 'E-Book', 0, 'A mystery thriller novel.');

-- Sample data for e-books
INSERT INTO ebooks (title, author, isbn, genre, file_path, description) VALUES
('Digital Fortress', 'Dan Brown', '9780312995423', 'Mystery', 'ebooks/digital_fortress.pdf', 'A techno-thriller novel about cryptography.'),
('The Hunger Games', 'Suzanne Collins', '9780439023481', 'Science Fiction', 'ebooks/hunger_games.pdf', 'A dystopian novel set in a future North America.'),
('Dune', 'Frank Herbert', '9780441172719', 'Science Fiction', 'ebooks/dune.pdf', 'A science fiction novel set in a distant future.'),
('The Shining', 'Stephen King', '9780307743657', 'Horror', 'ebooks/the_shining.pdf', 'A horror novel about a family who becomes caretakers of a haunted hotel.'),
('Gone Girl', 'Gillian Flynn', '9780307588371', 'Thriller', 'ebooks/gone_girl.pdf', 'A thriller novel about a woman who disappears on her fifth wedding anniversary.');
