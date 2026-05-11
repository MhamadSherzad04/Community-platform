-- Create the database
CREATE DATABASE IF NOT EXISTS community_db;
USE community_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  NOT NULL UNIQUE,
    email    VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- Posts table (with photo column added)
CREATE TABLE IF NOT EXISTS posts (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT  NOT NULL,
    category_id INT  NOT NULL,
    title       VARCHAR(255) NOT NULL,
    content     TEXT NOT NULL,
    photo       VARCHAR(255) DEFAULT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)     REFERENCES users(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Comments table
CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Add categories (only if table is empty)
INSERT INTO categories (name)
SELECT * FROM (SELECT 'Science' UNION SELECT 'Technology' UNION SELECT 'Health'
UNION SELECT 'History' UNION SELECT 'Art' UNION SELECT 'Gaming'
UNION SELECT 'Books' UNION SELECT 'General') AS tmp
WHERE NOT EXISTS (SELECT 1 FROM categories LIMIT 1);
