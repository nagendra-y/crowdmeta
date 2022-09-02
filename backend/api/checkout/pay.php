<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");

include_once '../../vendor/autoload.php';

// get database connection
include_once '../config/database.php';

// instantiate product object
include_once '../objects/customer.php';


use Razorpay\Api\Api;

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);

// get posted data
$data = json_decode(file_get_contents("php://input"));

//Use env vars
$rpay_key_id = "xxx"; 
$rpay_secret = "xxx";
$api = new Api($rpay_key_id, $rpay_secret);

// make sure data is not empty
if(
		!empty($data->name) &&
		!empty($data->email) &&
		!empty($data->phone) &&
		!empty($data->address) &&
		!empty($data->amount) 
){
		// set customer property values
		$customer->name = $data->name;
		$customer->email = $data->email;
		$customer->phone = $data->phone;
		$customer->address = $data->address;
		$customer->message = "msg"; 
		$customer->amount = $data->amount;

		$cid = random_int(100000, 999999);
		$customer->id = $cid;
		if($data->photo_type){
				$customer->photo = $cid . "." . $data->photo_type;
				$customer->photo_type = $data->photo_type;
		}

		$date = new DateTime();
		$customer->created = $date->format('Y-m-d H:i:s');
		$customer->ts = $date->format(DateTime::ISO8601); 

		// if unable to create the customer, tell the user
		if(!$customer->create()){

				// set response code - 503 service unavailable
				http_response_code(503);

				// tell the user
				echo json_encode(array("message" => "Unable to initiate payment. Customer creation failed"));
				return;
		}

		$rid = random_int(100000, 999999);
		$amount = $data->amount * 100; 
		//Razorpay decimal format. Example 5550 = 55.50

		$receipt = 'offline_order_rcptid_'.$rid;

		$receipt = 'online_order_rcptid_'.$rid;
		$order  = $api->order->create([
				'receipt' => $receipt, 
				'amount'  => $amount,
				'currency' => 'INR'
		]);

		//TODO:Validate data before placing in db
		//Store order detail in SQL database

		// set response code - 200 Order created
		http_response_code(200);
		$order_arr = array(
				"key" => $rpay_key_id,
				"currency"=> $order->currency,
				"id" => $order->id,
				"amount" => $order->amount/100,
				"rid" => $order->receipt,
				"customer_name" => $data->name,
				"customer_email" => $data->email,
				"customer_phone" => $data->phone

		);

		echo json_encode(array("message" => "Payment complete by customer.", "order"=> $order_arr, "cid"=> $customer->id, 
				"photo"=> $customer->photo, "uploadUrl"=> $customer->uploadUrl, "uploadType"=> $customer->photo_type));
		//echo json_encode($order_arr);

}

// tell the user data is incomplete
else{

		// set response code - 400 bad request
		http_response_code(400);

		// tell the user
		echo json_encode(array("message" => "Unable to complete payment."));
}
?>
