<?php
/**
 * Database Configuration & Connection
 * Connects to MySQL using PDO for secure prepared statements.
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

// Academic Project Business Constants
define('FINE_RATE_PER_DAY', 1.00); // $1.00 per day late fine
define('MAX_BORROW_LIMIT', 3);     // Maximum 3 concurrent active books per student
define('LOAN_PERIOD_DAYS', 14);    // Standard borrowing duration in days
define('RENEW_EXTEND_DAYS', 7);    // Loan extension duration in days

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Graceful error display if database is not created or MySQL is offline
    die('
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Connection Error - Library Management System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4 text-center">
                            <div class="text-danger mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-database-exclamation" viewBox="0 0 16 16">
                                  <path d="M8 1c-1.573 0-3.022.289-4.096.777C2.823 2.26 2 2.927 2 3.7v8.6c0 .773.823 1.44 1.904 1.923C4.978 14.711 6.427 15 8 15c.61 0 1.205-.043 1.77-.124a3.5 3.5 0 0 1-.36-.889 8 8 0 0 1-1.41.013C6.427 14 4.978 13.711 3.904 13.223 2.823 12.74 2 12.073 2 11.3V9.012c.566.385 1.36.69 2.293.887A8 8 0 0 0 8 10c.61 0 1.205-.043 1.77-.124a3.5 3.5 0 0 1-.36-.889 8 8 0 0 1-1.41.013C6.427 9 4.978 8.711 3.904 8.223 2.823 7.74 2 7.073 2 6.3V4.012c.566.385 1.36.69 2.293.887A8 8 0 0 0 8 5c.61 0 1.205-.043 1.77-.124a3.5 3.5 0 0 1-.36-.889 8 8 0 0 1-1.41.013C6.427 4 4.978 3.711 3.904 3.223 2.823 2.74 2 2.073 2 1.3V1.3c0-.773.823-1.44 1.904-1.923C4.978.289 6.427 0 8 0s3.022.289 4.096.777C13.177 1.26 14 1.927 14 2.7v4.618a3.5 3.5 0 0 1 .998.423V3.7c0-.773-.823-1.44-1.904-1.923C11.978 1.289 10.528 1 9 1z"/>
                                  <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 1 0V11a.5.5 0 0 0-.5-.5m0 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                </svg>
                            </div>
                            <h3 class="fw-bold text-dark">Database Connection Failed</h3>
                            <p class="text-muted">Unable to connect to MySQL database <strong>library_db</strong>.</p>
                            <div class="alert alert-light text-start border p-3">
                                <h6><strong>Troubleshooting Steps:</strong></h6>
                                <ol class="mb-0 small">
                                    <li>Ensure <strong>Apache</strong> and <strong>MySQL</strong> are started in the XAMPP Control Panel.</li>
                                    <li>Open <strong>phpMyAdmin</strong> (<a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a>).</li>
                                    <li>Create a database named <code>library_db</code>.</li>
                                    <li>Import the <code>database/library.sql</code> file provided in this project.</li>
                                </ol>
                            </div>
                            <p class="small text-secondary mb-0">Error details: ' . htmlspecialchars($e->getMessage()) . '</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>');
}
