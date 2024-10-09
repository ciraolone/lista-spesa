<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/utils.php';

$conn = getDbConnection();

$itemName = sanitizeInput($_POST['itemName'] ?? '');
if (empty($itemName)) {
    jsonResponse(['success' => false, 'message' => "Item name is required"], 400);
}

$extractedData = extractQuantityFromName($itemName);
$itemName = $extractedData['name'];
$quantity = $extractedData['quantity'];

$normalizedName = normalizeItemName($itemName);

$stmt = $conn->prepare("SELECT id, name, quantity, acquistato, reparto_id FROM items WHERE LOWER(REPLACE(REPLACE(name, ' ', ''), ',', '')) = ?");
$stmt->bind_param("s", $normalizedName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $item = $result->fetch_assoc();
    $newQuantity = $item['acquistato'] ? $quantity : $item['quantity'] + $quantity;
    $stmt = $conn->prepare("UPDATE items SET name = ?, quantity = ?, acquistato = 0, data_da_acquistare = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->bind_param("sii", $itemName, $newQuantity, $item['id']);
    $stmt->execute();
    
    $updatedItem = [
        'id' => $item['id'],
        'name' => $itemName,
        'quantity' => $newQuantity,
        'acquistato' => false,
        'ultimo_acquisto' => null,
        'data_da_acquistare' => date('Y-m-d H:i:s'),
        'reparto_id' => $item['reparto_id']
    ];
    jsonResponse(['success' => true, 'message' => 'Item updated', 'item' => $updatedItem]);
} else {
    $stmt = $conn->prepare("INSERT INTO items (name, quantity, data_da_acquistare) VALUES (?, ?, CURRENT_TIMESTAMP)");
    $stmt->bind_param("si", $itemName, $quantity);
    
    if ($stmt->execute()) {
        $id = $stmt->insert_id;
        $newItem = [
            'id' => $id,
            'name' => $itemName,
            'quantity' => $quantity,
            'acquistato' => false,
            'ultimo_acquisto' => null,
            'data_da_acquistare' => date('Y-m-d H:i:s'),
            'reparto_id' => null
        ];
        jsonResponse(['success' => true, 'message' => 'Item added successfully', 'item' => $newItem]);
    } else {
        jsonResponse(['success' => false, 'message' => "Error adding item: " . $stmt->error], 500);
    }
}