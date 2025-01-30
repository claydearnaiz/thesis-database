<?php 
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'user';

    if (strlen($full_name) < 2) {
        echo "<script>alert('Full name must be at least 2 characters'); window.location.href='../register.php';</script>";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email address'); window.location.href='../register.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo "<script>alert('Email already exists'); window.location.href='../register.php';</script>";
        exit();
    }

    if (strlen($password) < 6) {
        echo "<script>alert('Password must be at least 6 characters'); window.location.href='../register.php';</script>";
        exit();
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match'); window.location.href='../register.php';</script>";
        exit();
    }

    if (!in_array($user_type, ['admin', 'user'])) {
        echo "<script>alert('Invalid user type'); window.location.href='../register.php';</script>";
        exit();
    }

    $status = $user_type === 'admin' ? 'pending' : 'pending';
    
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, user_type, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $full_name, $email, $password, $user_type, $status);
    
    if ($stmt->execute()) {
        echo "<script>alert('Registration successful! Please wait for admin approval.'); window.location.href='../index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Registration failed! Please try again.'); window.location.href='../register.php';</script>";
        exit();
    }
}
?>