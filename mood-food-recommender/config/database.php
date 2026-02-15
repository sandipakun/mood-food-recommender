<?php
/**
 * Database Configuration
 * Update these values for your environment
 */

define('DB_HOST', 'localhost');
define('DB_PORT', 3307); // Update if MySQL listens on a different port
define('DB_NAME', 'mood_food_recommender');
define('DB_USER', 'root'); // Change in production
define('DB_PASS', ''); // Change in production
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }
    }
    
    return $pdo;
}

