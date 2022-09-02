<?php
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

$from = new From("support@nagendrawhy.com", "Crowdmeta");

$order_data = array (
  'user' => 
  array (
    'firstName' => 'John',
    'lastname' => 'Doe',
  ),
  'order' => 
  array (
    'id' => '868121123113',
    'type' => 'reward',
    'amount' => 5000,
    'date' => 
    array (
      'timeStamp' => '2021-09-05T23:00:00.000Z',
      'dateFormat' => 'MMMM DD, YYYY hh:mm A',
      'timezoneOffset' => '-0800',
    ),
    'edd' => 'November, 2021',
    'description' => 'One copy of the coffee-table book delivered to you at an incredible discounted launch price (Selling price of the book is ₹7500)',
  ),
  'campaign' => 
  array (
    'name' => 'Hampi - A Golden Era',
    'creator' => 'Vishwanath Suvarna',
    'target' => 300000,
  ),
);

$tos = [
    new To(
        "nagynv@gmail.com",
				"Test User", 
				$order_data
		)
];

$email = new Mail(
    $from,
    $tos
);
$email->setTemplateId("d-6707e9ee98754822a95e5be4177117e9");
$sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

/**************** GET CERTIFICATE FROM GCLOUD**********/

//Google Cloud Storage creds
$projectId = 'crowdmeta';
$home_dir = $_SERVER['HOME'];
$storage = new StorageClient([
		'keyFilePath' => $home_dir. '/creds/crowdmeta-c7a67965dca5.json',
		'projectId' => $projectId
]);

$storage->registerStreamWrapper();
$contents = file_get_contents('gs://crowdmeta-users/156195.png');

/***************************************************/

//Add that as an attachment
$file_encoded = base64_encode($contents);
$email->addAttachment(
    $file_encoded,
    "image/png",
    "certificate.png",
    "attachment"
);

try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '.  $e->getMessage(). "\n";
}

