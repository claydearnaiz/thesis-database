<?php 
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $userType = $_POST['userType'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_type = ? AND password = ?");
        $stmt->bind_param("sss", $email, $userType, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if ($user['status'] !== 'approved') {
                $statusMessage = $user['status'] === 'pending' ? "Your account is pending approval" : "Your account has been rejected";
                echo "<script>alert('$statusMessage'); window.location.href='../index.php';</script>";
                exit();
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            if ($user['user_type'] === 'admin') {
                echo "<script>window.location.href='../admin/dashboard.php';</script>";
            } else {
                echo "<script>window.location.href='../user/dashboard.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Invalid credentials'); window.location.href='../index.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('System error occurred'); window.location.href='../index.php';</script>";
    }
    exit();
}
?>