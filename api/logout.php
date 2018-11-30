<?php require_once(__DIR__ . '/../loader.php');
require_auth();

$token = $_SERVER['HTTP_TOKEN'];
db_delete_record('tokens', ['token' => $token]);
