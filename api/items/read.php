<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/utils.php';

$conn = getDbConnection();

// Modifica della query per includere le informazioni sul reparto
$result = $conn->query("SELECT i.id, i.name, i.quantity, i.acquistato, i.ultimo_acquisto, i.data_da_acquistare, r.id as reparto_id, r.nome as reparto_nome 
                        FROM items i 
                        LEFT JOIN reparti r ON i.reparto_id = r.id 
                        ORDER BY 
                            CASE 
                                WHEN i.acquistato = 1 THEN i.ultimo_acquisto 
                                ELSE i.data_da_acquistare 
                            END DESC");

$items = [];
while ($row = $result->fetch_assoc()) {
    $row['acquistato'] = $row['acquistato'] == 1;
    $items[] = $row;
}
jsonResponse(['success' => true, 'items' => $items]);