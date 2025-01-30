<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $thesis_id = $_POST['thesis_id'] ?? null;
    $action = $_POST['action'] ?? null;
    
    if (!$thesis_id || !$action) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing parameters']);
        exit();
    }
    
    try {
        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, thesis_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $_SESSION['user_id'], $thesis_id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Bookmark added']);
        } 
        else if ($action === 'remove') {
            $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND thesis_id = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $thesis_id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}
?>