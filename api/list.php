<?php
require_once(__DIR__ . '/../loader.php');
require_auth();

$items = db_get_records_array('items', [], 'id');

echo json_encode($items);
