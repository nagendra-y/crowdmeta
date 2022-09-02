<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
include_once '../config/database.php';
include_once '../objects/customer.php';
  
  
// instantiate database and customer object
$database = new Database();
$db = $database->getConnection();
  
// initialize object
$customer = new Customer($db);
  
// query customers
$stmt = $customer->readAll();
$num = $stmt->rowCount();

// For sorting supporters based on their contribution
function cmp($a, $b) {
    return $a["amount"] > $b["amount"] ? -1: 1;
}



// check if more than 0 record found
if($num>0){
  
    // customers array
    $customers_data=array();
    $customers_data["supporters"]=array();
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only
        extract($row);
  
        $customer_item=array(
            "id" => $id,
            "name" => $name,
            "email" => $email,
            "photo" => $photo,
            "amount" => $amount,
            "created" => $created,
        );
  
        array_push($customers_data["supporters"], $customer_item);
    }
  
  
    // set response code - 200 OK
    http_response_code(200);
  
		usort($customers_data["supporters"], "cmp");
		
		// make it json format
    echo json_encode($customers_data);
}
  
else{
  
    // set response code - 300 FAIL 
    http_response_code(200);
  
    // tell the user customers does not exist
    echo json_encode(
        array("message" => "No supporters found.")
    );
}
?>
