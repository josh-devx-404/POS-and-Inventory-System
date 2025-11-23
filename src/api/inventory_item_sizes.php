<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single size
            $stmt = $conn->prepare("
                SELECT s.*, m.name as item_name 
                FROM item_sizes s
                JOIN menu_items m ON s.item_id = m.item_id
                WHERE s.size_id = ?
            ");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $size = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'size' => $size
            ]);
        } else {
            // Get all sizes
            $stmt = $conn->prepare("
                SELECT s.*, m.name as item_name 
                FROM item_sizes s
                JOIN menu_items m ON s.item_id = m.item_id
                ORDER BY m.name, s.size_name
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sizes = [];
            while ($row = $result->fetch_assoc()) {
                $sizes[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'sizes' => $sizes
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle POST request
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $size_id = $_POST['size_id'] ?? null;
        $item_id = $_POST['item_id'];
        $size_name = $_POST['size_name'];
        $price = $_POST['price'];
        
        if ($size_id) {
            // Update existing size
            $stmt = $conn->prepare("
                UPDATE item_sizes 
                SET item_id = ?, size_name = ?, price = ?
                WHERE size_id = ?
            ");
            $stmt->bind_param("isdi", $item_id, $size_name, $price, $size_id);
        } else {
            // Insert new size
            $stmt = $conn->prepare("
                INSERT INTO item_sizes (item_id, size_name, price) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("isd", $item_id, $size_name, $price);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => $size_id ? 'Size updated successfully' : 'Size created successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle DELETE request
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    try {
        parse_str(file_get_contents("php://input"), $_DELETE);
        $id = $_GET['id'] ?? $_DELETE['id'] ?? null;
        
        if (!$id) {
            throw new Exception('Size ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM item_sizes WHERE size_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Size deleted successfully'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

$conn->close();
?>