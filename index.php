<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize database
initializeDatabase();

// Get articles for homepage
$articles = getAllArticles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Knowledge Base</title>
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
                <h1 class="mb-4">Welcome to the Knowledge Base</h1>
                
                <div class="mb-4">
                    <form action="search.php" method="GET" class="d-flex">
                        <input type="text" name="q" class="form-control me-2" placeholder="Search articles...">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>

                <div class="articles">
                    <?php if (empty($articles)): ?>
                        <div class="alert alert-info">
                            <h4>No articles yet!</h4>
                            <p>Start building your knowledge base by <a href="create.php">creating your first article</a>.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="view.php?id=<?php echo $article['id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($article['content'], 0, 200)); ?>...</p>
                                    <div class="mb-2">
                                        <?php if ($article['tags']): ?>
                                            <?php foreach (explode(',', $article['tags']) as $tag): ?>
                                                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars(trim($tag)); ?></span>
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
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-tags"></i> Popular Tags</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        try {
                            $tags = getPopularTags();
                            if (!empty($tags)) {
                                foreach ($tags as $tag): 
                                ?>
                                    <a href="search.php?tag=<?php echo urlencode($tag['tag']); ?>" class="badge bg-light text-dark me-1 mb-1 text-decoration-none">
                                        <?php echo htmlspecialchars($tag['tag']); ?> (<?php echo $tag['count']; ?>)
                                    </a>
                                <?php 
                                endforeach;
                            } else {
                                echo '<p class="text-muted mb-0">No tags yet. <a href="create.php">Create an article</a> to get started!</p>';
                            }
                        } catch (Exception $e) {
                            echo '<p class="text-muted mb-0">Tags will appear here once you create articles.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
