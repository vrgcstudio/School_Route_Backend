<?php
require_once(__DIR__ . '/../loader.php');
require_auth();

$request = json_decode(file_get_contents('php://input'));
if (!$request || !is_object($request) || !isset($request->name)
    || !isset($request->price) || !is_numeric($request->price)) {
    http_response_code(400); exit;
}

$item = new stdClass();
$item->id = 0;
$item->name = $request->name;
$item->price = $request->price;

$newID = db_insert_record('items', $item);
if (!$newID) {
    http_response_code(500);
    exit;
}

echo json_encode(['id' => $newID]);
