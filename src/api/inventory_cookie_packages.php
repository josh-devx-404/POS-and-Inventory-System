<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single package
            $stmt = $conn->prepare("SELECT * FROM cookies_packages WHERE package_id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $package = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'package' => $package
            ]);
        } else {
            // Get all packages
            $stmt = $conn->prepare("SELECT * FROM cookies_packages ORDER BY package_name");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $packages = [];
            while ($row = $result->fetch_assoc()) {
                $packages[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'packages' => $packages
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
        $package_id = $_POST['package_id'] ?? null;
        $package_name = $_POST['package_name'];
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        
        if ($package_id) {
            // Update existing package
            $stmt = $conn->prepare("
                UPDATE cookies_packages 
                SET package_name = ?, description = ?, price = ?, quantity = ?
                WHERE package_id = ?
            ");
            $stmt->bind_param("ssdii", $package_name, $description, $price, $quantity, $package_id);
        } else {
            // Insert new package
            $stmt = $conn->prepare("
                INSERT INTO cookies_packages (package_name, description, price, quantity) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("ssdi", $package_name, $description, $price, $quantity);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => $package_id ? 'Package updated successfully' : 'Package created successfully'
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
            throw new Exception('Package ID is required');
        }
        
        $stmt = $conn->prepare("DELETE FROM cookies_packages WHERE package_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Package deleted successfully'
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