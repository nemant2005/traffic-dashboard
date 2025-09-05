<?php
require_once("../includes/auth.php");
require_once("../includes/db.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'];
    
    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['success' => false, 'error' => 'No IDs provided']);
        exit;
    }
    
    // Sanitize IDs
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, function($id) { return $id > 0; });
    
    if (empty($ids)) {
        echo json_encode(['success' => false, 'error' => 'No valid IDs provided']);
        exit;
    }
    
    try {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $stmt = $conn->prepare("DELETE FROM traffic_data WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            echo json_encode([
                'success' => true, 
                'message' => "$deleted_count records deleted successfully",
                'deleted_count' => $deleted_count
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>