<?php
// api/items/update.php

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/utils.php';

$conn = getDbConnection();

$input = json_decode(file_get_contents('php://input'), true);
$itemId = $input['id'] ?? null;
$action = $input['action'] ?? null;

if (!$itemId || !in_array($action, ['increase', 'decrease', 'toggleAcquistato', 'delete', 'assignReparto'])) {
    jsonResponse(['success' => false, 'message' => "Invalid request data"], 400);
}

if ($action === 'delete') {
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Item deleted successfully']);
    } else {
        jsonResponse(['success' => false, 'message' => "Error deleting item"], 500);
    }
} elseif ($action === 'toggleAcquistato') {
    $stmt = $conn->prepare("SELECT acquistato, name FROM items WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $newAcquistatoValue = $item['acquistato'] == 1 ? 0 : 1;
    
    if ($newAcquistatoValue == 1) {
        $stmt = $conn->prepare("UPDATE items SET acquistato = ?, quantity = 1, ultimo_acquisto = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ii", $newAcquistatoValue, $itemId);
    } else {
        $stmt = $conn->prepare("UPDATE items SET acquistato = ?, data_da_acquistare = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ii", $newAcquistatoValue, $itemId);
    }
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        jsonResponse(['success' => false, 'message' => "Item not found"], 404);
    }

    $result = $conn->query("SELECT i.id, i.name, i.quantity, i.acquistato, i.ultimo_acquisto, i.data_da_acquistare, r.id as reparto_id, r.nome as reparto_nome 
                            FROM items i 
                            LEFT JOIN reparti r ON i.reparto_id = r.id 
                            WHERE i.id = $itemId");
    $updatedItem = $result->fetch_assoc();
    $updatedItem['acquistato'] = $updatedItem['acquistato'] == 1;
    jsonResponse(['success' => true, 'message' => 'Item status updated', 'item' => $updatedItem]);
} elseif ($action === 'decrease') {
    $stmt = $conn->prepare("UPDATE items SET quantity = GREATEST(quantity - 1, 1) WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        jsonResponse(['success' => false, 'message' => "Item not found"], 404);
    }

    $result = $conn->query("SELECT i.id, i.name, i.quantity, i.acquistato, r.id as reparto_id, r.nome as reparto_nome 
                            FROM items i 
                            LEFT JOIN reparti r ON i.reparto_id = r.id 
                            WHERE i.id = $itemId");
    $updatedItem = $result->fetch_assoc();
    $updatedItem['acquistato'] = $updatedItem['acquistato'] == 1;

    jsonResponse(['success' => true, 'message' => 'Quantity updated', 'item' => $updatedItem]);
} elseif ($action === 'increase') {
    $stmt = $conn->prepare("UPDATE items SET quantity = quantity + 1 WHERE id = ?");
    $stmt->bind_param("i", $itemId);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $result = $conn->query("SELECT i.id, i.name, i.quantity, i.acquistato, r.id as reparto_id, r.nome as reparto_nome 
                                FROM items i 
                                LEFT JOIN reparti r ON i.reparto_id = r.id 
                                WHERE i.id = $itemId");
        $updatedItem = $result->fetch_assoc();
        $updatedItem['acquistato'] = $updatedItem['acquistato'] == 1;
        jsonResponse(['success' => true, 'message' => 'Quantity updated', 'item' => $updatedItem]);
    } else {
        jsonResponse(['success' => false, 'message' => "Item not found"], 404);
    }
} elseif ($action === 'assignReparto') {
    $repartoId = $input['repartoId'] ?? null;
    
    if ($repartoId === null) {
        jsonResponse(['success' => false, 'message' => "Reparto ID is required"], 400);
    }

    $stmt = $conn->prepare("UPDATE items SET reparto_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $repartoId, $itemId);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $result = $conn->query("SELECT i.id, i.name, i.quantity, i.acquistato, r.id as reparto_id, r.nome as reparto_nome 
                                    FROM items i 
                                    LEFT JOIN reparti r ON i.reparto_id = r.id 
                                    WHERE i.id = $itemId");
            $updatedItem = $result->fetch_assoc();
            $updatedItem['acquistato'] = $updatedItem['acquistato'] == 1;
            jsonResponse(['success' => true, 'message' => 'Reparto assigned successfully', 'item' => $updatedItem]);
        } else {
            jsonResponse(['success' => false, 'message' => "Item not found"], 404);
        }
    } else {
        jsonResponse(['success' => false, 'message' => "Error assigning reparto"], 500);
    }
}