<?php
$page_title = "Search Thesis";
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/functions.php';

$database = new Database();
$conn = $database->getConnection();

$results = [];
$search_query = '';
$total_results = 0;

if (isset($_GET['q'])) {
    $search_query = sanitize($_GET['q']);

    $stmt = $conn->prepare("
        SELECT t.*, u.first_name, u.last_name, d.department_name 
        FROM thesis t
        JOIN users u ON t.author_id = u.user_id
        JOIN departments d ON t.department_id = d.department_id
        WHERE t.status = 'approved' AND (
            t.title LIKE ? OR 
            t.abstract LIKE ? OR 
            t.keywords LIKE ? OR 
            CONCAT(u.first_name, ' ', u.last_name) LIKE ?
        )
        ORDER BY t.publication_year DESC
    ");
    $search_term = "%$search_query%";
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $results = $stmt->fetchAll();
    $total_results = count($results);
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Thesis Archive</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="container" style="margin-top: 48px; margin-bottom: 48px;">
        <!-- Search Header -->
        <div class="search-header">
            <h1>Search Thesis</h1>
            <p>Find academic research and thesis documents</p>
        </div>

        <!-- Search Box -->
        <div class="card" style="margin-bottom: 32px;">
            <form method="GET" action="">
                <div class="search-box">
                    <input
                        type="text"
                        name="q"
                        placeholder="Search by title, author, keywords..."
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        required>
                    <button type="submit">Search</button>
                </div>
            </form>
        </div>

        <!-- Search Results -->
        <?php if ($search_query): ?>
            <div class="search-results-header">
                <h2>Search Results</h2>
                <p class="text-muted">Found <?php echo $total_results; ?> result<?php echo $total_results != 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search_query); ?>"</p>
            </div>

            <?php if ($total_results > 0): ?>
                <div class="results-list">
                    <?php foreach ($results as $thesis): ?>
                        <div class="card result-card">
                            <h3 class="result-title">
                                <?php echo htmlspecialchars($thesis['title']); ?>
                            </h3>

                            <div class="result-meta">
                                <span><strong>Author:</strong> <?php echo htmlspecialchars($thesis['first_name'] . ' ' . $thesis['last_name']); ?></span>
                                <span class="meta-divider">•</span>
                                <span><strong>Year:</strong> <?php echo htmlspecialchars($thesis['publication_year']); ?></span>
                                <span class="meta-divider">•</span>
                                <span><strong>Department:</strong> <?php echo htmlspecialchars($thesis['department_name']); ?></span>
                            </div>

                            <?php if (!empty($thesis['keywords'])): ?>
                                <div class="result-keywords">
                                    <?php
                                    $keywords = explode(',', $thesis['keywords']);
                                    foreach (array_slice($keywords, 0, 5) as $keyword):
                                    ?>
                                        <span class="keyword-tag"><?php echo htmlspecialchars(trim($keyword)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <p class="result-abstract">
                                <?php echo htmlspecialchars(substr($thesis['abstract'], 0, 250)) . '...'; ?>
                            </p>

                            <a href="view-thesis.php?id=<?php echo $thesis['thesis_id']; ?>" class="btn btn-primary btn-sm">
                                View Details →
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="card empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <h3>No Results Found</h3>
                    <p>Try different keywords or check your spelling</p>
                    <a href="search.php" class="btn btn-secondary">New Search</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Initial State - No Search Yet -->
            <div class="card empty-state">
                <div class="empty-state-icon">🔎</div>
                <h3>Start Your Search</h3>
                <p>Enter keywords, title, or author name to find thesis documents</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="home-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Thesis Archive Management System</p>
        </div>
    </footer>
</body>

</html>