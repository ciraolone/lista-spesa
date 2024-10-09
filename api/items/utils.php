<?php
function normalizeItemName($name) {
    return preg_replace('/[^a-z0-9]/', '', strtolower($name));
}

function extractQuantityFromName($name) {
    $quantity = 1;
    $pattern = '/^\s*x\s*(\d+)\s*(.+)$|^(.+?)\s*x\s*(\d+)\s*$/i';
    if (preg_match($pattern, $name, $matches)) {
        if (!empty($matches[1])) {
            $quantity = intval($matches[1]);
            $name = trim($matches[2]);
        } else {
            $quantity = intval($matches[4]);
            $name = trim($matches[3]);
        }
    }
    return ['name' => $name, 'quantity' => $quantity];
}