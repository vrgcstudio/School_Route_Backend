<?php
require_once(__DIR__ . '/../loader.php');
require_auth();

$request = json_decode(file_get_contents('php://input'));
if (!$request || !is_object($request)) {
    http_response_code(400); exit;
}

$item = db_get_record('items', ['id' => $request->id]);
if (!$item) {
    http_response_code(404);
    exit;
}

$item->name = isset($request->name) ? $request->name : $item->name;
$item->price = isset($request->price) && is_numeric($request->price) ? $request->price : $item->price;

if (!db_update_records('items', $item)) {
    http_response_code(500);
    exit;
}
