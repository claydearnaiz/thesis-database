<?php 
session_start(); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Thesis</title>
    <link rel="stylesheet" type="text/css" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" href="../css/user/submit_thesis.css?v=<?php echo time(); ?>">
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
                <li class="active">
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
                <h1>Submit Your Thesis</h1>
                <p>Share your research work with the academic community</p>
            </section>

            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
            <?php endif; ?>

            <section class="submit-section">
                <div class="submit-container">
                    <form action="../api/upload_thesis.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="title">Thesis Title</label>
                            <input type="text" id="title" name="title" required>
                        </div>

                        <div class="form-group">
                            <label for="authors">Authors (separate multiple authors with commas)</label>
                            <input type="text" id="authors" name="authors" 
                                   placeholder="e.g., John Doe, Jane Smith" required>
                        </div>

                        <div class="form-group">
                            <label for="abstract">Abstract</label>
                            <textarea id="abstract" name="abstract" rows="6" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="computer_science">Computer Science</option>
                                <option value="information_technology">Information Technology</option>
                                <option value="engineering">Engineering</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="keywords">Keywords (comma separated)</label>
                            <input type="text" id="keywords" name="keywords" 
                                   placeholder="e.g., AI, Machine Learning, Data Science">
                        </div>

                        <div class="form-group">
                            <label for="thesis_file">Thesis File (PDF only)</label>
                            <input type="file" id="thesis_file" name="thesis_file" accept=".pdf" required>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Submit Thesis
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>