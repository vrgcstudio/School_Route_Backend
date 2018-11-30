<?php
require_once(__DIR__ . '/config.php');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Token');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

$CONNECTION = mysqli_connect($CONFIG['host'], $CONFIG['username'], $CONFIG['password'], $CONFIG['database']);
if (!$CONNECTION) {
    http_response_code(500);
    exit;
}

require_once(__DIR__ . '/db.php');
require_once(__DIR__ . '/functions.php');
db_sql('SET CHARSET utf8mb4');

db_sql('DELETE FROM token WHERE expiration < ? ', [time()]);
