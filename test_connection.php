<?php
require_once 'db.php';

// Test 1: Check connection
try {
    $result = $pdo->query("SELECT 1");
    echo "✓ Database connection successful\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: List all tables
echo "\nTables in database:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "  - $table\n";
}

// Test 3: Check projects table structure
if (in_array('projects', $tables)) {
    echo "\nProjects table structure:\n";
    $columns = $pdo->query("DESCRIBE projects")->fetchAll();
    foreach ($columns as $col) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}

// Test 4: Test your current query
echo "\nTesting: SELECT * FROM projects\n";
try {
    $stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC LIMIT 5");
    $projects = $stmt->fetchAll();
    echo "✓ Query successful. Found " . count($projects) . " projects\n";
    
    if (count($projects) > 0) {
        echo "\nFirst project:\n";
        print_r($projects[0]);
    }
} catch (PDOException $e) {
    echo "✗ Query failed: " . $e->getMessage() . "\n";
}
?>
