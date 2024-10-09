<?php
require_once 'config.php';

/**
 * Stabilisce una connessione al database
 * 
 * @return mysqli La connessione al database
 * @throws Exception Se la connessione fallisce
 */
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connessione al database fallita: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}