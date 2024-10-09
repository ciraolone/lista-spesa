<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../functions.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log function
function logMessage($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/../debug.log');
}

logMessage("Request received: " . $_SERVER['REQUEST_METHOD']);

$conn = getDbConnection();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $result = $conn->query("SELECT * FROM reparti ORDER BY ordinamento, nome");
        $reparti = $result->fetch_all(MYSQLI_ASSOC);
        jsonResponse(['success' => true, 'reparti' => $reparti]);
        break;

    case 'POST':
        logMessage("POST data received: " . print_r($_POST, true));
        $nome = sanitizeInput($_POST['nome'] ?? '');
        logMessage("Sanitized nome: " . $nome);
        if (empty($nome)) {
            logMessage("Error: Empty nome");
            jsonResponse(['success' => false, 'message' => "Il nome del reparto è obbligatorio"], 400);
        }

        // Get the maximum ordinamento value
        $result = $conn->query("SELECT MAX(ordinamento) as max_ord FROM reparti");
        $row = $result->fetch_assoc();
        $newOrdinamento = ($row['max_ord'] !== null) ? $row['max_ord'] + 1 : 1;

        $stmt = $conn->prepare("INSERT INTO reparti (nome, ordinamento) VALUES (?, ?)");
        if (!$stmt) {
            logMessage("Error in prepare statement: " . $conn->error);
            jsonResponse(['success' => false, 'message' => "Errore nella preparazione della query: " . $conn->error], 500);
        }
        $stmt->bind_param("si", $nome, $newOrdinamento);

        logMessage("Executing statement");
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            logMessage("Reparto added successfully. New ID: " . $newId);
            jsonResponse(['success' => true, 'message' => 'Reparto aggiunto con successo', 'id' => $newId]);
        } else {
            logMessage("Error in execute: " . $stmt->error);
            jsonResponse(['success' => false, 'message' => "Errore nell'aggiunta del reparto: " . $stmt->error], 500);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['action']) && $data['action'] === 'updateOrder') {
            $newOrder = $data['newOrder'];
            $conn->begin_transaction();
            try {
                foreach ($newOrder as $index => $id) {
                    $stmt = $conn->prepare("UPDATE reparti SET ordinamento = ? WHERE id = ?");
                    $ordinamento = $index + 1;
                    $stmt->bind_param("ii", $ordinamento, $id);
                    $stmt->execute();
                }
                $conn->commit();
                jsonResponse(['success' => true, 'message' => 'Ordine dei reparti aggiornato con successo']);
            } catch (Exception $e) {
                $conn->rollback();
                jsonResponse(['success' => false, 'message' => "Errore nell'aggiornamento dell'ordine dei reparti: " . $e->getMessage()], 500);
            }
        } else {
            jsonResponse(['success' => false, 'message' => "Azione non valida"], 400);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            jsonResponse(['success' => false, 'message' => "ID del reparto è obbligatorio"], 400);
        }

        $conn->begin_transaction();
        try {
            // Get the ordinamento of the reparto to be deleted
            $stmt = $conn->prepare("SELECT ordinamento FROM reparti WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $reparto = $result->fetch_assoc();
            $deletedOrdinamento = $reparto['ordinamento'];

            // Delete the reparto
            $stmt = $conn->prepare("DELETE FROM reparti WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Update items associated with this reparto
            $stmt = $conn->prepare("UPDATE items SET reparto_id = NULL WHERE reparto_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            // Shift the ordinamento of other reparti
            $stmt = $conn->prepare("UPDATE reparti SET ordinamento = ordinamento - 1 WHERE ordinamento > ?");
            $stmt->bind_param("i", $deletedOrdinamento);
            $stmt->execute();

            $conn->commit();
            jsonResponse(['success' => true, 'message' => 'Reparto eliminato con successo']);
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(['success' => false, 'message' => "Errore nell'eliminazione del reparto: " . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => "Metodo non consentito"], 405);
}