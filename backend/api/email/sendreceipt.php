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

require '../../vendor/autoload.php'; // If you're using Composer (recommended)
// Comment out the above line if not using Composer
// require("<PATH TO>/sendgrid-php.php");
// If not using Composer, uncomment the above line and
// download sendgrid-php.zip from the latest release here,
// replacing <PATH TO> with the path to the sendgrid-php.php file,
// which is included in the download:
// https://github.com/sendgrid/sendgrid-php/releases
use Google\Cloud\Storage\StorageClient;

use SendGrid\Mail\From;
use SendGrid\Mail\To;
use SendGrid\Mail\Mail;

$database = new Database();
$db = $database->getConnection();

$order = new Customer($db); //This should be new Order

// get posted data
$data = json_decode(file_get_contents("php://input"));


// make sure data is not empty
// Ideally reward/order description should also be read from database
// Currently angular is feeding to reward descriptions
// But, we should fetch those things also from db. This will be imprtant when
// we move to a self use platform
if(
		!empty($data->oid) &&
		!empty($data->description) 
){
		// Read order from order-ID(oid)
		// From that, get customer-ID(cid) and then read customer details

		// set ID property of order to read
		$order->id = $data->oid;
		$order = $order->readOne();
		if(!$order){
				// set response code - 400 bad request
				http_response_code(400);
				echo json_encode(array("message" => "Unable to find order"));
				return;
		}

		$from = new From("support@nagendrawhy.com", "Crowdmeta");

		$order_data = array (
				'user' => 
				array (
						'firstName' => $order->name, //Customer name
						'lastname' => 'Last name',
				),
				'order' => 
				array (
						'id' => $order->id,
						'type' => 'reward',
						'amount' => $order->amount,
						'date' => 
						array (
								'timeStamp' => '2021-09-05T23:00:00.000Z',
								'dateFormat' => 'MMMM DD, YYYY hh:mm A',
								'timezoneOffset' => '-0800',
						),
						'edd' => 'November, 2021',
						'description' => $data->description, 
				),
				'campaign' => 
				array ( //Get this also from DB from stats table
						'name' => 'Hampi - A Golden Era',
						'creator' => 'Vishwanath Suvarna',
						'target' => 300000,
				),
		);

		$tos = [
				new To(
						$order->email,
						$order->name, 
						$order_data
				)
		];

		$email = new Mail(
				$from,
				$tos
		);
		$email->setTemplateId("d-6707e9ee98754822a95e5be4177117e9");
		
		//error_log(print_r(getenv('SENDGRID_API_KEY'), TRUE), 3, '/var/log/php_errors.log');

		$sendgrid_key = getenv('SENDGRID_API_KEY');
		$sendgrid = new \SendGrid($sendgrid_key);

		/**************** GET CERTIFICATE FROM GCLOUD**********/

		//Google Cloud Storage creds
		$projectId = 'crowdmeta';
		$home_dir = $_SERVER['HOME'];
		$storage = new StorageClient([
				'keyFilePath' => $home_dir. '/creds/crowdmeta-c7a67965dca5.json',
				'projectId' => $projectId
		]);
		
		$bucket_name = "crowdmeta-users";
		$storage->registerStreamWrapper();
		$contents = file_get_contents('gs://'.$bucket_name.'/'.$order->photo);

		/***************************************************/
		
		if($contents){
				//Add that as an attachment
				$file_encoded = base64_encode($contents);
				$email->addAttachment(
						$file_encoded,
						"image/png",
						"certificate.png",
						"attachment"
				);
		}

		//error_log(print_r($order, TRUE), 3, '/var/log/php_errors.log');
		//error_log(print_r($order_data, TRUE), 3, '/var/log/php_errors.log');

		try {
				$response = $sendgrid->send($email);
				
				//error_log(print_r($response->statusCode(), TRUE), 3, '/var/log/php_errors.log');
				//error_log(print_r($response->headers(), TRUE), 3, '/var/log/php_errors.log');
				//error_log(print_r($response->body(), TRUE), 3, '/var/log/php_errors.log');
				http_response_code(200);
				echo json_encode(array("message" => "Sent email!", "sgstatus"=> $response->statusCode()));
		} catch (Exception $e) {
				echo json_encode(array("message" => "Unable to send email!"));
		}
}



