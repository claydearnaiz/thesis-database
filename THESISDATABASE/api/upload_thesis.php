<?php
session_start();
require_once '../api/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
$authors = filter_input(INPUT_POST, 'authors', FILTER_SANITIZE_STRING);
$abstract = filter_input(INPUT_POST, 'abstract', FILTER_SANITIZE_STRING);
$category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
$keywords = filter_input(INPUT_POST, 'keywords', FILTER_SANITIZE_STRING);

if (!$title || !$authors || !$abstract || !$category) {
    header('Location: ../user/submit_thesis.php?error=Missing required fields');
    exit();
}

if (!isset($_FILES['thesis_file']) || $_FILES['thesis_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: ../user/submit_thesis.php?error=No file uploaded or upload error');
    exit();
}

$file = $_FILES['thesis_file'];
$fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($fileType !== 'pdf') {
    header('Location: ../user/submit_thesis.php?error=Only PDF files are allowed');
    exit();
}
// 50 mb
if ($file['size'] > 50 * 1024 * 1024) {
    header('Location: ../user/submit_thesis.php?error=File size must be less than 50MB');
    exit();
}
// file upload directory
$uploadDir = '../uploads/theses/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = $file['name'];
$uploadPath = $uploadDir . $fileName;

try {
    $conn->begin_transaction();

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    $sql = "INSERT INTO theses (
        title, authors, abstract, category, keywords, file_path, user_id, status, publication_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param(
        "ssssssi",
        $title,
        $authors,
        $abstract,
        $category,
        $keywords,
        $fileName,
        $_SESSION['user_id']
    );

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $conn->commit();
    header('Location: ../user/dashboard.php?success=Thesis submitted successfully');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    header('Location: ../user/submit_thesis.php?error=Failed to upload thesis: ' . $e->getMessage());
    exit();
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?>