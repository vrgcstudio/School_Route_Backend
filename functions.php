<?php

const CHARACTER = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

function generate_token($size = 128) {
    $token = '';
    $max = strlen(CHARACTER) - 1;
    for($i=0; $i < $size; $i++)
    {
        $token .= CHARACTER[rand(0, $max)];
    }

    return $token;
}

function validate_token($token) {
    $tokenObj = db_get_record('tokens', ['token' => $token]);
    if (!$tokenObj) {
        return null;
    }

    return db_get_record('users', ['id' => $tokenObj->user]);
}

function require_auth() {
    if (!isset($_SERVER['HTTP_TOKEN'])) {
        http_response_code(401);
        exit;
    }

    $token = $_SERVER['HTTP_TOKEN'];
    if (!validate_token($token)) {
        http_response_code(403);
        exit;
    }
}
