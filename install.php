<?php
// Simple installation script for Knowledge Base
require_once 'config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Knowledge Base - Installation</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-primary text-white'>
                        <h4 class='mb-0'><i class='fas fa-cog'></i> Knowledge Base Installation</h4>
                    </div>
                    <div class='card-body'>";

try {
    echo "<div class='alert alert-info'>
            <h5><i class='fas fa-info-circle'></i> Starting Installation...</h5>
          </div>";
    
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4.0') < 0) {
        throw new Exception('PHP 7.4 or higher is required. Current version: ' . PHP_VERSION);
    }
    echo "<div class='alert alert-success'>✓ PHP version check passed (" . PHP_VERSION . ")</div>";
    
    // Check SQLite extension
    if (!extension_loaded('sqlite3')) {
        throw new Exception('SQLite3 extension is not loaded');
    }
    echo "<div class='alert alert-success'>✓ SQLite3 extension is available</div>";
    
    // Check directory permissions
    $dataDir = __DIR__ . '/data';
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0755, true)) {
            throw new Exception('Cannot create data directory');
        }
    }
    if (!is_writable($dataDir)) {
        throw new Exception('Data directory is not writable');
    }
    echo "<div class='alert alert-success'>✓ Data directory is writable</div>";
    
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        if (!mkdir($uploadsDir, 0755, true)) {
            throw new Exception('Cannot create uploads directory');
        }
    }
    if (!is_writable($uploadsDir)) {
        throw new Exception('Uploads directory is not writable');
    }
    echo "<div class='alert alert-success'>✓ Uploads directory is writable</div>";
    
    // Initialize database
    echo "<div class='alert alert-info'>Initializing database...</div>";
    initializeDatabase();
    echo "<div class='alert alert-success'>✓ Database initialized successfully</div>";
    
    // Create sample content if requested
    if (isset($_GET['sample']) && $_GET['sample'] === '1') {
        require_once 'includes/functions.php';
        
        echo "<div class='alert alert-info'>Creating sample content...</div>";
        
        // Sample article 1
        $sampleContent1 = "# Welcome to Your Knowledge Base

This is your first article! This knowledge base system provides you with powerful tools to create, organize, and share information.

## Features

- **WYSIWYG Editor**: Rich text editing with formatting options
- **File Uploads**: Support for images, documents, and more
- **Search**: Full-text search across all articles
- **Tags**: Organize your content with tags
- **Markdown**: Full markdown support for technical documentation

## Getting Started

1. Click **New Article** to create your first article
2. Use the toolbar to format your content
3. Add tags to organize your articles
4. Upload files to enhance your content

## Code Example

```php
<?php
echo 'Hello, Knowledge Base!';
?>
```

## Tables

| Feature | Status |
|---------|--------|
| Editor  | ✅ Ready |
| Search  | ✅ Ready |
| Upload  | ✅ Ready |

Happy writing!";

        createArticle('Welcome to Your Knowledge Base', $sampleContent1, 'welcome, getting-started, tutorial');
        
        // Sample article 2
        $sampleContent2 = "# Markdown Syntax Guide

This article demonstrates the markdown syntax supported by the knowledge base.

## Text Formatting

**Bold text** and *italic text* are supported.

You can also use `inline code` for technical terms.

## Lists

### Bullet Lists
- Item 1
- Item 2
  - Nested item
  - Another nested item

### Numbered Lists
1. First item
2. Second item
3. Third item

## Links

You can create [links to external sites](https://example.com) or internal references.

## Code Blocks

```javascript
function greetUser(name) {
    console.log(`Hello, ${name}!`);
    return `Welcome to the knowledge base, ${name}`;
}

greetUser('Developer');
```

## Blockquotes

> This is a blockquote. Use it for important notes or citations.

## Tables

| Syntax | Description |
|--------|-------------|
| **Bold** | Bold text |
| *Italic* | Italic text |
| `Code` | Inline code |
| [Link](url) | Hyperlink |

This guide should help you format your articles effectively!";

        createArticle('Markdown Syntax Guide', $sampleContent2, 'markdown, syntax, formatting, guide');
        
        echo "<div class='alert alert-success'>✓ Sample content created</div>";
    }
    
    echo "<div class='alert alert-success'>
            <h5><i class='fas fa-check-circle'></i> Installation Complete!</h5>
            <p class='mb-0'>Your knowledge base is ready to use.</p>
          </div>";
    
    echo "<div class='d-grid gap-2 mt-4'>
            <a href='index.php' class='btn btn-primary btn-lg'>
                <i class='fas fa-arrow-right'></i> Go to Knowledge Base
            </a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <h5><i class='fas fa-exclamation-triangle'></i> Installation Failed</h5>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <p class='mb-0'>Please fix the error above and refresh this page.</p>
          </div>";
}

echo "           </div>
                </div>
            </div>
        </div>
        
        <div class='text-center mt-4'>
            <p class='text-muted'>
                <small>
                    Want sample content? <a href='install.php?sample=1'>Click here to install with sample articles</a>
                </small>
            </p>
        </div>
    </div>
</body>
</html>";
?>
