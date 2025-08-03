<?php
require_once 'config/database.php';

$fileId = intval($_GET['id'] ?? 0);
$articleId = intval($_GET['article_id'] ?? 0);

if ($fileId <= 0 || $articleId <= 0) {
    header('Location: index.php');
    exit;
}

$pdo = getDatabase();

// Get file info
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND article_id = ?");
$stmt->execute([$fileId, $articleId]);
$file = $stmt->fetch();

if (!$file) {
    header('Location: edit.php?id=' . $articleId . '&error=File not found');
    exit;
}

// Delete physical file
if (file_exists($file['file_path'])) {
    unlink($file['file_path']);
}

// Delete file record
$stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
if ($stmt->execute([$fileId])) {
    header('Location: edit.php?id=' . $articleId . '&message=File deleted successfully');
} else {
    header('Location: edit.php?id=' . $articleId . '&error=Failed to delete file');
}
exit;
?>
