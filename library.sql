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
-- Insert sample users (1 Admin + 21 College Students)
-- Default Password for all demo accounts: student123 (or admin123)
-- --------------------------------------------------------
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@library.com', 'admin123', 'admin'),
(2, 'Alex Johnson (Student)', 'student@library.com', 'student123', 'student'),
(3, 'Emma Watson', 'emma.watson@college.edu', 'student123', 'student'),
(4, 'Liam Davis', 'liam.davis@college.edu', 'student123', 'student'),
(5, 'Sophia Miller', 'sophia.miller@college.edu', 'student123', 'student'),
(6, 'Noah Wilson', 'noah.wilson@college.edu', 'student123', 'student'),
(7, 'Olivia Taylor', 'olivia.taylor@college.edu', 'student123', 'student'),
(8, 'Ethan Anderson', 'ethan.anderson@college.edu', 'student123', 'student'),
(9, 'Ava Thomas', 'ava.thomas@college.edu', 'student123', 'student'),
(10, 'Lucas Martinez', 'lucas.martinez@college.edu', 'student123', 'student'),
(11, 'Mia Garcia', 'mia.garcia@college.edu', 'student123', 'student'),
(12, 'Benjamin Robinson', 'benjamin.r@college.edu', 'student123', 'student'),
(13, 'Charlotte Clark', 'charlotte.c@college.edu', 'student123', 'student'),
(14, 'Henry Rodriguez', 'henry.rodriguez@college.edu', 'student123', 'student'),
(15, 'Amelia Lewis', 'amelia.lewis@college.edu', 'student123', 'student'),
(16, 'Daniel Lee', 'daniel.lee@college.edu', 'student123', 'student'),
(17, 'Harper Walker', 'harper.walker@college.edu', 'student123', 'student'),
(18, 'Matthew Hall', 'matthew.hall@college.edu', 'student123', 'student'),
(19, 'Evelyn Allen', 'evelyn.allen@college.edu', 'student123', 'student'),
(20, 'James Young', 'james.young@college.edu', 'student123', 'student'),
(21, 'Isabella King', 'isabella.king@college.edu', 'student123', 'student'),
(22, 'Mason Wright', 'mason.wright@college.edu', 'student123', 'student');

-- --------------------------------------------------------
-- Insert sample books catalogue
-- --------------------------------------------------------
INSERT INTO `books` (`book_id`, `title`, `author`, `category`, `quantity`, `available_quantity`) VALUES
(101, 'Database System Concepts', 'Abraham Silberschatz', 'DBMS', 5, 4),
(102, 'Operating System Concepts', 'Abraham Silberschatz', 'OS', 4, 3),
(103, 'Clean Code', 'Robert C. Martin', 'Programming', 4, 3),
(104, 'Python Crash Course', 'Eric Matthes', 'Programming', 6, 5),
(105, 'Introduction to Algorithms', 'Thomas H. Cormen', 'Algorithms', 5, 4),
(106, 'Computer Networks', 'Andrew S. Tanenbaum', 'Networking', 4, 3),
(107, 'Artificial Intelligence: A Modern Approach', 'Stuart Russell', 'AI', 5, 4),
(108, 'The Pragmatic Programmer', 'Andrew Hunt', 'Programming', 4, 3),
(109, 'Design Patterns: Elements of Reusable OOP', 'Erich Gamma', 'Software Eng', 4, 3),
(110, 'Computer Systems: A Programmer\'s Perspective', 'Randal E. Bryant', 'Systems', 4, 3),
(111, 'Modern Operating Systems', 'Andrew S. Tanenbaum', 'OS', 4, 3),
(112, 'Deep Learning', 'Ian Goodfellow', 'AI', 3, 3),
(113, 'Web Development with Node & Express', 'Ethan Brown', 'Web Dev', 5, 4),
(114, 'Discrete Mathematics & Its Applications', 'Kenneth H. Rosen', 'Mathematics', 5, 5);

-- --------------------------------------------------------
-- Insert sample transactions (Active Loans & Past Audit History)
-- Includes overdue loans to demonstrate fine calculations!
-- --------------------------------------------------------
INSERT INTO `transactions` (`transaction_id`, `user_id`, `book_id`, `issue_date`, `due_date`, `return_date`, `status`) VALUES
-- Overdue Active Loans (Fines in effect)
(1, 3, 101, '2026-11-01', '2026-11-15', NULL, 'issued'),
(2, 4, 103, '2026-11-06', '2026-11-20', NULL, 'issued'),
(3, 5, 105, '2026-11-09', '2026-11-23', NULL, 'issued'),
(4, 6, 107, '2026-11-11', '2026-11-25', NULL, 'issued'),
(5, 10, 109, '2026-11-03', '2026-11-17', NULL, 'issued'),
(6, 14, 111, '2026-11-08', '2026-11-22', NULL, 'issued'),
-- On-Schedule Active Loans ($0.00 Fine)
(7, 2, 104, '2026-11-20', '2026-12-04', NULL, 'issued'),
(8, 7, 102, '2026-11-22', '2026-12-06', NULL, 'issued'),
(9, 8, 106, '2026-11-24', '2026-12-08', NULL, 'issued'),
(10, 9, 108, '2026-11-25', '2026-12-09', NULL, 'issued'),
(11, 11, 110, '2026-11-26', '2026-12-10', NULL, 'issued'),
(12, 21, 113, '2026-11-25', '2026-12-09', NULL, 'issued'),
-- Completed / Returned History
(13, 12, 101, '2026-10-10', '2026-10-24', '2026-10-29', 'returned'),
(14, 13, 104, '2026-10-15', '2026-10-29', '2026-10-28', 'returned'),
(15, 15, 105, '2026-11-01', '2026-11-15', '2026-11-14', 'returned'),
(16, 16, 103, '2026-10-20', '2026-11-03', '2026-11-07', 'returned'),
(17, 17, 107, '2026-10-25', '2026-11-08', '2026-11-07', 'returned'),
(18, 18, 112, '2026-11-02', '2026-11-16', '2026-11-19', 'returned'),
(19, 19, 114, '2026-11-05', '2026-11-19', '2026-11-18', 'returned');
