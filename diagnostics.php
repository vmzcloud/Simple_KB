<?php
// Database diagnostics script
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Diagnostics - Simple Knowledge Base</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .status-good { color: #28a745; }
        .status-bad { color: #dc3545; }
        .status-warning { color: #ffc107; }
    </style>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-10'>
                <div class='card shadow'>
                    <div class='card-header bg-info text-white'>
                        <h4 class='mb-0'>🔍 Database Diagnostics</h4>
                    </div>
                    <div class='card-body'>";

// Check PHP version
echo "<h5>PHP Environment</h5>";
echo "<table class='table table-sm'>";
echo "<tr><td><strong>PHP Version:</strong></td><td>" . PHP_VERSION . "</td></tr>";

// Check SQLite
if (extension_loaded('sqlite3')) {
    echo "<tr><td><strong>SQLite3 Extension:</strong></td><td class='status-good'>✓ Available</td></tr>";
    if (class_exists('SQLite3')) {
        $version = SQLite3::version();
        echo "<tr><td><strong>SQLite Version:</strong></td><td>" . $version['versionString'] . "</td></tr>";
    }
} else {
    echo "<tr><td><strong>SQLite3 Extension:</strong></td><td class='status-bad'>✗ Not Available</td></tr>";
}

// Check PDO SQLite
if (extension_loaded('pdo_sqlite')) {
    echo "<tr><td><strong>PDO SQLite:</strong></td><td class='status-good'>✓ Available</td></tr>";
} else {
    echo "<tr><td><strong>PDO SQLite:</strong></td><td class='status-bad'>✗ Not Available</td></tr>";
}

echo "</table>";

// Check directories
echo "<h5>Directory Status</h5>";
echo "<table class='table table-sm'>";

$baseDir = __DIR__;
$dataDir = $baseDir . '/data';
$uploadsDir = $baseDir . '/uploads';

echo "<tr><td><strong>Base Directory:</strong></td><td>$baseDir</td></tr>";

// Check if directories exist and are writable
$dirs = [
    'Data Directory' => $dataDir,
    'Uploads Directory' => $uploadsDir
];

foreach ($dirs as $name => $dir) {
    echo "<tr><td><strong>$name:</strong></td><td>$dir</td></tr>";
    
    if (is_dir($dir)) {
        echo "<tr><td>&nbsp;&nbsp;Exists:</td><td class='status-good'>✓ Yes</td></tr>";
        
        if (is_readable($dir)) {
            echo "<tr><td>&nbsp;&nbsp;Readable:</td><td class='status-good'>✓ Yes</td></tr>";
        } else {
            echo "<tr><td>&nbsp;&nbsp;Readable:</td><td class='status-bad'>✗ No</td></tr>";
        }
        
        if (is_writable($dir)) {
            echo "<tr><td>&nbsp;&nbsp;Writable:</td><td class='status-good'>✓ Yes</td></tr>";
        } else {
            echo "<tr><td>&nbsp;&nbsp;Writable:</td><td class='status-bad'>✗ No</td></tr>";
        }
        
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<tr><td>&nbsp;&nbsp;Permissions:</td><td>$perms</td></tr>";
        
    } else {
        echo "<tr><td>&nbsp;&nbsp;Exists:</td><td class='status-bad'>✗ No</td></tr>";
        
        // Try to create it
        if (mkdir($dir, 0755, true)) {
            echo "<tr><td>&nbsp;&nbsp;Created:</td><td class='status-good'>✓ Successfully created</td></tr>";
        } else {
            echo "<tr><td>&nbsp;&nbsp;Created:</td><td class='status-bad'>✗ Failed to create</td></tr>";
        }
    }
}

echo "</table>";

// Test database connection
echo "<h5>Database Connection Test</h5>";
echo "<div class='alert alert-info'>";

try {
    // Try to create database connection
    $dbPath = $dataDir . '/knowledge_base.db';
    echo "<p><strong>Database Path:</strong> $dbPath</p>";
    
    if (file_exists($dbPath)) {
        echo "<p class='status-good'>✓ Database file exists</p>";
        $perms = substr(sprintf('%o', fileperms($dbPath)), -4);
        echo "<p><strong>Database Permissions:</strong> $perms</p>";
    } else {
        echo "<p class='status-warning'>⚠ Database file doesn't exist yet (will be created)</p>";
    }
    
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p class='status-good'>✓ Database connection successful!</p>";
    
    // Test basic operations
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_table (id INTEGER PRIMARY KEY, test_data TEXT)");
    $pdo->exec("INSERT OR REPLACE INTO test_table (id, test_data) VALUES (1, 'test')");
    $stmt = $pdo->query("SELECT * FROM test_table WHERE id = 1");
    $result = $stmt->fetch();
    
    if ($result && $result['test_data'] === 'test') {
        echo "<p class='status-good'>✓ Database read/write test successful!</p>";
    } else {
        echo "<p class='status-bad'>✗ Database read/write test failed</p>";
    }
    
    // Clean up test table
    $pdo->exec("DROP TABLE IF EXISTS test_table");
    
} catch (Exception $e) {
    echo "<p class='status-bad'>✗ Database connection failed:</p>";
    echo "<pre class='bg-light p-2'>" . htmlspecialchars($e->getMessage()) . "</pre>";
}

echo "</div>";

// Suggestions
echo "<h5>Troubleshooting Suggestions</h5>";
echo "<div class='alert alert-warning'>";
echo "<ol>";
echo "<li><strong>Create directories manually:</strong><br>";
echo "<code>mkdir -p " . htmlspecialchars($dataDir) . "</code><br>";
echo "<code>mkdir -p " . htmlspecialchars($uploadsDir) . "</code></li>";

echo "<li><strong>Set proper permissions:</strong><br>";
echo "<code>chmod 755 " . htmlspecialchars($dataDir) . "</code><br>";
echo "<code>chmod 755 " . htmlspecialchars($uploadsDir) . "</code><br>";
echo "<small>If that doesn't work, try: <code>chmod 777</code> (less secure but might be needed)</small></li>";

echo "<li><strong>Check web server user:</strong><br>";
echo "Make sure the web server user (www-data, apache, nginx) owns or can write to these directories:<br>";
echo "<code>chown -R www-data:www-data " . htmlspecialchars($baseDir) . "</code></li>";

echo "<li><strong>SELinux (if applicable):</strong><br>";
echo "On systems with SELinux, you might need:<br>";
echo "<code>setsebool -P httpd_can_network_connect 1</code><br>";
echo "<code>setsebool -P httpd_unified 1</code></li>";

echo "</ol>";
echo "</div>";

echo "                </div>
                    <div class='card-footer'>
                        <small class='text-muted'>
                            Run this diagnostic script whenever you encounter database issues. 
                            Once everything shows green checkmarks, your knowledge base should work properly.
                        </small>
                    </div>
                </div>
                
                <div class='text-center mt-3'>
                    <a href='install.php' class='btn btn-primary'>Go to Installation</a>
                    <a href='index.php' class='btn btn-secondary'>Go to Knowledge Base</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
