<?php
require_once(__DIR__ . '/../loader.php');
require_auth();

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit;
}

$id = $_GET['id'];

$item = db_get_record('items', ['id' => $id]);
if (!$item) {
    http_response_code(404);
    exit;
}

echo json_encode($item);
