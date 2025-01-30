<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$query = "SELECT t.*, u.full_name as submitter_name 
          FROM theses t
          LEFT JOIN users u ON t.user_id = u.id
          WHERE (t.status = 'approved') AND 
                (t.title LIKE ? OR 
                 t.authors LIKE ? OR 
                 t.keywords LIKE ? OR 
                 t.abstract LIKE ?)";

if ($category !== 'all') {
    $query .= " AND t.category = ?";
}

$query .= " ORDER BY t.publication_date DESC";

$search_term = "%$search%";
$stmt = $conn->prepare($query);

if ($category !== 'all') {
    $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $category);
} else {
    $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
}

$stmt->execute();
$result = $stmt->get_result();

$theses = array();
while ($thesis = $result->fetch_assoc()) {
    $thesis['publication_date'] = date('F Y', strtotime($thesis['publication_date']));
    $thesis['abstract_preview'] = nl2br(substr(htmlspecialchars($thesis['abstract']), 0, 200)) . '...';
    $thesis['category_display'] = str_replace('_', ' ', ucwords($thesis['category']));
    
    $keywords_html = '';
    if (!empty($thesis['keywords'])) {
        $keywords = explode(',', $thesis['keywords']);
        foreach ($keywords as $keyword) {
            $keywords_html .= '<span class="keyword-tag">' . trim(htmlspecialchars($keyword)) . '</span>';
        }
    }
    $thesis['keywords_html'] = $keywords_html;
    
    $theses[] = $thesis;
}

header('Content-Type: application/json');
echo json_encode($theses);