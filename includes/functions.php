<?php
require_once 'config/database.php';

// Article functions
function getAllArticles($limit = 50) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM articles ORDER BY updated_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getArticleById($id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function createArticle($title, $content, $tags = '') {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, tags) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $tags]);
    return $pdo->lastInsertId();
}

function updateArticle($id, $title, $content, $tags = '') {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, tags = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$title, $content, $tags, $id]);
}

function deleteArticle($id) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    return $stmt->execute([$id]);
}

// Search functions
function searchArticles($query, $tag = null) {
    $pdo = getDatabase();
    
    try {
        if ($tag) {
            $stmt = $pdo->prepare("SELECT * FROM articles WHERE tags LIKE ? ORDER BY updated_at DESC");
            $stmt->execute(['%' . $tag . '%']);
        } else if (!empty($query)) {
            // Try FTS search first, fall back to LIKE search if FTS fails
            try {
                $stmt = $pdo->prepare("
                    SELECT articles.* FROM articles 
                    JOIN articles_fts ON articles.id = articles_fts.rowid 
                    WHERE articles_fts MATCH ? 
                    ORDER BY bm25(articles_fts)
                ");
                $stmt->execute([$query]);
            } catch (PDOException $e) {
                // Fallback to simple LIKE search if FTS fails
                $stmt = $pdo->prepare("
                    SELECT * FROM articles 
                    WHERE title LIKE ? OR content LIKE ? 
                    ORDER BY updated_at DESC
                ");
                $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
            }
        } else {
            // Return empty array if no search criteria
            return [];
        }
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Return empty array on any database error
        error_log("Search error: " . $e->getMessage());
        return [];
    }
}

function getPopularTags($limit = 20) {
    $pdo = getDatabase();
    
    // Get all articles with tags
    $stmt = $pdo->query("SELECT tags FROM articles WHERE tags IS NOT NULL AND tags != ''");
    $articles = $stmt->fetchAll();
    
    $tagCounts = [];
    
    // Parse tags manually in PHP (more reliable than complex SQL)
    foreach ($articles as $article) {
        if (!empty($article['tags'])) {
            $tags = explode(',', $article['tags']);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag)) {
                    $tagLower = strtolower($tag);
                    if (isset($tagCounts[$tagLower])) {
                        $tagCounts[$tagLower]['count']++;
                    } else {
                        $tagCounts[$tagLower] = [
                            'tag' => $tag,
                            'count' => 1
                        ];
                    }
                }
            }
        }
    }
    
    // Sort by count (descending) and then by tag name
    uasort($tagCounts, function($a, $b) {
        if ($a['count'] == $b['count']) {
            return strcmp($a['tag'], $b['tag']);
        }
        return $b['count'] - $a['count'];
    });
    
    // Return limited results
    return array_slice(array_values($tagCounts), 0, $limit);
}

// File functions
function uploadFile($file, $articleId = null) {
    $uploadDir = __DIR__ . '/../uploads/';
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('File type not allowed');
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        throw new Exception('File size too large');
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $uploadDir . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to upload file');
    }
    
    // Save file info to database
    $pdo = getDatabase();
    $stmt = $pdo->prepare("
        INSERT INTO files (filename, original_name, file_path, file_type, file_size, article_id) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $filename,
        $file['name'],
        $filepath,
        $file['type'],
        $file['size'],
        $articleId
    ]);
    
    return [
        'id' => $pdo->lastInsertId(),
        'filename' => $filename,
        'original_name' => $file['name'],
        'url' => 'uploads/' . $filename
    ];
}

function getFilesByArticle($articleId) {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM files WHERE article_id = ? ORDER BY created_at DESC");
    $stmt->execute([$articleId]);
    return $stmt->fetchAll();
}

// Utility functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatMarkdown($text) {
    // Simple markdown parser
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank">$1</a>', $text);
    
    // Code blocks
    $text = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $text);
    
    // Headers
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    
    // Line breaks
    $text = nl2br($text);
    
    return $text;
}

function generateSlug($text) {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
?>
