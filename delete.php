<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$article = getArticleById($id);

if (!$article) {
    header('Location: index.php?error=Article not found');
    exit;
}

// Delete associated files first
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

// Delete the article
if (deleteArticle($id)) {
    header('Location: index.php?message=Article deleted successfully');
} else {
    header('Location: view.php?id=' . $id . '&error=Failed to delete article');
}
exit;
?>
