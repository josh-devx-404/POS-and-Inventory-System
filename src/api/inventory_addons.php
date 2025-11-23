<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single addon
            $stmt = $conn->prepare("SELECT * FROM addons WHERE addon_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $addon = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'addon' => $addon
            ]);
        } else {
            // Get all addons
            $stmt = $conn->prepare("SELECT * FROM addons ORDER BY name");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $addons = [];
            while ($row = $result->fetch_assoc()) {
                $addons[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'addons' => $addons
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
        $addon_id = $_POST['addon_id'] ?? null;
        $name = $_POST['name'];
        $price = $_POST['price'];
        
        if ($addon_id) {
            // Update existing addon
            $stmt = $conn->prepare("
                UPDATE addons 
                SET name = ?, price = ?
                WHERE addon_id = ?
            ");
            $stmt->bind_param("sdi", $name, $price, $addon_id);
        } else {
            // Insert new addon
            $stmt = $conn->prepare("
                INSERT INTO addons (name, price) 
                VALUES (?, ?)
            ");
            $stmt->bind_param("sd", $name, $price);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => $addon_id ? 'Add-on updated successfully' : 'Add-on created successfully'
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
            throw new Exception('Add-on ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM addons WHERE addon_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Add-on deleted successfully'
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