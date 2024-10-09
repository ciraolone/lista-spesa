<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';
require_once __DIR__ . '/../../functions.php';

$conn = getDbConnection();

$result = $conn->query("SELECT * FROM reparti ORDER BY ordinamento, nome");
$reparti = $result->fetch_all(MYSQLI_ASSOC);

jsonResponse(['success' => true, 'reparti' => $reparti]);