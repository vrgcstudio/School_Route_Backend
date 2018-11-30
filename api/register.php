<?php
require_once(__DIR__ . '/../loader.php');


$request = json_decode(file_get_contents('php://input'));

if(!is_object($request)){
    echo json_encode('4');
    http_response_code(400); exit;
}

// user

$user =new stdClass();
$user->id = 0;
$user->email = $request->email; 
$user->password = password_hash($request->password,PASSWORD_BCRYPT); 
$user->status = $request->status; 
$userID = db_insert_record('main',$user);
if(!$userID){
    http_response_code(500); exit;
}
echo json_encode($userID);

// student

if($request->status=='student'){
    $studentNewUser = new stdClass();
    $studentNewUser->id_stu =0;
    $studentNewUser->main_id = $user->id;
    $studentNewUser->email_stu = $request->email_stu;
    $studentNewUser->first_name_stu = $request->first_name_stu;
    $studentNewUser->last_name_stu = $request->last_name_stu;
    $studentNewUser->gender_stu = $request->gender_stu;
    $studentNewUser->db_stu = $request->db_stu;
    $studentNewUser->address = $request->address;
    $studentNewUser->lat = $request->lat;
    $studentNewUser->lon = $request->lon;
    $studentNewUser->sick_stu = $request->sick_stu;
    $studentNewUser->school = $request->school;
    $studentNewUser->tel_stu = $request->tel_stu;
    
    $stuID = db_insert_record('students',$studentNewUser);
    if(!$stuID){
        http_response_code(500); exit;
    }
    echo json_encode($userID);
}

// parent

if($request->status=='parent'){
    $parentNewUser = new stdClass();
    $parentNewUser->id_par = 0 ;
    $parentNewUser->main_id = $user->id ;
    $parentNewUser->email_par = $request->email_par;
    $parentNewUser->first_name_par = $request->first_name_par;
    $parentNewUser->last_name_par = $request->last_name_par;
    $parentNewUser->gender_par = $request->gender_par;
    $parentNewUser->relation = $request->relation;
    $parentNewUser->tel_par = $request->tel_par;
    
    $parID = db_insert_record('parents',$parentNewUser);
    if(!$parID){
        http_response_code(500); exit;
    }
    echo json_encode($parID);
}

// driver

if($request->status=='driver'){
    $driverNewUser = new stdClass();
    $driverNewUser->id_dri = 0;
    $driverNewUser->main_id = $user->id ;
    $driverNewUser->email_dri = $request->emaildrir;
    $driverNewUser->first_name_dri = $request->first_name_dri;
    $driverNewUser->last_name_dri = $request->last_name_dri;
    $driverNewUser->bd_dri = $request->bd_dri;
    $driverNewUser->gender_dri = $request->gender_dri;
    $driverNewUser->id_card_dri = $request->id_card_dri;
    $driverNewUser->tel_dri = $request->tel_dri;
    
    $driID = db_insert_record('drivers',$driverNewUser);
    if(!$driID){
        http_response_code(500); exit;
    }
    echo json_encode($driID);

    // van

    $van = new stdClass();
    $van->id_van = 0;
    $van->ownwe_van = $request->$owner_van;  
    $van->car_number = $request->$car_number;  
    $van->color = $request->$color;  
    $van->seats = $request->$seats;  
    $van->brand = $request->$brand;  
    $van->lat = $request->$lat;  
    $van->lon = $request->$lon;  
    $van->detail = $request->$detail;

    $vanID = db_insert_record('van',$van);
    if(!$vanID){
        http_response_code(500); exit;
    }
    echo json_encode($vanID);
}