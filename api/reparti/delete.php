<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';

$conn = getDbConnection();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    jsonResponse(['success' => false, 'message' => "ID del reparto è obbligatorio"], 400);
}

// Prima, aggiorna tutti gli item associati a questo reparto impostando reparto_id a NULL
$stmt = $conn->prepare("UPDATE items SET reparto_id = NULL WHERE reparto_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Ora, elimina il reparto
$stmt = $conn->prepare("DELETE FROM reparti WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        jsonResponse(['success' => true, 'message' => 'Reparto eliminato con successo']);
    } else {
        jsonResponse(['success' => false, 'message' => "Nessun reparto trovato con l'ID fornito"], 404);
    }
} else {
    jsonResponse(['success' => false, 'message' => "Errore nell'eliminazione del reparto: " . $stmt->error], 500);
}