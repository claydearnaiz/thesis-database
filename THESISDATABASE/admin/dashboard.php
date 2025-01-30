<?php 
session_start(); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

$users_stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE id != ?");
$users_stmt->bind_param("i", $_SESSION['user_id']);
$users_stmt->execute();
$total_users = $users_stmt->get_result()->fetch_assoc()['total_users'];


$pending_stmt = $conn->prepare("SELECT COUNT(*) as pending_thesis FROM theses WHERE status = 'pending'");
$pending_stmt->execute();
$pending_thesis = $pending_stmt->get_result()->fetch_assoc()['pending_thesis'];


$thesis_stmt = $conn->prepare("SELECT COUNT(*) as total_thesis FROM theses");
$thesis_stmt->execute();
$total_thesis = $thesis_stmt->get_result()->fetch_assoc()['total_thesis'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CPE Database</title>
    <link rel="stylesheet" type="text/css" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
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
                <li class="active">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
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
                <li>
                    <a href="thesis-list.php">
                        <i class="fas fa-list"></i>
                        <span>Thesis List</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="dashboard-main">
            <section class="welcome-section">
                <h1>Admin Dashboard</h1>
                <p>Monitor and manage the thesis database system</p>
            </section>

            <section class="quick-stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total Users</h3>
                    <p class="stat-number"><?php echo $total_users; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-file-alt"></i>
                    <h3>Total Thesis</h3>
                    <p class="stat-number"><?php echo $total_thesis; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Pending Thesis</h3>
                    <p class="stat-number"><?php echo $pending_thesis; ?></p>
                </div>
            </section>

            <section class="recent-thesis">
                <h2>Quick Actions</h2>
                <div class="thesis-grid">
                <div class="thesis-card">
                        <h3>Manage Users</h3>
                        <p>Check the list of users</p>
                        <a href="users.php" class="submit-btn">Check Users</a>
                    </div>
                    <div class="thesis-card">
                        <h3>Thesis Management</h3>
                        <p>Review and approve thesis submissions</p>
                        <a href="thesis-approval.php" class="submit-btn">Manage Thesis</a>
                    </div>
                    <div class="thesis-card">
                        <h3>View All Thesis</h3>
                        <p>Browse and manage all thesis entries</p>
                        <a href="thesis-list.php" class="submit-btn">View List</a>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>