<?php 
session_start(); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_submissions,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_submissions,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_submissions
    FROM theses 
    WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$recent_stmt = $conn->prepare("SELECT * FROM theses WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recent_stmt->bind_param("i", $user_id);
$recent_stmt->execute();
$recent_submissions = $recent_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
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
                <li class="active">
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
                <li>
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
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>
                <p>Manage your thesis submissions and explore other research work</p>
            </section>

            <section class="quick-stats">
                <div class="stat-card">
                    <i class="fas fa-file-alt"></i>
                    <h3>My Submissions</h3>
                    <p class="stat-number"><?php echo $stats['total_submissions']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Approved Thesis</h3>
                    <p class="stat-number"><?php echo $stats['approved_submissions']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Review</h3>
                    <p class="stat-number"><?php echo $stats['pending_submissions']; ?></p>
                </div>
            </section>

            <section class="recent-thesis">
                <h2>Recent Submissions</h2>
                <div class="thesis-grid">
                    <?php if ($recent_submissions->num_rows > 0): ?>
                        <?php while ($thesis = $recent_submissions->fetch_assoc()): ?>
                            <div class="thesis-card">
                                <h3><?php echo htmlspecialchars($thesis['title']); ?></h3>
                                <p class="thesis-info">
                                    <span class="status-badge status-<?php echo $thesis['status']; ?>">
                                        <?php echo ucfirst($thesis['status']); ?>
                                    </span>
                                    <span class="submission-date">
                                        Submitted: <?php echo date('M d, Y', strtotime($thesis['created_at'])); ?>
                                    </span>
                                </p>
                                <button onclick="showThesisDetails('<?php echo htmlspecialchars(addslashes($thesis['title'])); ?>', 
                                                                '<?php echo htmlspecialchars(addslashes($thesis['abstract'])); ?>', 
                                                                '<?php echo htmlspecialchars(addslashes($thesis['authors'])); ?>')" 
                                        class="view-btn">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="thesis-card">
                            <h3>No submissions yet</h3>
                            <p>Start by submitting your thesis</p>
                            <a href="submit_thesis.php" class="submit-btn">Submit Now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    <div id="thesisModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalThesisTitle"></h2>
            <div class="thesis-details">
                <div class="author-section">
                    <h3><i class="fas fa-users"></i> Authors</h3>
                    <p id="modalThesisAuthors"></p>
                </div>
                <div class="abstract-section">
                    <h3><i class="fas fa-file-alt"></i> Abstract</h3>
                    <p id="modalThesisAbstract"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/user/dashboard.js"></script>
</body>
</html>