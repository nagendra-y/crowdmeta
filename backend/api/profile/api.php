<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/profile.php';

// get database connection
$database = new Database();
$db = $database->getConnection();

// prepare profile object
$profile = new Profile($db);

$op = isset($_GET['op']) ? $_GET['op'] : die();

if("getprofile" == $op){
	// set email property of record to read email
	$profile->email = isset($_GET['email']) ? $_GET['email'] : die();

	// read the details of profile to be edited
	$profile->readProfile();

	//if(null == $profile->sid)
	//	$profile->create();

	// create array
	$profile_arr = array(
		"sid" =>  $profile->sid,
		"name" => $profile->name,
		"email" => $profile->email
	);

	// set response code - 200 OK
	http_response_code(200);

	// make it json format
	echo json_encode($profile_arr);

}

//This API never fails
?>
