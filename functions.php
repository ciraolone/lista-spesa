<?php
/**
 * Sanitizza l'input dell'utente per prevenire XSS
 * 
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera una risposta JSON
 * 
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    header("Content-Type: application/json");
    http_response_code($statusCode);
    $jsonData = json_encode($data);
    if ($jsonData === false) {
        error_log("JSON encode error: " . json_last_error_msg());
        $jsonData = json_encode(['success' => false, 'message' => 'Error encoding JSON response']);
    }
    echo $jsonData;
    exit;
}