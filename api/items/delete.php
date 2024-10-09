<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';

$conn = getDbConnection();

$input = json_decode(file_get_contents('php://input'), true);
$itemId = $input['id'] ?? null;

if (!$itemId) {
    jsonResponse(['success' => false, 'message' => "Invalid request data"], 400);
}

$stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
$stmt->bind_param("i", $itemId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    jsonResponse(['success' => true, 'message' => 'Item deleted', 'id' => $itemId]);
} else {
    jsonResponse(['success' => false, 'message' => "Item not found or could not be deleted"], 404);
}