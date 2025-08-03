<?php
// Test script to verify database functions
require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Function Test - Simple Knowledge Base</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-pass { color: #28a745; }
        .test-fail { color: #dc3545; }
    </style>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-10'>
                <div class='card shadow'>
                    <div class='card-header bg-primary text-white'>
                        <h4 class='mb-0'>🧪 Function Test Suite</h4>
                    </div>
                    <div class='card-body'>";

$tests = [];
$passedTests = 0;
$totalTests = 0;

function runTest($testName, $callback) {
    global $tests, $passedTests, $totalTests;
    $totalTests++;
    
    try {
        $result = $callback();
        if ($result) {
            $tests[] = ['name' => $testName, 'status' => 'pass', 'message' => 'OK'];
            $passedTests++;
        } else {
            $tests[] = ['name' => $testName, 'status' => 'fail', 'message' => 'Test returned false'];
        }
    } catch (Exception $e) {
        $tests[] = ['name' => $testName, 'status' => 'fail', 'message' => $e->getMessage()];
    }
}

// Test 1: Database initialization
runTest('Database Initialization', function() {
    initializeDatabase();
    return true;
});

// Test 2: Create test article
runTest('Create Article', function() {
    $id = createArticle('Test Article', 'This is test content with **markdown**.', 'test, example, demo');
    return $id > 0;
});

// Test 3: Get all articles
runTest('Get All Articles', function() {
    $articles = getAllArticles();
    return is_array($articles);
});

// Test 4: Get popular tags (the problematic function)
runTest('Get Popular Tags', function() {
    $tags = getPopularTags();
    return is_array($tags);
});

// Test 5: Search articles
runTest('Search Articles', function() {
    $results = searchArticles('test');
    return is_array($results);
});

// Test 6: Search by tag
runTest('Search by Tag', function() {
    $results = searchArticles('', 'test');
    return is_array($results);
});

// Test 7: Get article by ID
runTest('Get Article by ID', function() {
    $articles = getAllArticles(1);
    if (!empty($articles)) {
        $article = getArticleById($articles[0]['id']);
        return $article !== false && isset($article['title']);
    }
    return true; // No articles to test with
});

// Test 8: Markdown formatting
runTest('Markdown Formatting', function() {
    $html = formatMarkdown('**bold** and *italic*');
    return strpos($html, '<strong>') !== false && strpos($html, '<em>') !== false;
});

// Display results
echo "<h5>Test Results</h5>";
echo "<div class='progress mb-3'>";
$percentage = $totalTests > 0 ? ($passedTests / $totalTests) * 100 : 0;
echo "<div class='progress-bar bg-" . ($percentage == 100 ? 'success' : ($percentage > 50 ? 'warning' : 'danger')) . "' role='progressbar' style='width: {$percentage}%'>{$passedTests}/{$totalTests} tests passed</div>";
echo "</div>";

echo "<table class='table table-striped'>";
echo "<thead><tr><th>Test</th><th>Status</th><th>Message</th></tr></thead>";
echo "<tbody>";

foreach ($tests as $test) {
    $statusClass = $test['status'] === 'pass' ? 'test-pass' : 'test-fail';
    $statusIcon = $test['status'] === 'pass' ? '✅' : '❌';
    echo "<tr>";
    echo "<td>{$test['name']}</td>";
    echo "<td class='{$statusClass}'>{$statusIcon} " . ucfirst($test['status']) . "</td>";
    echo "<td>" . htmlspecialchars($test['message']) . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

// Show sample data if tests passed
if ($passedTests === $totalTests) {
    echo "<div class='alert alert-success'>";
    echo "<h6>🎉 All tests passed! Here's some sample data:</h6>";
    
    $articles = getAllArticles(3);
    if (!empty($articles)) {
        echo "<h6>Sample Articles:</h6>";
        echo "<ul>";
        foreach ($articles as $article) {
            echo "<li><strong>" . htmlspecialchars($article['title']) . "</strong> - " . htmlspecialchars(substr($article['content'], 0, 50)) . "...</li>";
        }
        echo "</ul>";
    }
    
    $tags = getPopularTags(5);
    if (!empty($tags)) {
        echo "<h6>Popular Tags:</h6>";
        echo "<ul>";
        foreach ($tags as $tag) {
            echo "<li>" . htmlspecialchars($tag['tag']) . " ({$tag['count']})</li>";
        }
        echo "</ul>";
    }
    
    echo "</div>";
} else {
    echo "<div class='alert alert-danger'>";
    echo "<h6>⚠️ Some tests failed</h6>";
    echo "<p>Please check the error messages above and fix any issues before proceeding.</p>";
    echo "</div>";
}

echo "                </div>
                    <div class='card-footer'>
                        <small class='text-muted'>
                            This test suite verifies that all core functions are working properly.
                        </small>
                    </div>
                </div>
                
                <div class='text-center mt-3'>
                    <a href='diagnostics.php' class='btn btn-info'>System Diagnostics</a>
                    <a href='install.php' class='btn btn-primary'>Installation</a>
                    <a href='index.php' class='btn btn-success'>Knowledge Base</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
