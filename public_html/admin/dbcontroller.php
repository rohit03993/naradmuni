<?php
class DBController {
	private $host = "localhost";
	private $user = "thenaradmunicom_db";
	private $password = "RIzx63ZTUNeqx";
	private $database = "thenaradmunicom_db";
	private $conn;
	
	function __construct() {
		$this->conn = $this->connectDB();
	}
	
	function connectDB() {
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database);
        mysqli_set_charset($conn,"utf8");
		return $conn;
	}
	
	function runQuery($query) {
		$result = mysqli_query($this->conn,$query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}		
		if(!empty($resultset))
			return $resultset;
	}
	
	function numRows($query) {
		$result  = mysqli_query($this->conn,$query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;	
	}

	function escape($value) {
		return mysqli_real_escape_string($this->conn, $value);
	}
}
?>
