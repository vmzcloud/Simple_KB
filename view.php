<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
$article = getArticleById($id);

if (!$article) {
    header('Location: index.php');
    exit;
}

$files = getFilesByArticle($id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Knowledge Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .article-content {
            line-height: 1.8;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.375rem;
            margin: 10px 0;
        }
        .article-content table {
            width: 100%;
            margin: 15px 0;
        }
        .article-content pre {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 15px;
            overflow-x: auto;
        }
        .article-content blockquote {
            border-left: 4px solid #007bff;
            padding-left: 15px;
            margin: 15px 0;
            color: #6c757d;
        }
        .file-attachment {
            border: 1px solid #ddd;
            border-radius: 0.375rem;
            padding: 15px;
            margin: 5px 0;
            background: #f8f9fa;
        }
        .file-attachment:hover {
            background: #e9ecef;
        }
        .tag-link {
            text-decoration: none;
        }
        .tag-link:hover {
            text-decoration: underline;
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
                <a class="nav-link" href="create.php">
                    <i class="fas fa-plus"></i> New Article
                </a>
                <a class="nav-link" href="search.php">
                    <i class="fas fa-search"></i> Search
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <article>
                    <header class="mb-4">
                        <h1 class="display-4"><?php echo htmlspecialchars($article['title']); ?></h1>
                        
                        <div class="mb-3">
                            <?php if ($article['tags']): ?>
                                <?php foreach (explode(',', $article['tags']) as $tag): ?>
                                    <a href="search.php?tag=<?php echo urlencode(trim($tag)); ?>" 
                                       class="badge bg-primary me-1 tag-link">
                                        <?php echo htmlspecialchars(trim($tag)); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-muted mb-4">
                            <small>
                                <i class="fas fa-calendar"></i> 
                                Created: <?php echo date('M j, Y \a\t g:i A', strtotime($article['created_at'])); ?>
                                <?php if ($article['updated_at'] !== $article['created_at']): ?>
                                    <br>
                                    <i class="fas fa-edit"></i> 
                                    Updated: <?php echo date('M j, Y \a\t g:i A', strtotime($article['updated_at'])); ?>
                                <?php endif; ?>
                            </small>
                        </div>
                    </header>

                    <div class="article-content">
                        <?php echo formatMarkdown($article['content']); ?>
                    </div>

                    <?php if (!empty($files)): ?>
                        <div class="mt-5">
                            <h4><i class="fas fa-paperclip"></i> Attachments</h4>
                            <?php foreach ($files as $file): ?>
                                <div class="file-attachment">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                                                <i class="fas fa-image fa-2x text-primary"></i>
                                            <?php elseif ($file['file_type'] === 'application/pdf'): ?>
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            <?php else: ?>
                                                <i class="fas fa-file fa-2x text-secondary"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <?php if ($file['file_type'] === 'application/pdf'): ?>
                                                    <a href="#" class="text-decoration-none" onclick="showPdfPreview('<?php echo 'uploads/' . htmlspecialchars($file['filename']); ?>', '<?php echo htmlspecialchars($file['original_name']); ?>'); return false;">
                                                        <?php echo htmlspecialchars($file['original_name']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="<?php echo 'uploads/' . htmlspecialchars($file['filename']); ?>" 
                                                       target="_blank" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($file['original_name']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo number_format($file['file_size'] / 1024, 1); ?> KB • 
                                                <?php echo date('M j, Y', strtotime($file['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="<?php echo 'uploads/' . htmlspecialchars($file['filename']); ?>" 
                                               class="btn btn-sm btn-outline-primary" download>
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <?php if (strpos($file['file_type'], 'image/') === 0): ?>
                                        <div class="mt-3">
                                            <img src="<?php echo 'uploads/' . htmlspecialchars($file['filename']); ?>" 
                                                 alt="<?php echo htmlspecialchars($file['original_name']); ?>"
                                                 class="img-fluid rounded">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($file['file_type'] === 'application/pdf'): ?>
                                        <div class="mt-3" id="pdf-preview-<?php echo $file['id']; ?>" style="display:none;"></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>

                <div class="mt-5 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="edit.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Article
                            </a>
                            <button class="btn btn-outline-danger" onclick="deleteArticle(<?php echo $article['id']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        <div>
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print
                            </button>
                            <button class="btn btn-outline-secondary" onclick="shareArticle()">
                                <i class="fas fa-share"></i> Share
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-list"></i> Table of Contents</h6>
                    </div>
                    <div class="card-body" id="table-of-contents">
                        <!-- TOC will be generated by JavaScript -->
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-search"></i> Quick Search</h6>
                    </div>
                    <div class="card-body">
                        <form action="search.php" method="GET">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Search...">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tags"></i> Related Tags</h6>
                    </div>
                    <div class="card-body">
                        <?php if ($article['tags']): ?>
                            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                                <a href="search.php?tag=<?php echo urlencode(trim($tag)); ?>" 
                                   class="badge bg-light text-dark me-1 mb-1 text-decoration-none">
                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">No tags assigned</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
        // Generate table of contents
        function generateTOC() {
            const headings = document.querySelectorAll('.article-content h1, .article-content h2, .article-content h3, .article-content h4, .article-content h5, .article-content h6');
            const toc = document.getElementById('table-of-contents');
            
            if (headings.length === 0) {
                toc.innerHTML = '<p class="text-muted mb-0">No headings found</p>';
                return;
            }
            
            let tocHTML = '<ul class="list-unstyled">';
            headings.forEach((heading, index) => {
                const id = 'heading-' + index;
                heading.id = id;
                const level = parseInt(heading.tagName.charAt(1));
                const indent = level > 1 ? 'ms-' + ((level - 1) * 2) : '';
                tocHTML += `<li class="${indent}"><a href="#${id}" class="text-decoration-none">${heading.textContent}</a></li>`;
            });
            tocHTML += '</ul>';
            toc.innerHTML = tocHTML;
        }

        // Delete article confirmation
        function deleteArticle(id) {
            if (confirm('Are you sure you want to delete this article? This action cannot be undone.')) {
                window.location.href = 'delete.php?id=' + id;
            }
        }

        // Share article
        function shareArticle() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } else {
                // Fallback: copy URL to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Article URL copied to clipboard!');
                });
            }
        }

        // PDF preview functions
        function showPdfPreview(pdfUrl, pdfName) {
            // Remove any existing preview modals
            let oldModal = document.getElementById('pdfPreviewModal');
            if (oldModal) oldModal.remove();
            
            // Create modal using Bootstrap's modal structure
            let modal = document.createElement('div');
            modal.id = 'pdfPreviewModal';
            modal.className = 'modal fade';
            modal.tabIndex = -1;
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class='fas fa-file-pdf text-danger'></i> Preview: ${pdfName}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="height:80vh;">
                            <iframe src="${pdfUrl}" style="width:100%;height:100%;border:none;" allowfullscreen></iframe>
                        </div>
                        <div class="modal-footer">
                            <a href="${pdfUrl}" class="btn btn-primary" target="_blank" download><i class="fas fa-download"></i> Download PDF</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            let bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            // Remove modal from DOM when closed
            modal.addEventListener('hidden.bs.modal', function() {
                modal.remove();
            });
        }

        // Initialize TOC when page loads
        document.addEventListener('DOMContentLoaded', generateTOC);
    </script>
</body>
</html>
