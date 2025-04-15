<?php
include 'config.php';

// Fetch the last 20 scanned student logs
$result = mysqli_query($link, "SELECT * FROM tbllogs WHERE module = 'Barcode Scanner' ORDER BY datelog DESC, timelog DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scanned Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { background: #f4f4f4; margin: 5px 0; padding: 10px; border-radius: 5px; }
        a { display: inline-block; margin-top: 10px; padding: 8px 12px; background: #007BFF; color: white; text-decoration: none; border-radius: 5px; }
        a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h2>Scanned Students Logs</h2>
    <ul>
        <?php while ($log = mysqli_fetch_assoc($result)) {
            echo "<li><strong>Date:</strong> " . htmlspecialchars($log['datelog']) . " <strong>Time:</strong> " . htmlspecialchars($log['timelog']) . " - " . htmlspecialchars($log['action']) . "</li>";
        } ?>
    </ul>
    <a href="security-index.php">Back</a>
</body>
</html>
