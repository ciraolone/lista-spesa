<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';

$conn = getDbConnection();

$data = json_decode(file_get_contents('php://input'), true);
$nome = sanitizeInput($data['nome'] ?? '');

if (empty($nome)) {
    jsonResponse(['success' => false, 'message' => "Il nome del reparto è obbligatorio"], 400);
}

$stmt = $conn->prepare("INSERT INTO reparti (nome) VALUES (?)");
$stmt->bind_param("s", $nome);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    jsonResponse(['success' => true, 'message' => 'Reparto aggiunto con successo', 'id' => $newId]);
} else {
    jsonResponse(['success' => false, 'message' => "Errore nell'aggiunta del reparto: " . $stmt->error], 500);
}