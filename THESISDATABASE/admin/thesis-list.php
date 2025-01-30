<?php 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

// get all the thesis from theses table
$stmt = $conn->prepare("
    SELECT t.*, u.full_name as submitter_name 
    FROM theses t 
    LEFT JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC
");
$stmt->execute();
$theses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis List</title>
    <link rel="stylesheet" type="text/css" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" href="../css/admin/thesis-list.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="dashboard.php" class="logo">
            <img src="../images/logo.png?v=<?php echo time(); ?>" alt="Thesis DB Logo">

                <span>Administrator</span>
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
                <span>Admin: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
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
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li>
                    <a href="thesis-approval.php">
                        <i class="fas fa-check-circle"></i>
                        <span>Thesis Approval</span>
                    </a>
                </li>
                <li class="active">
                    <a href="thesis-list.php">
                        <i class="fas fa-list"></i>
                        <span>Thesis List</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="dashboard-main">
            <section class="welcome-section">
                <h1>Thesis Repository</h1>
                <p>Manage and view all thesis submissions</p>
            </section>

            <section class="filter-section">
                <select id="categoryFilter" class="filter-select">
                    <option value="all">All Categories</option>
                    <option value="computer_science">Computer Science</option>
                    <option value="information_technology">Information Technology</option>
                    <option value="engineering">Engineering</option>
                    <option value="other">Other</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </section>

            <section class="thesis-grid">
                <?php if ($theses->num_rows > 0): 
                    while ($thesis = $theses->fetch_assoc()): ?>
                    <div class="thesis-card" 
                         data-id="<?php echo $thesis['id']; ?>"
                         data-category="<?php echo $thesis['category']; ?>"
                         data-title="<?php echo strtolower($thesis['title']); ?>"
                         data-authors="<?php echo strtolower($thesis['authors']); ?>"
                         data-keywords="<?php echo strtolower($thesis['keywords']); ?>"
                         data-status="<?php echo $thesis['status']; ?>">
                        
                        <button class="delete-btn" onclick="confirmDelete(<?php echo $thesis['id']; ?>)">
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="thesis-content">
                            <h3 class="thesis-title"><?php echo htmlspecialchars($thesis['title']); ?></h3>
                            <p class="thesis-authors">
                                <i class="fas fa-users"></i>
                                <?php echo htmlspecialchars($thesis['authors']); ?>
                            </p>
                            <p class="thesis-submitter">
                                <i class="fas fa-user"></i>
                                Submitted by: <?php echo htmlspecialchars($thesis['submitter_name']); ?>
                            </p>
                            <p class="thesis-category">
                                <i class="fas fa-tag"></i>
                                <?php echo str_replace('_', ' ', ucwords($thesis['category'])); ?>
                            </p>
                            <p class="thesis-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('F Y', strtotime($thesis['publication_date'])); ?>
                            </p>
                            <p class="thesis-status">
                                <i class="fas fa-info-circle"></i>
                                Status: 
                                <span class="status-badge status-<?php echo $thesis['status']; ?>">
                                    <?php echo ucfirst($thesis['status']); ?>
                                </span>
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
                            <button onclick="showAbstract('<?php echo htmlspecialchars(addslashes($thesis['title'])); ?>', '<?php echo htmlspecialchars(addslashes($thesis['abstract'])); ?>')" class="action-btn view-btn">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <a href="../uploads/theses/<?php echo $thesis['file_path']; ?>" class="action-btn download-btn" target="_blank">
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

    <!-- Abstract Modal -->
    <div id="abstractModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalAbstract"></p>
        </div>
    </div>

    <script src="../js/admin/search.js"></script>
    <script>
    function confirmDelete(thesisId) {
        if (confirm('Are you sure you want to delete this thesis? This action cannot be undone.')) {
            deleteThesis(thesisId);
        }
    }

    function deleteThesis(thesisId) {
        fetch('../api/delete-thesis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${thesisId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const thesisCard = document.querySelector(`.thesis-card[data-id="${thesisId}"]`);
                if (thesisCard) {
                    thesisCard.remove();
                }
                alert('Thesis deleted successfully');
                location.reload();
            } else {
                alert('Error deleting thesis: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting thesis');
        });
    }

    function showAbstract(title, abstract) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalAbstract').textContent = abstract;
        document.getElementById('abstractModal').style.display = 'block';
    }

    document.querySelector('.close').onclick = function() {
        document.getElementById('abstractModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('abstractModal')) {
            document.getElementById('abstractModal').style.display = 'none';
        }
    }
    </script>
</body>
</html>