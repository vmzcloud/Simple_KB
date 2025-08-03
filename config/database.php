<?php
class Database {
    private $pdo;
    
    public function __construct() {
        $this->connect();
    }
    
    private function connect() {
        try {
            // Ensure data directory exists and is writable
            $dataDir = __DIR__ . '/../data';
            if (!is_dir($dataDir)) {
                if (!mkdir($dataDir, 0755, true)) {
                    throw new Exception('Cannot create data directory: ' . $dataDir);
                }
            }
            
            // Check if directory is writable
            if (!is_writable($dataDir)) {
                throw new Exception('Data directory is not writable: ' . $dataDir . '. Please set permissions to 755 or 777.');
            }
            
            $dbPath = $dataDir . '/knowledge_base.db';
            
            // If database doesn't exist, create it and set permissions
            $dbExists = file_exists($dbPath);
            
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Set database file permissions if it was just created
            if (!$dbExists && file_exists($dbPath)) {
                chmod($dbPath, 0664);
            }
            
        } catch (PDOException $e) {
            $errorMsg = 'Database connection failed: ' . $e->getMessage();
            $errorMsg .= '<br><br><strong>Troubleshooting:</strong><br>';
            $errorMsg .= '1. Make sure the "data" directory exists and is writable<br>';
            $errorMsg .= '2. Set directory permissions: <code>chmod 755 ' . __DIR__ . '/../data</code><br>';
            $errorMsg .= '3. Or try: <code>chmod 777 ' . __DIR__ . '/../data</code><br>';
            $errorMsg .= '4. Check that your web server can write to this location<br>';
            die($errorMsg);
        } catch (Exception $e) {
            die('Database setup failed: ' . $e->getMessage());
        }
    }
    
    public function getPdo() {
        return $this->pdo;
    }
}

function getDatabase() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db->getPdo();
}

function initializeDatabase() {
    $pdo = getDatabase();
    
    // Create articles table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS articles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            tags TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create files table for uploaded files
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS files (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            filename TEXT NOT NULL,
            original_name TEXT NOT NULL,
            file_path TEXT NOT NULL,
            file_type TEXT NOT NULL,
            file_size INTEGER NOT NULL,
            article_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (article_id) REFERENCES articles (id)
        )
    ");
    
    // Create search index for full-text search
    $pdo->exec("
        CREATE VIRTUAL TABLE IF NOT EXISTS articles_fts USING fts5(
            title, content, tags, content='articles', content_rowid='id'
        )
    ");
    
    // Create triggers to maintain the FTS index
    $pdo->exec("
        CREATE TRIGGER IF NOT EXISTS articles_ai AFTER INSERT ON articles BEGIN
            INSERT INTO articles_fts(rowid, title, content, tags) VALUES (new.id, new.title, new.content, new.tags);
        END
    ");
    
    $pdo->exec("
        CREATE TRIGGER IF NOT EXISTS articles_ad AFTER DELETE ON articles BEGIN
            INSERT INTO articles_fts(articles_fts, rowid, title, content, tags) VALUES('delete', old.id, old.title, old.content, old.tags);
        END
    ");
    
    $pdo->exec("
        CREATE TRIGGER IF NOT EXISTS articles_au AFTER UPDATE ON articles BEGIN
            INSERT INTO articles_fts(articles_fts, rowid, title, content, tags) VALUES('delete', old.id, old.title, old.content, old.tags);
            INSERT INTO articles_fts(rowid, title, content, tags) VALUES (new.id, new.title, new.content, new.tags);
        END
    ");
    
    // Ensure data directory exists and is writable
    $dataDir = __DIR__ . '/../data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('Cannot create data directory: ' . $dataDir);
        }
    }
    
    // Ensure uploads directory exists and is writable
    $uploadsDir = __DIR__ . '/../uploads';
    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true)) {
            throw new Exception('Cannot create uploads directory: ' . $uploadsDir);
        }
    }
    
    // Check directory permissions
    if (!is_writable($dataDir)) {
        throw new Exception('Data directory is not writable: ' . $dataDir . '. Please run: chmod 755 ' . $dataDir);
    }
    
    if (!is_writable($uploadsDir)) {
        throw new Exception('Uploads directory is not writable: ' . $uploadsDir . '. Please run: chmod 755 ' . $uploadsDir);
    }
}
?>
