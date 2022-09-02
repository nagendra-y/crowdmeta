<?php
// required headers
header("Access-Control-Allow-Origin: http://localhost:80");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");

include_once '../../vendor/autoload.php';

// get database connection
include_once '../config/database.php';

// instantiate product object
include_once '../objects/product.php';

use Razorpay\Api\Api;

$database = new Database();
$db = $database->getConnection();

// get posted data
$data = json_decode(file_get_contents("php://input"));

//Use env vars
$rpay_key_id = "xxx"; 
$rpay_secret = "xxxx";
$api = new Api($rpay_key_id, $rpay_secret);

// make sure data is not empty
if(
		!empty($data->razorpay_payment_id) &&
		!empty($data->razorpay_order_id) &&
		!empty($data->razorpay_signature) 
){
		try{	
				$attributes  = array(
						'razorpay_signature'  => $data->razorpay_signature,  
						'razorpay_payment_id'  => $data->razorpay_payment_id ,  
						'razorpay_order_id' => $data->razorpay_order_id
				);

				$api->utility->verifyPaymentSignature($attributes);

				// After verifying the signature, 
				// fetch the order in your system that corresponds to the razorpay_order_id in your database. 
				// Mark it as successful and process the order.

				$order_status = "OK";
				$error = "";
		}
		catch(SignatureVerificationError $e){
				$order_status = "FAIL";
				$response = 'failure' ;       
				$error = 'Razorpay Error : ' . $e->getMessage();
		}

		$orderId = $data->razorpay_order_id;
		$order  = $api->order->fetch($orderId);
		// set response code - 200 Payment Complete 
		http_response_code(200);
		$order_arr = array(
				"status" => $order_status,
				"error" => $error,	
				"id" => $order->id,
				"amount" => $order->amount,
				"receipt" => $order->receipt,
		);
		echo json_encode($order_arr);

}

// tell the user data is incomplete
else{

		// set response code - 400 bad request
		http_response_code(400);

		// tell the user
		echo json_encode(array("message" => "Unable to complete payment."));
}
?>
