<?php
require_once './api/config.php';
$stmt = $conn->prepare("
    SELECT id, title, authors, publication_date, category, abstract 
    FROM theses 
    WHERE status = 'approved' 
    ORDER BY RAND() 
    LIMIT 6
");
$stmt->execute();
$featured_theses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Database System</title>
    <link rel="stylesheet" type="text/css" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.11.2/js/all.min.js" crossorigin="anonymous"></script>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery/jquery.form.js"></script>
</head>
<body>
    <div class="background-slideshow">
        <div class="slide" style="background-image: url('./images/adamson1.jpg');"></div>
        <div class="slide" style="background-image: url('./images/adamson2.jpg');"></div>
        <div class="slide" style="background-image: url('./images/adamson3.jpg');"></div>
        <div class="slide" style="background-image: url('./images/adamson4.jpg');"></div>
    </div>

    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo">
            <img src="./images/logo.png?v=<?php echo time(); ?>" alt="Thesis DB Logo">            </a>
        </div>
        <div class="nav-right">
            <div class="auth-buttons">
                <button onclick="showLoginModal('user')" class="login-btn">Student Login</button>
                <button onclick="showLoginModal('admin')" class="admin-btn">Admin Login</button>
                <button onclick="window.location.href='register.php'" class="register-btn">Register</button>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <h1>Welcome to Thesis Database System</h1>
            <p>Explore academic research and dissertations</p>
        </section>

        <section class="featured-thesis">
            <h2>Featured Thesis</h2>
            <div class="thesis-grid">
                <?php if ($featured_theses->num_rows > 0): 
                    while ($thesis = $featured_theses->fetch_assoc()): ?>
                    <div class="thesis-card">
                        <span class="thesis-category">
                            <?php echo htmlspecialchars(str_replace('_', ' ', ucwords($thesis['category']))); ?>
                        </span>
                        <h3><?php echo htmlspecialchars($thesis['title']); ?></h3>
                        <div class="thesis-info">
                            <p><strong>Authors:</strong> <?php echo htmlspecialchars($thesis['authors']); ?></p>
                            <p><strong>Published:</strong> <?php echo date('F Y', strtotime($thesis['publication_date'])); ?></p>
                        </div>
                        <button onclick="showThesisDetails('<?php echo htmlspecialchars(addslashes($thesis['title'])); ?>', 
                                                         '<?php echo htmlspecialchars(addslashes($thesis['abstract'])); ?>', 
                                                         '<?php echo htmlspecialchars(addslashes($thesis['authors'])); ?>')" 
                                class="view-btn">
                            View Details <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                <?php endwhile; 
                else: ?>
                    <div class="no-thesis text-center">
                        <p>No thesis entries available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeLoginModal()">&times;</span>
            <h2 id="modalTitle">Login</h2>
            <form id="loginForm" action="api/login.php" method="POST">
                <input type="hidden" id="userType" name="userType" value="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="login2-btn">Login</button>
                </div>
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <div id="thesisModal" class="modal">
        <div class="modal-content thesis-modal">
            <span class="close" onclick="closeThesisModal()">&times;</span>
            <h2 id="thesisTitle"></h2>
            <div class="thesis-modal-info">
                <p><strong>Authors:</strong></p>
                <p id="thesisAuthors" class="thesis-authors"></p>
                <p><strong>Abstract:</strong></p>
                <p id="thesisAbstract" class="thesis-abstract"></p>
            </div>
        </div>
    </div>

    <script src="./js/login.js"></script>
    <script src="./js/slideshow.js"></script>
</body>
</html>