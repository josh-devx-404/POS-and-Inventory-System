<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Handle GET request - Fetch all items or single item
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['id'])) {
            // Get single item
            $stmt = $conn->prepare("
                SELECT m.*, c.name as category_name 
                FROM menu_items m
                LEFT JOIN categories c ON m.category_id = c.category_id
                WHERE m.item_id = ?
            ");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'item' => $item
            ]);
        } else {
            // Get all items
            $stmt = $conn->prepare("
                SELECT m.*, c.name as category_name 
                FROM menu_items m
                LEFT JOIN categories c ON m.category_id = c.category_id
                ORDER BY m.name
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'items' => $items
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Handle POST request - Create or Update item
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $item_id = $_POST['item_id'] ?? null;
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'];
        $stock = $_POST['stock'] ?? 0;
        
        // Handle image upload
        $image_path = $_POST['current_image'] ?? '';
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('product_') . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'uploads/products/' . $file_name;
                
                // Delete old image if exists
                if (!empty($_POST['current_image']) && file_exists('../' . $_POST['current_image'])) {
                    unlink('../' . $_POST['current_image']);
                }
            }
        }
        
        if ($item_id) {
            // Update existing item
            $stmt = $conn->prepare("
                UPDATE menu_items 
                SET name = ?, category_id = ?, description = ?, image_path = ?, price = ?, stock = ?
                WHERE item_id = ?
            ");
            $stmt->bind_param("sissdii", $name, $category_id, $description, $image_path, $price, $stock, $item_id);
        } else {
            // Insert new item
            $stmt = $conn->prepare("
                INSERT INTO menu_items (name, category_id, description, image_path, price, stock) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sissdi", $name, $category_id, $description, $image_path, $price, $stock);
        }
        
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => $item_id ? 'Item updated successfully' : 'Item created successfully',
            'item_id' => $item_id ?: $conn->insert_id
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
            throw new Exception('Item ID is required');
        }
        
        // Get image path before deleting
        $stmt = $conn->prepare("SELECT image_path FROM menu_items WHERE item_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        
        // Delete item
        $stmt = $conn->prepare("DELETE FROM menu_items WHERE item_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Delete image file if exists
        if (!empty($item['image_path']) && file_exists('../' . $item['image_path'])) {
            unlink('../' . $item['image_path']);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Item deleted successfully'
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