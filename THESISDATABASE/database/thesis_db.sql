DROP DATABASE IF EXISTS thesis_db;
CREATE DATABASE thesis_db;
USE thesis_db;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'user') DEFAULT 'user',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE theses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    authors VARCHAR(255) NOT NULL,
    abstract TEXT NOT NULL,
    category ENUM('computer_science', 'information_technology', 'engineering', 'other') NOT NULL,
    keywords VARCHAR(255),
    file_path VARCHAR(255) NOT NULL,
    user_id INT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    publication_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    thesis_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (thesis_id) REFERENCES theses(id),
    UNIQUE KEY unique_bookmark (user_id, thesis_id)
);


INSERT INTO users (full_name, email, password, user_type, status) 
VALUES ('System Administrator', 'admin@admin.com', 'admin123', 'admin', 'approved');