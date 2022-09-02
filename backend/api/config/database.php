<?php
class Database{
  
    // specify your own database credentials
    private $host = "localhost";
    private $db_name = "vv_db";
    private $username = "test";
    private $password = "testpass";
    public $conn;
  
    // get the database connection
    public function getConnection(){
  
        $this->conn = null;
  
        try{
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
	    $this->conn->exec("set names utf8");
	    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	
	}catch(PDOException $exception){
		echo json_encode(
		    array("pdo error" => $exception->getMessage())
	    );
	}
  
        return $this->conn;
    }
}
?>
