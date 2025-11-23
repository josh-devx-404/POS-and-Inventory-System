<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/db.php';

// Simple API for inventory management. Expects POST for mutating actions.
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

try {
    if ($action === 'list') {
        // Return ingredients and recent movements
        $ingredients = db_fetch_all('SELECT id, name, unit, unit_price, current_stock, reorder_level, notes, last_updated FROM ingredients ORDER BY name');
        $movements = db_fetch_all('SELECT im.id, im.ingredient_id, ing.name AS ingredient, im.type, im.quantity, im.reference, im.date FROM inventory_movements im JOIN ingredients ing ON im.ingredient_id = ing.id ORDER BY im.date DESC LIMIT 200');
        echo json_encode(['success' => true, 'ingredients' => $ingredients, 'movements' => $movements]);
        exit;
    }

    if ($method !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Read input (supports application/json)
    $input = $_POST;
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json)) $input = array_merge($input, $json);
    }

    if ($action === 'save_item') {
        $id = isset($input['id']) && $input['id'] !== '' ? (int)$input['id'] : null;
        $name = trim($input['name'] ?? '');
        $unit = trim($input['unit'] ?? '');
        $price = isset($input['price']) ? (float)$input['price'] : 0.0;
        $stock = isset($input['stock']) ? (float)$input['stock'] : 0.0;
        $reorder = isset($input['reorder']) ? (float)$input['reorder'] : 0.0;
        $notes = $input['notes'] ?? null;

        if ($name === '' || $unit === '') throw new Exception('Missing required fields');

        if ($id) {
            db_execute('UPDATE ingredients SET name = ?, unit = ?, unit_price = ?, current_stock = ?, reorder_level = ?, notes = ? WHERE id = ?', [$name, $unit, $price, $stock, $reorder, $notes, $id]);
            $item = db_fetch('SELECT id, name, unit, unit_price, current_stock, reorder_level, notes, last_updated FROM ingredients WHERE id = ?', [$id]);
            echo json_encode(['success' => true, 'item' => $item]);
            exit;
        } else {
            db_execute('INSERT INTO ingredients (name, unit, unit_price, current_stock, reorder_level, notes) VALUES (?, ?, ?, ?, ?, ?)', [$name, $unit, $price, $stock, $reorder, $notes]);
            $newId = db_last_insert_id();
            $item = db_fetch('SELECT id, name, unit, unit_price, current_stock, reorder_level, notes, last_updated FROM ingredients WHERE id = ?', [$newId]);
            echo json_encode(['success' => true, 'item' => $item]);
            exit;
        }
    }

    if ($action === 'delete_item') {
        $id = isset($input['id']) ? (int)$input['id'] : 0;
        if (!$id) throw new Exception('Missing id');
        db_execute('DELETE FROM ingredients WHERE id = ?', [$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'adjust') {
        $ingredient_id = isset($input['ingredient_id']) ? (int)$input['ingredient_id'] : 0;
        $type = $input['type'] ?? '';
        $quantity = isset($input['quantity']) ? (float)$input['quantity'] : 0.0;
        $reference = $input['reference'] ?? null;

        if (!$ingredient_id || !$type || $quantity <= 0) throw new Exception('Invalid adjustment data');

        // Compute signed quantity
        if ($type === 'IN') {
            $signed = $quantity;
        } elseif ($type === 'OUT') {
            $signed = -abs($quantity);
        } else {
            // ADJUST can be positive or negative via a signed quantity param; here we accept quantity as signed when type=ADJUST
            $signed = isset($input['signed']) ? (float)$input['signed'] : $quantity;
        }

        db_begin_transaction();
        try {
            db_execute('INSERT INTO inventory_movements (ingredient_id, type, quantity, reference) VALUES (?, ?, ?, ?)', [$ingredient_id, $type, $signed, $reference]);
            // Update current_stock
            db_execute('UPDATE ingredients SET current_stock = GREATEST(0, current_stock + ?) WHERE id = ?', [$signed, $ingredient_id]);
            db_commit();
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            db_rollback();
            throw $e;
        }
    }

    throw new Exception('Unknown action');
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

?>
