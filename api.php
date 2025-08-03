<?php
// Simple API for the Knowledge Base
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once 'config/database.php';
require_once 'includes/functions.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Remove api.php from path if present
if (end($pathParts) === 'api.php') {
    array_pop($pathParts);
}

$resource = $pathParts[count($pathParts) - 1] ?? '';
$id = $pathParts[count($pathParts)] ?? null;

// Initialize database
initializeDatabase();

try {
    switch ($method) {
        case 'GET':
            if ($resource === 'articles') {
                if ($id) {
                    // Get single article
                    $article = getArticleById($id);
                    if ($article) {
                        $files = getFilesByArticle($id);
                        $article['files'] = $files;
                        echo json_encode(['success' => true, 'data' => $article]);
                    } else {
                        http_response_code(404);
                        echo json_encode(['success' => false, 'error' => 'Article not found']);
                    }
                } else {
                    // Get all articles
                    $limit = $_GET['limit'] ?? 50;
                    $articles = getAllArticles($limit);
                    echo json_encode(['success' => true, 'data' => $articles]);
                }
            } elseif ($resource === 'search') {
                $query = $_GET['q'] ?? '';
                $tag = $_GET['tag'] ?? null;
                if ($query || $tag) {
                    $results = searchArticles($query, $tag);
                    echo json_encode(['success' => true, 'data' => $results]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Query parameter required']);
                }
            } elseif ($resource === 'tags') {
                $tags = getPopularTags();
                echo json_encode(['success' => true, 'data' => $tags]);
            } else {
                // API info
                echo json_encode([
                    'success' => true,
                    'message' => 'Knowledge Base API',
                    'version' => '1.0.0',
                    'endpoints' => [
                        'GET /api.php/articles' => 'Get all articles',
                        'GET /api.php/articles/{id}' => 'Get article by ID',
                        'POST /api.php/articles' => 'Create new article',
                        'PUT /api.php/articles/{id}' => 'Update article',
                        'DELETE /api.php/articles/{id}' => 'Delete article',
                        'GET /api.php/search?q={query}' => 'Search articles',
                        'GET /api.php/search?tag={tag}' => 'Search by tag',
                        'GET /api.php/tags' => 'Get popular tags'
                    ]
                ]);
            }
            break;
            
        case 'POST':
            if ($resource === 'articles') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['title']) || !isset($input['content'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Title and content are required']);
                    break;
                }
                
                $title = sanitizeInput($input['title']);
                $content = $input['content'];
                $tags = sanitizeInput($input['tags'] ?? '');
                
                $articleId = createArticle($title, $content, $tags);
                $article = getArticleById($articleId);
                
                http_response_code(201);
                echo json_encode(['success' => true, 'data' => $article]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if ($resource === 'articles' && $id) {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!$input || !isset($input['title']) || !isset($input['content'])) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Title and content are required']);
                    break;
                }
                
                $article = getArticleById($id);
                if (!$article) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Article not found']);
                    break;
                }
                
                $title = sanitizeInput($input['title']);
                $content = $input['content'];
                $tags = sanitizeInput($input['tags'] ?? '');
                
                updateArticle($id, $title, $content, $tags);
                $updatedArticle = getArticleById($id);
                
                echo json_encode(['success' => true, 'data' => $updatedArticle]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            }
            break;
            
        case 'DELETE':
            if ($resource === 'articles' && $id) {
                $article = getArticleById($id);
                if (!$article) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Article not found']);
                    break;
                }
                
                // Delete associated files
                $files = getFilesByArticle($id);
                foreach ($files as $file) {
                    if (file_exists($file['file_path'])) {
                        unlink($file['file_path']);
                    }
                }
                
                // Delete file records
                $pdo = getDatabase();
                $stmt = $pdo->prepare("DELETE FROM files WHERE article_id = ?");
                $stmt->execute([$id]);
                
                // Delete article
                deleteArticle($id);
                
                echo json_encode(['success' => true, 'message' => 'Article deleted successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal server error: ' . $e->getMessage()]);
}
?>
