<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../functions.php';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        require_once __DIR__ . '/items/read.php';
        break;
    case 'POST':
        require_once __DIR__ . '/items/create.php';
        break;
    case 'PUT':
        require_once __DIR__ . '/items/update.php';
        break;
    case 'DELETE':
        require_once __DIR__ . '/items/delete.php';
        break;
    default:
        jsonResponse(['success' => false, 'message' => "Method not allowed"], 405);
}