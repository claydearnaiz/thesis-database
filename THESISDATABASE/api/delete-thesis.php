<?php
session_start();
require_once 'config.php';

// user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// id
if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No thesis ID provided']);
    exit();
}

$thesis_id = $_POST['id'];

try {
    $conn->begin_transaction();

    // delete bookmarks
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE thesis_id = ?");
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();

    // getting of file path
    $stmt = $conn->prepare("SELECT file_path FROM theses WHERE id = ?");
    $stmt->bind_param("i", $thesis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $thesis = $result->fetch_assoc();

    if ($thesis) {
        // if files exist
        $file_path = "../uploads/theses/" . $thesis['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // delete thesis from the theses table
        $stmt = $conn->prepare("DELETE FROM theses WHERE id = ?");
        $stmt->bind_param("i", $thesis_id);
        
        if ($stmt->execute()) {

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Thesis deleted successfully']);
        } else {
            throw new Exception("Error deleting thesis");
        }
    } else {
        throw new Exception("Thesis not found");
    }
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();