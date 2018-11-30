<?php
require_once(__DIR__ . '/../loader.php');
require_auth();

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit;
}

$id = $_GET['id'];

if (!db_delete_record('items', ['id' => $id])) {
    http_response_code(500);
    exit;
}
