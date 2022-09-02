<?php
class Profile{
  
    // database connection and table name
    private $conn;
    private $table_name = "profiles";
  
    // object properties
    public $id;
    public $name;
    public $email;
    public $phone;
    public $about;
    public $address;
    public $price;
  
    // constructor with $db as database connection
    public function __construct($db){
        $this->conn = $db;
    }

    // create profile
    function create(){

	    // query to insert record
	    $query = "INSERT INTO
		    " . $this->table_name . "
		    SET
		    sid=:sid, name=:name, email=:email";

	    // prepare query
	    $stmt = $this->conn->prepare($query);

	    // sanitize
	    $this->id =htmlspecialchars(strip_tags($this->id));
	    $this->name=htmlspecialchars(strip_tags($this->name));
	    $this->email =htmlspecialchars(strip_tags($this->email));

	    // bind values
	    $stmt->bindParam(":id", $this->id);
	    $stmt->bindParam(":name", $this->name);
	    $stmt->bindParam(":email", $this->email);

	    // execute query
	    if($stmt->execute()){
		    return true;
	    }

	    return false;
    }

    function readProfile(){
	    $email = $this->email;
	    $query = "SELECT *
		    FROM
		    " . $this->table_name . " 
		    WHERE email = ?" ; 

	    // prepare query statement
	    $stmt = $this->conn->prepare($query);
	    $stmt->bindParam(1, $this->email);
	    
	    // execute query
	    $stmt->execute();

	    // get retrieved row
	    $row = $stmt->fetch(PDO::FETCH_ASSOC);
	    
	    // set values to object properties
	    $this->name = $row['name'];
	    $this->email = $row['email'];
	    $this->id = $row['id'];

	    return $stmt;
    }

}
?>
