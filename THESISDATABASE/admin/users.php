<?php 
session_start(); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $user_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($_GET['status'], FILTER_SANITIZE_STRING);
    
    if (in_array($status, ['approved', 'rejected'])) {
        try {
            $update_stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $status, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "User status updated to " . ucfirst($status);
            } else {
                $error_message = "Failed to update user status";
            }
            $update_stmt->close();
        } catch (Exception $e) {
            $error_message = "System error occurred";
        }
    }
}
$stmt = $conn->prepare("SELECT id, full_name, email, user_type, status, created_at 
                       FROM users 
                       WHERE id != ? 
                       ORDER BY 
                           CASE status 
                               WHEN 'pending' THEN 1 
                               WHEN 'approved' THEN 2 
                               WHEN 'rejected' THEN 3 
                           END, 
                           created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" type="text/css" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" type="text/css" href="../css/admin/users.css?v=<?php echo time(); ?>">
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
                <li>
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="active">
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
                <h1>Manage Users</h1>
                <p>Review and manage user accounts</p>
            </section>

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <section class="users-section">
                <div class="users-container">
                    <div class="users-header">
                        <h2>User Accounts</h2>
                    </div>

                    <div class="users-table-container">
                        <table class="users-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($users->num_rows > 0):
                                    while ($user = $users->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($user['user_type'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $user['status']; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="actions">
                                        <?php if ($user['status'] !== 'approved'): ?>
                                        <a href="?action=update_status&id=<?php echo $user['id']; ?>&status=approved" 
                                           class="action-btn approve-btn" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['status'] !== 'rejected'): ?>
                                        <a href="?action=update_status&id=<?php echo $user['id']; ?>&status=rejected" 
                                           class="action-btn reject-btn" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" class="no-records">No users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>