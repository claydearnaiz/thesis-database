<?php 
session_start(); 
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $thesis_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $status = filter_var($_GET['status'], FILTER_SANITIZE_STRING);
    
    if (in_array($status, ['approved', 'rejected'])) {
        try {
            $update_stmt = $conn->prepare("UPDATE theses SET status = ? WHERE id = ?");
            $update_stmt->bind_param("si", $status, $thesis_id);
            if ($update_stmt->execute()) {
                $success_message = "Thesis status updated to " . ucfirst($status);
            } else {
                $error_message = "Failed to update thesis status";
            }
            $update_stmt->close();
        } catch (Exception $e) {
            $error_message = "System error occurred";
        }
    }
}
$stmt = $conn->prepare("
    SELECT t.*, u.full_name as submitter_name 
    FROM theses t
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY 
        CASE t.status 
            WHEN 'pending' THEN 1 
            WHEN 'approved' THEN 2 
            WHEN 'rejected' THEN 3 
        END, 
        t.created_at DESC
");
$stmt->execute();
$theses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Approval</title>
    <link rel="stylesheet" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/admin/thesis-approval.css?v=<?php echo time(); ?>">
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
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li class="active">
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
                <h1>Thesis Approval</h1>
                <p>Review and approve thesis submissions</p>
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

            <section class="thesis-section">
                <div class="thesis-container">
                    <div class="thesis-header">
                        <h2>Thesis Submissions</h2>
                    </div>
                    <div class="thesis-table-container">
                        <table class="thesis-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Authors</th>
                                    <th>Submitted By</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Date Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($theses->num_rows > 0): 
                                    while ($thesis = $theses->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($thesis['title']); ?></td>
                                    <td><?php echo htmlspecialchars($thesis['authors']); ?></td>
                                    <td><?php echo htmlspecialchars($thesis['submitter_name']); ?></td>
                                    <td><?php echo str_replace('_', ' ', ucwords($thesis['category'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $thesis['status']; ?>">
                                            <?php echo ucfirst($thesis['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($thesis['created_at'])); ?></td>
                                    <td class="actions">
                                        <button onclick="viewThesis(<?php echo htmlspecialchars(json_encode($thesis)); ?>)" 
                                                class="action-btn view-btn" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($thesis['status'] !== 'approved'): ?>
                                        <a href="?action=update_status&id=<?php echo $thesis['id']; ?>&status=approved" 
                                           class="action-btn approve-btn" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <?php endif; ?>
                                        <?php if ($thesis['status'] !== 'rejected'): ?>
                                        <a href="?action=update_status&id=<?php echo $thesis['id']; ?>&status=rejected" 
                                           class="action-btn reject-btn" title="Reject">
                                            <i class="fas fa-times"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile;
                                else: ?>
                                <tr>
                                    <td colspan="7" class="no-records">No thesis submissions found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="thesisModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
    function viewThesis(thesis) {
        const modal = document.getElementById('thesisModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        
        modalTitle.textContent = thesis.title;
        modalBody.innerHTML = `
            <p><strong>Authors:</strong> ${thesis.authors}</p>
            <p><strong>Category:</strong> ${thesis.category.replace('_', ' ')}</p>
            <p><strong>Publication Date:</strong> ${new Date(thesis.publication_date).toLocaleDateString()}</p>
            <p><strong>Keywords:</strong> ${thesis.keywords}</p>
            <h3>Abstract</h3>
            <p>${thesis.abstract}</p>
            <div class="modal-actions">
                <a href="../uploads/theses/${thesis.file_path}" class="action-btn download-btn" target="_blank">
                    <i class="fas fa-download"></i> Download PDF
                </a>
            </div>
        `;
        
        modal.style.display = "block";
    }

    const modal = document.getElementById('thesisModal');
    const span = document.getElementsByClassName("close")[0];
    
    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    </script>
</body>
</html>