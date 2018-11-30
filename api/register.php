<?php
require_once(__DIR__ . '/../loader.php');

$request = json_decode(file_get_contents('php://input'));

if(!is_object($request) || !isset($request->username)|| 
!isset($request->password)||!isset($request->name)){
    http_response_code(400); exit;

}

$user =new stdClass();
$user->username = $request->username; 
$user->password = password_hash($request->password,PASSWORD_BCRYPT); 
$user->name = $request->name; 

$userID = db_insert_record('users',$user);
if(!$userID){
    http_response_code(500); exit;
}

echo json_encode($userID);
