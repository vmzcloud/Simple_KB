<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$query = sanitizeInput($_GET['q'] ?? '');
$tag = sanitizeInput($_GET['tag'] ?? '');
$results = [];

if ($query || $tag) {
    $results = searchArticles($query, $tag);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Knowledge Base</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1 class="mb-4"><i class="fas fa-search"></i> Search Knowledge Base</h1>
                
                <form method="GET" class="mb-4">
                    <div class="input-group input-group-lg">
                        <input type="text" name="q" class="form-control" placeholder="Search articles..." 
                               value="<?php echo htmlspecialchars($query); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>

                <?php if ($query || $tag): ?>
                    <div class="search-results">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>
                                Search Results 
                                <?php if ($tag): ?>
                                    for tag: <span class="badge bg-primary"><?php echo htmlspecialchars($tag); ?></span>
                                <?php elseif ($query): ?>
                                    for: "<?php echo htmlspecialchars($query); ?>"
                                <?php endif; ?>
                            </h4>
                            <small class="text-muted"><?php echo count($results); ?> result(s) found</small>
                        </div>

                        <?php if (empty($results)): ?>
                            <div class="alert alert-info">
                                <h5>No results found</h5>
                                <p class="mb-0">Try adjusting your search terms or <a href="create.php">create a new article</a>.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($results as $article): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a href="view.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="card-text">
                                            <?php 
                                            $excerpt = strip_tags(formatMarkdown($article['content']));
                                            echo htmlspecialchars(substr($excerpt, 0, 300)); 
                                            if (strlen($excerpt) > 300) echo '...';
                                            ?>
                                        </p>
                                        <div class="mb-2">
                                            <?php if ($article['tags']): ?>
                                                <?php foreach (explode(',', $article['tags']) as $articleTag): ?>
                                                    <a href="search.php?tag=<?php echo urlencode(trim($articleTag)); ?>" 
                                                       class="badge bg-secondary me-1 text-decoration-none">
                                                        <?php echo htmlspecialchars(trim($articleTag)); ?>
                                                    </a>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($article['created_at'])); ?>
                                            <i class="fas fa-clock ms-2"></i> <?php echo date('g:i A', strtotime($article['updated_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="search-help">
                        <h4>Search Tips</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-lightbulb"></i> Search Tips</h6>
                                        <ul class="list-unstyled">
                                            <li><strong>Keywords:</strong> Try specific terms related to your topic</li>
                                            <li><strong>Phrases:</strong> Use quotes for exact phrases</li>
                                            <li><strong>Tags:</strong> Click on tags to find related articles</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-tags"></i> Browse by Tags</h6>
                                        <?php 
                                        $popularTags = getPopularTags(10);
                                        foreach ($popularTags as $tagInfo): 
                                        ?>
                                            <a href="search.php?tag=<?php echo urlencode($tagInfo['tag']); ?>" 
                                               class="badge bg-light text-dark me-1 mb-1 text-decoration-none">
                                                <?php echo htmlspecialchars($tagInfo['tag']); ?> (<?php echo $tagInfo['count']; ?>)
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-filter"></i> Search Filters</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="mb-3">
                                <label for="search-query" class="form-label">Search Terms</label>
                                <input type="text" id="search-query" name="q" class="form-control" 
                                       value="<?php echo htmlspecialchars($query); ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="search-tag" class="form-label">Specific Tag</label>
                                <input type="text" id="search-tag" name="tag" class="form-control" 
                                       value="<?php echo htmlspecialchars($tag); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-clock"></i> Recent Articles</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $recentArticles = getAllArticles(5);
                        foreach ($recentArticles as $recent): 
                        ?>
                            <div class="mb-2">
                                <a href="view.php?id=<?php echo $recent['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($recent['title']); ?>
                                </a>
                                <br>
                                <small class="text-muted">
                                    <?php echo date('M j, Y', strtotime($recent['updated_at'])); ?>
                                </small>
                            </div>
                            <hr class="my-2">
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Advanced Search</h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2"><strong>Search operators:</strong></p>
                        <ul class="small list-unstyled">
                            <li><code>"exact phrase"</code> - Find exact matches</li>
                            <li><code>term1 term2</code> - Find both terms</li>
                            <li><code>tag:name</code> - Search within tags</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus search input on page load
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[name="q"]').focus();
            }
        });
    </script>
</body>
</html>
