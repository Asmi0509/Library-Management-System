# 🌿 Eco-Library Management System
### Software Engineering-I | Academic Prototype & Full Working Demo

![PHP](https://img.shields.io/badge/PHP-8.x%20%7C%207.4-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-2e8b57?style=for-the-badge)

A responsive, web-based digital library portal developed for university **Software Engineering-I** and **Database Management** academic demonstrations. 

Designed with an elegant **botanical emerald green theme**, **picturesque leafy backgrounds**, and **glassmorphic cards**, the system features robust concurrency safeguards, automated overdue fine tracking, student loan quota enforcement, and reporting tools.

---

## 🌟 Key Features

### 🎓 1. Student Portal (`student_dashboard.php`)
* **Live Catalogue Search & Filter:** Real-time search across titles, authors, and categories with an **"In-Stock Only"** toggle.
* **Student Profile & Avatars:** High-definition student profile picture, ID badge, and verified academic credentials.
* **Atomic Borrowing:** Atomically decrements shelf copies with zero-stock protection.
* **Borrowing Quota:** Limits students to **3 concurrent active books** at a time.
* **Loan Renewal (`renew.php`):** 1-click **"Renew (+7d)"** button to extend borrowing periods (guards against renewing overdue books).
* **Automated Overdue Fines:** Calculates overdue charges (**$1.00 per day late**) and highlights delinquent loans in red.
* **Borrowing History:** Audit trail of all returned books with return dates and late fee logs.

### 🛡️ 2. Administrator Portal (`admin_dashboard.php`)
* **Circulation Metrics:** Live dashboard tracking total copies, unique titles, available stock, active loans, and total pending fines.
* **Inventory Management:** Full CRUD operations to add, edit, or safely delete catalogue titles (safeguarded against deleting books with active loans).
* **Loan Monitoring & Overrides:** Monitor all issued books across the student body with 1-click return processing and loan renewals.
* **Student Directory:** Browse all 21 registered student members with photos, active loan counts, and lifetime statistics.
* **CSV Audit Export (`export_csv.php`):** Download a spreadsheet of all active loans and completed transactions.
* **Print Summary:** Printable circulation report (`window.print()`).

---

## 👥 Demo Accounts (Pre-configured)

Default password for all student accounts: **`student123`**  
Default password for administrator: **`admin123`**

| Role | Account Name | Email Address | Status / Demo Feature |
| :--- | :--- | :--- | :--- |
| **System Admin** | System Administrator | `admin@library.com` | Full Admin Portal, Circulation Oversight, CSV Export |
| **Student** | Emma Watson | `emma.watson@college.edu` | **Overdue Demo ($11.00 fine, 11 days late)** |
| **Student** | Liam Davis | `liam.davis@college.edu` | **Overdue Demo ($6.00 fine, 6 days late)** |
| **Student** | Alex Johnson | `student@library.com` | **Clean Standing (Eligible for +7d loan renewal)** |
| **Student** | Olivia Taylor | `olivia.taylor@college.edu` | Clean Standing |
| *+17 More Students* | *Diverse Class of 2027* | `[first].[last]@college.edu` | Listed in Admin Student Directory |

> 💡 **Demo Helper on Login:** The login page (`login.php`) includes 1-click quick-fill buttons: **"Clean Student"**, **"Overdue Student"**, and **"Admin"**.

---

## 🛠️ Technology Stack & Architecture

* **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, Google Fonts (*Plus Jakarta Sans*)
* **Backend:** PHP 7.4 / 8.x (Vanilla, Modular Architecture)
* **Database Access:** PHP PDO (PHP Data Objects) with 100% prepared statements (immunizing against SQL Injection)
* **Authentication & Security:** Bcrypt hashing (`password_hash`), Session RBAC (`require_admin`, `require_student`)
* **Database:** MySQL / MariaDB (InnoDB engine with Foreign Keys and Cascading Integrity)
* **Local Server:** XAMPP (Apache & MySQL)

---

## 📁 Project Directory Structure

```text
library-management-system/
├── index.php                 # Home landing page with live statistics and student avatar strip
├── login.php                 # Authentication page with 1-click demo autofill
├── register.php              # Student registration with validation and password hashing
├── logout.php                # Secure session termination
├── student_dashboard.php     # Student portal: search, live loans, fines, quota tracker
├── admin_dashboard.php       # Admin portal: metrics, inventory CRUD, issued loans, student directory
├── catalogue.php             # Full-width searchable catalogue with category & availability filters
├── borrow.php                # Atomic book borrowing controller with quota check
├── return.php                # Book return controller with inventory restoration
├── renew.php                 # 7-day loan extension controller
├── add_book.php              # Admin controller to add new book titles
├── edit_book.php             # Admin controller to modify book records
├── delete_book.php           # Admin controller to safely delete books
├── export_csv.php            # Admin transaction audit log export (CSV spreadsheet)
├── config/
│   └── database.php          # PDO database connection & business constants
├── includes/
│   ├── auth.php              # Session management, RBAC, fine calculator, avatar helper
│   ├── header.php            # Shared navbar with student profile dropdown & typography
│   └── footer.php            # Shared footer component
├── assets/
│   ├── css/
│   │   └── style.css         # Botanical emerald green stylesheet & glassmorphism
│   └── js/
│       └── script.js         # Real-time search filter & autofill interactions
├── database/
│   └── library.sql           # Database schema definition, 14 books & 21 student seeds
└── README.md                 # Complete documentation
```

---

## 🚀 Installation & Local Setup (XAMPP)

### Step 1: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Start both **Apache** and **MySQL**.

### Step 2: Place Project Files in `htdocs`
Copy or extract this folder to:
```text
C:\xampp\htdocs\Library-Management-System-main\
```

### Step 3: Import the Database
1. Open your browser and navigate to **phpMyAdmin**:
   ```text
   http://localhost/phpmyadmin/
   ```
2. Click on the **Import** tab in the top navigation bar.
3. Click **Choose File** and select:
   ```text
   database/library.sql
   ```
4. Scroll to the bottom and click **Import** (or **Go**).
   *(The SQL script automatically creates the `library_db` database, tables, demo books, and student accounts).*

### Step 4: Open the Website
Open your browser and visit:
```text
http://localhost/Library-Management-System-main/
```

---

## 📋 Academic Test Case Matrix

| Test ID | Scenario | Input Data | Expected Result | Status |
| :---: | :--- | :--- | :--- | :---: |
| **TC-01** | Standard Borrowing | Book with `available > 0` | Available count decrements by 1; 14-day due date set | **PASS** |
| **TC-02** | Zero-Stock Defense | Book with `available = 0` | Borrow button disabled; backend rejects invalid attempt | **PASS** |
| **TC-03** | Duplicate Prevention | Borrowing same title twice | System blocks duplicate: "Already borrowed" | **PASS** |
| **TC-04** | Quota Enforcement | Borrowing 4th book when holding 3 | System rejects: "Borrowing quota reached (Max: 3)" | **PASS** |
| **TC-05** | Overdue Fine Engine | Book past due date by 11 days | Highlights in red: "11d late ($11.00)" | **PASS** |
| **TC-06** | Loan Extension | Click "Renew (+7d)" on active loan | Extends due date by 7 days; blocks if overdue | **PASS** |
| **TC-07** | Inventory Restoration | Click "Return Book" | Restores available quantity by +1; status &rarr; 'returned' | **PASS** |
| **TC-08** | Audit Export | Click "Export CSV" | Generates downloadable Excel/CSV circulation audit log | **PASS** |

---

## 📜 License

This project is open-source under the [MIT License](LICENSE).
