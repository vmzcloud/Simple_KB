<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $tags = sanitizeInput($_POST['tags'] ?? '');
    
    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        try {
            $articleId = createArticle($title, $content, $tags);
            
            // Handle file uploads
            if (!empty($_FILES['files']['name'][0])) {
                foreach ($_FILES['files']['name'] as $key => $filename) {
                    if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['files']['name'][$key],
                            'type' => $_FILES['files']['type'][$key],
                            'tmp_name' => $_FILES['files']['tmp_name'][$key],
                            'size' => $_FILES['files']['size'][$key]
                        ];
                        uploadFile($file, $articleId);
                    }
                }
            }
            
            $message = 'Article created successfully!';
            header('Location: view.php?id=' . $articleId);
            exit;
        } catch (Exception $e) {
            $error = 'Error creating article: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Article - Knowledge Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/monokai.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .editor-container {
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            min-height: 400px;
        }
        .editor-toolbar {
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            padding: 10px;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .editor-content {
            padding: 15px;
            min-height: 350px;
        }
        .editor-content[contenteditable="true"]:focus {
            outline: none;
        }
        .btn-toolbar .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 0.375rem;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .file-upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        .uploaded-files {
            margin-top: 15px;
        }
        .uploaded-file {
            display: inline-block;
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            margin: 2px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-book"></i> Knowledge Base
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home"></i> Home
                </a>
                <a class="nav-link" href="search.php">
                    <i class="fas fa-search"></i> Search
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h1 class="mb-4"><i class="fas fa-plus"></i> Create New Article</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required 
                               value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (comma-separated)</label>
                        <input type="text" class="form-control" id="tags" name="tags" 
                               placeholder="e.g., documentation, tutorial, guide"
                               value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Content *</label>
                        <div class="editor-container">
                            <div class="editor-toolbar">
                                <div class="btn-toolbar" role="toolbar">
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHeading(1)" title="Heading 1">
                                            H1
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHeading(2)" title="Heading 2">
                                            H2
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertHeading(3)" title="Heading 3">
                                            H3
                                        </button>
                                    </div>
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertList('ul')" title="Bullet List">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertList('ol')" title="Numbered List">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                    </div>
                                    <div class="btn-group me-2" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertLink()" title="Insert Link">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertTable()" title="Insert Table">
                                            <i class="fas fa-table"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertCodeBlock()" title="Code Block">
                                            <i class="fas fa-code"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="editor-content" contenteditable="true" id="content-editor">
                                <?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?>
                            </div>
                        </div>
                        <textarea name="content" id="content-hidden" style="display: none;" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File Uploads</label>
                        <div class="file-upload-area" onclick="document.getElementById('file-input').click()">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <p class="mb-0">Click to upload files or drag and drop</p>
                            <small class="text-muted">Supports images, PDFs, documents (max 10MB each)</small>
                        </div>
                        <input type="file" id="file-input" name="files[]" multiple style="display: none" 
                               accept="image/*,.pdf,.txt,.doc,.docx">
                        <div class="uploaded-files" id="uploaded-files"></div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Article
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // WYSIWYG Editor functionality
        const editor = document.getElementById('content-editor');
        const hiddenTextarea = document.getElementById('content-hidden');

        // Update hidden textarea when editor content changes
        editor.addEventListener('input', function() {
            hiddenTextarea.value = editor.innerHTML;
        });

        // Formatting functions
        function formatText(command) {
            document.execCommand(command, false, null);
            editor.focus();
        }

        function insertHeading(level) {
            document.execCommand('formatBlock', false, `<h${level}>`);
            editor.focus();
        }

        function insertList(type) {
            if (type === 'ul') {
                document.execCommand('insertUnorderedList', false, null);
            } else {
                document.execCommand('insertOrderedList', false, null);
            }
            editor.focus();
        }

        function insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                const text = window.getSelection().toString() || 'Link text';
                document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
            }
            editor.focus();
        }

        function insertTable() {
            const rows = prompt('Number of rows:', '3');
            const cols = prompt('Number of columns:', '3');
            if (rows && cols) {
                let table = '<table class="table table-bordered"><tbody>';
                for (let i = 0; i < parseInt(rows); i++) {
                    table += '<tr>';
                    for (let j = 0; j < parseInt(cols); j++) {
                        table += '<td>Cell</td>';
                    }
                    table += '</tr>';
                }
                table += '</tbody></table>';
                document.execCommand('insertHTML', false, table);
            }
            editor.focus();
        }

        function insertCodeBlock() {
            const code = prompt('Enter code:');
            if (code) {
                document.execCommand('insertHTML', false, `<pre><code>${code}</code></pre>`);
            }
            editor.focus();
        }

        // File upload functionality
        const fileInput = document.getElementById('file-input');
        const uploadArea = document.querySelector('.file-upload-area');
        const uploadedFiles = document.getElementById('uploaded-files');

        fileInput.addEventListener('change', handleFiles);

        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            fileInput.files = e.dataTransfer.files;
            handleFiles();
        });

        function handleFiles() {
            uploadedFiles.innerHTML = '';
            for (let file of fileInput.files) {
                const fileDiv = document.createElement('span');
                fileDiv.className = 'uploaded-file';
                fileDiv.innerHTML = `<i class="fas fa-file"></i> ${file.name}`;
                uploadedFiles.appendChild(fileDiv);
            }
        }

        // Initialize hidden textarea with any existing content
        hiddenTextarea.value = editor.innerHTML;
    </script>
</body>
</html>
