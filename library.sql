-- ========================================================
-- LIBRARY MANAGEMENT SYSTEM - DATABASE SCHEMA & SAMPLE DATA
-- Software Engineering-I Academic Prototype
-- ========================================================

CREATE DATABASE IF NOT EXISTS `library_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_db`;

-- Drop tables if they already exist (in correct dependency order)
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `books`
-- --------------------------------------------------------
CREATE TABLE `books` (
  `book_id` INT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `author` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `available_quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `transactions`
-- --------------------------------------------------------
CREATE TABLE `transactions` (
  `transaction_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `book_id` INT NOT NULL,
  `issue_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `return_date` DATE DEFAULT NULL,
  `status` ENUM('issued', 'returned') NOT NULL DEFAULT 'issued',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_transaction_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_transaction_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Insert sample users
-- Credentials:
-- Admin:   admin@library.com   / admin123
-- Student: student@library.com / student123
-- (On first login, the application automatically upgrades
-- plain passwords to secure bcrypt hashes via password_hash)
-- --------------------------------------------------------
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@library.com', 'admin123', 'admin'),
(2, 'Alex Johnson (Student)', 'student@library.com', 'student123', 'student');

-- Also provide plain text fallback check in login.php so both bcrypt and initial demo seeds work seamlessly!

-- --------------------------------------------------------
-- Insert sample books (Exact sample records from specifications)
-- --------------------------------------------------------
INSERT INTO `books` (`book_id`, `title`, `author`, `category`, `quantity`, `available_quantity`) VALUES
(101, 'Database System Concepts', 'Abraham Silberschatz', 'DBMS', 3, 3),
(102, 'Operating System Concepts', 'Abraham Silberschatz', 'OS', 2, 2),
(103, 'Clean Code', 'Robert C. Martin', 'Programming', 2, 2),
(104, 'Python Crash Course', 'Eric Matthes', 'Programming', 4, 4),
(105, 'Introduction to Algorithms', 'Thomas H. Cormen', 'Algorithms', 3, 3),
(106, 'Computer Networks', 'Andrew S. Tanenbaum', 'Networking', 2, 2),
(107, 'Artificial Intelligence', 'Stuart Russell', 'AI', 3, 3),
(108, 'The Pragmatic Programmer', 'Andrew Hunt', 'Programming', 2, 2);
