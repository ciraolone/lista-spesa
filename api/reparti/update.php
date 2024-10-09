<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';

$conn = getDbConnection();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$nome = sanitizeInput($data['nome'] ?? '');

if (!$id || empty($nome)) {
    jsonResponse(['success' => false, 'message' => "ID e nome del reparto sono obbligatori"], 400);
}

$stmt = $conn->prepare("UPDATE reparti SET nome = ? WHERE id = ?");
$stmt->bind_param("si", $nome, $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        jsonResponse(['success' => true, 'message' => 'Reparto aggiornato con successo']);
    } else {
        jsonResponse(['success' => false, 'message' => "Nessun reparto trovato con l'ID fornito"], 404);
    }
} else {
    jsonResponse(['success' => false, 'message' => "Errore nell'aggiornamento del reparto: " . $stmt->error], 500);
}