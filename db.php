<?php
/**
 * E-Learning Management System - Database Connection
 * 
 * Establishes connection to MySQL database with proper error handling
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Database connection singleton
 * 
 * @var PDO|null
 */
static $pdo = null;

/**
 * Get or create database connection
 * 
 * @throws RuntimeException If connection fails
 * @return PDO Database connection instance
 */
function getDatabase(): PDO
{
    global $pdo;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    try {
        $db = config('DATABASE');
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_PERSISTENT => false,
        ];
        
        $pdo = new PDO($dsn, $db['user'], $db['password'], $options);
        
        // Set timezone
        $pdo->query("SET time_zone = '" . $pdo->quote($db['timezone']) . "'");
        
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        
        http_response_code(500);
        
        if (isDebug()) {
            throw new RuntimeException(
                'Database Connection Error: ' . $e->getMessage() .
                '\n\nEnsure MySQL is running and credentials in config.php are correct.'
            );
        } else {
            throw new RuntimeException(
                'A database error occurred. Please try again later.'
            );
        }
    }
}

// Establish initial connection for backward compatibility
$pdo = getDatabase();
