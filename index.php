<?php
require_once 'db.php';

// Fetch projects or courses from database
$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Learning Project Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>E-Learning Resource Repository</h1>
        
        <div id="project-list">
            <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <h3><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
