<?php
// Configurazione del database
$isLocalEnvironment = false;

// Verifica se siamo in ambiente locale
if (isset($_SERVER['SERVER_NAME'])) {
    $isLocalEnvironment = ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1');
} elseif (isset($_SERVER['SERVER_ADDR'])) {
    $isLocalEnvironment = ($_SERVER['SERVER_ADDR'] == '127.0.0.1');
}

if ($isLocalEnvironment) {
    // Configurazione locale
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'lista_spesa');
} else {
    // Configurazione per l'hosting (Cloudways)
    define('DB_HOST', '***');
    define('DB_USER', '***');
    define('DB_PASS', '***');
    define('DB_NAME', '***');
}

// Impostazioni di debug
if ($isLocalEnvironment) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Impostazione del fuso orario
date_default_timezone_set('Europe/Rome');

// Costanti dell'applicazione
define('APP_NAME', 'Lista della Spesa');
define('APP_VERSION', '1.0.0');