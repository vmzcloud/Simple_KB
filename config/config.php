<?php
// Configuration file for Simple Knowledge Base

// Database configuration
define('DB_PATH', __DIR__ . '/../data/knowledge_base.db');

// Upload configuration
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp',
    'application/pdf',
    'text/plain',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
]);

// Application settings
define('APP_NAME', 'Simple Knowledge Base');
define('APP_VERSION', '1.0.0');
define('ITEMS_PER_PAGE', 20);
define('SEARCH_RESULTS_LIMIT', 50);

// Security settings
define('ENABLE_FILE_UPLOADS', true);
define('SANITIZE_HTML', true);

// UI Settings
define('THEME', 'default');
define('ENABLE_DARK_MODE', true);
define('SHOW_BREADCRUMBS', true);

// Feature flags
define('ENABLE_TAGS', true);
define('ENABLE_SEARCH', true);
define('ENABLE_MARKDOWN', true);
define('ENABLE_CODE_HIGHLIGHTING', true);
define('ENABLE_TABLE_EDITOR', true);

// Time zone
date_default_timezone_set('UTC');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
