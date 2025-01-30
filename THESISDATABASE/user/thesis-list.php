<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

// bookmark status
$stmt = $conn->prepare("
    SELECT t.*, 
           u.full_name as submitter_name,
           CASE WHEN b.id IS NOT NULL THEN 1 ELSE 0 END as is_bookmarked
    FROM theses t 
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN bookmarks b ON t.id = b.thesis_id AND b.user_id = ?
    WHERE t.status = 'approved' 
    ORDER BY t.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$theses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis List</title>
    <link rel="stylesheet" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/user/thesis-list.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard.php" class="logo">
            <img src="../images/logo.png?v=<?php echo time(); ?>" alt="Thesis DB Logo">
            </a>
        </div>
        <div class="nav-middle">
            <div class="search-container">
                <input type="text" placeholder="Search thesis..." id="searchInput">
                <button type="button"><i class="fas fa-search"></i></button>
            </div>
        </div>
        <div class="nav-right">
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../api/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <a href="profile.php">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                </li>
                <li>
                    <a href="submit_thesis.php">
                        <i class="fas fa-upload"></i>
                        <span>Submit Thesis</span>
                    </a>
                </li>
                <li class="active">
                    <a href="thesis-list.php">
                        <i class="fas fa-list"></i>
                        <span>Thesis List</span>
                    </a>
                </li>
                <li>
                    <a href="bookmarks.php">
                        <i class="fas fa-bookmark"></i>
                        <span>Bookmarks</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="dashboard-main">
            <section class="welcome-section">
                <h1>Thesis Repository</h1>
                <p>Browse and download thesis documents</p>
            </section>

            <section class="filter-section">
                <select id="categoryFilter" class="filter-select">
                    <option value="all">All Categories</option>
                    <option value="computer_science">Computer Science</option>
                    <option value="information_technology">Information Technology</option>
                    <option value="engineering">Engineering</option>
                    <option value="other">Other</option>
                </select>
            </section>

            <section class="thesis-grid">
                <?php if ($theses->num_rows > 0): 
                    while ($thesis = $theses->fetch_assoc()): ?>
                    <div class="thesis-card" 
                         data-category="<?php echo $thesis['category']; ?>"
                         data-title="<?php echo strtolower($thesis['title']); ?>"
                         data-authors="<?php echo strtolower($thesis['authors']); ?>"
                         data-keywords="<?php echo strtolower($thesis['keywords']); ?>">
                    
                        <button onclick="toggleBookmark(<?php echo $thesis['id']; ?>)" 
                                class="bookmark-icon <?php echo $thesis['is_bookmarked'] ? 'bookmarked' : ''; ?>" 
                                id="bookmark-<?php echo $thesis['id']; ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>

                        <div class="thesis-content">
                            <h3 class="thesis-title"><?php echo htmlspecialchars($thesis['title']); ?></h3>
                            <p class="thesis-authors">
                                <i class="fas fa-users"></i>
                                <?php echo htmlspecialchars($thesis['authors']); ?>
                            </p>
                            <p class="thesis-category">
                                <i class="fas fa-tag"></i>
                                <?php echo str_replace('_', ' ', ucwords($thesis['category'])); ?>
                            </p>
                            <p class="thesis-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('F Y', strtotime($thesis['publication_date'])); ?>
                            </p>
                            <div class="thesis-abstract">
                                <?php echo nl2br(substr(htmlspecialchars($thesis['abstract']), 0, 200)) . '...'; ?>
                            </div>
                            <?php if (!empty($thesis['keywords'])): ?>
                                <div class="thesis-keywords">
                                    <?php 
                                    $keywords = explode(',', $thesis['keywords']);
                                    foreach ($keywords as $keyword) {
                                        echo '<span class="keyword-tag">' . trim(htmlspecialchars($keyword)) . '</span>';
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="thesis-actions">
                            <button onclick="showAbstract('<?php echo htmlspecialchars(addslashes($thesis['title'])); ?>', 
                                                         '<?php echo htmlspecialchars(addslashes($thesis['abstract'])); ?>', 
                                                         '<?php echo htmlspecialchars(addslashes($thesis['authors'])); ?>')" 
                                    class="action-btn view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <a href="../uploads/theses/<?php echo $thesis['file_path']; ?>" 
                               class="action-btn download-btn" 
                               target="_blank">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>
                    </div>
                <?php endwhile;
                else: ?>
                    <div class="no-records">
                        <i class="fas fa-search"></i>
                        <p>No thesis entries found</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div id="abstractModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle" class="thesis-title"></h2>
            <div class="modal-info">
                <p class="thesis-info">
                    <i class="fas fa-users"></i>
                    <span id="modalAuthor"></span>
                </p>
            </div>
            <div class="modal-abstract">
                <h3><i class="fas fa-file-alt"></i> Abstract</h3>
                <p id="modalAbstract"></p>
            </div>
        </div>
    </div>

    <script src="../js/user/search.js"></script>
    <script src="../js/user/bookmarks.js"></script>
</body>
</html>