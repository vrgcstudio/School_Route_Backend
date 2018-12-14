<?php require_once(__DIR__ . '/../loader.php');

$request = json_decode(file_get_contents('php://input'));
if (!$request || !isset($request->email) || !isset($request->password)) {
    http_response_code(400);
    exit;
}

$user = db_get_record('main', ['email' => $request->email]);
if (!$user) {
    http_response_code(403);
    exit;
}

if (!password_verify($request->password, $user->password)) {
    http_response_code(403);
    exit;
}

do {
    $token = generate_token();
} while (db_num_rows('tokens', ['token' => $token]) > 0);

$tokenObj = new stdClass();
$tokenObj->id = 0;
$tokenObj->user = $user->id;
$tokenObj->token = $token;
$tokenObj->expiration = time() + 600;
if (!db_insert_record('tokens', $tokenObj)) {
    http_response_code(500);
    exit;
}


unset($user->password);
$user->token = $token;

echo json_encode($user);
