<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

require_once '../api/config.php';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $email_check->bind_param("si", $email, $_SESSION['user_id']);
    $email_check->execute();
    if ($email_check->get_result()->num_rows > 0) {
        $errors[] = "Email is already taken";
    }

    if (!empty($current_password)) {
        $password_check_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $password_check_stmt->bind_param("i", $_SESSION['user_id']);
        $password_check_stmt->execute();
        $db_password = $password_check_stmt->get_result()->fetch_assoc()['password'];

        // checking of current password
        if ($current_password !== $db_password) {
            $errors[] = "Current password is incorrect";
        }

        if (empty($new_password)) {
            $errors[] = "New password is required";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }
    }

    if (empty($errors)) {
        if (!empty($new_password)) {
            $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("sssi", $full_name, $email, $new_password, $_SESSION['user_id']);
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $full_name, $email, $_SESSION['user_id']);
        }

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully";
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $errors[] = "Failed to update profile";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../css/user/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../css/user/profile.css?v=<?php echo time(); ?>">
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
                <li class="active">
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
                <h1>My Profile</h1>
                <p>Manage your account information</p>
            </section>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
            <?php endif; ?>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="section-divider"></div>
                    <h3 class="section-title">Change Password</h3>

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>