<?php

include_once '../storage/bucket.php';

class Customer{

		// database connection and table name
		private $conn;
		private $table_name = "customers";

		// object properties
		public $id;
		public $name;
		public $email;
		public $phone;
		public $address;
		public $photo;
		public $photo_type;
		public $message;
		public $amount;
		public $created;
		public $ts;
		public $uploadUrl;

		// constructor with $db as database connection
		public function __construct($db){
				$this->conn = $db;
		}

		// create customer 
		function create(){

				// query to insert record
				$query = "INSERT INTO
						" . $this->table_name . "
						SET
						id=:id, name=:name, email=:email, phone=:phone, address=:address, photo=:photo, message=:message, amount=:amount, created=:created, ts=:ts";

				// prepare query
				$stmt = $this->conn->prepare($query);

				// sanitize
				$this->name=htmlspecialchars(strip_tags($this->name));
				$this->email =htmlspecialchars(strip_tags($this->email));
				$this->phone =htmlspecialchars(strip_tags($this->phone));
				$this->address =htmlspecialchars(strip_tags($this->address));
				$this->photo =htmlspecialchars(strip_tags($this->photo));
				$this->message =htmlspecialchars(strip_tags($this->message));
				$this->created =htmlspecialchars(strip_tags($this->created));
				$this->ts =htmlspecialchars(strip_tags($this->ts));

				// bind values
				$stmt->bindParam(":id", $this->id);
				$stmt->bindParam(":name", $this->name);
				$stmt->bindParam(":email", $this->email);
				$stmt->bindParam(":phone", $this->phone);
				$stmt->bindParam(":address", $this->address);
				$stmt->bindParam(":photo", $this->photo);
				$stmt->bindParam(":message", $this->message);
				$stmt->bindParam(":amount", $this->amount);
				$stmt->bindParam(":created", $this->created);
				$stmt->bindParam(":ts", $this->ts);

				if($this->photo){
						$this->uploadUrl = upload_object_v4_signed_url("crowdmeta-users", $this->photo, "image/".$this->photo_type);
						//Not storing upload URL. Because it is temporary.
				}

				// execute query
				if($stmt->execute()){
						return true;
				}

				return false;
		}

		function readOne(){
				$query = "SELECT * FROM " . $this->table_name . " WHERE id=". $this->id .";"; 

				// prepare query statement
				$stmt = $this->conn->prepare($query);

				// execute query
				$stmt->execute();
				
				// get retrieved row
				$row = $stmt->fetch(PDO::FETCH_ASSOC);	
				
				if(!$row)
						return null;

        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->phone = $row['phone'];
        $this->address = $row['address'];
        $this->photo = $row['photo'];
        $this->amount = $row['amount'];
        $this->message = $row['message'];
        $this->created = $row['created'];
        $this->ts = $row['ts'];
				
				return $this;
		}



		function readAll(){
				$query = "SELECT * FROM " . $this->table_name . ";"; 

				// prepare query statement
				$stmt = $this->conn->prepare($query);

				// execute query
				$stmt->execute();
				
				return $stmt;
		}

		// used for paging list of supporters 
		public function count(){
				$query = "SELECT COUNT(*) as total_rows FROM " . $this->table_name . "";

				$stmt = $this->conn->prepare( $query );
				$stmt->execute();
				$row = $stmt->fetch(PDO::FETCH_ASSOC);

				return $row['total_rows'];
		}

}

?>
