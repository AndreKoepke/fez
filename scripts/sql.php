<?php

require_once 'config.php';

class SQLConnection
{

	public $conn = null;

	function __construct() {
       	$this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);

		// Check connection
		if ($this->conn->connect_error) {
		    die("Connection failed: " . $this->conn->connect_error);
		    error_log("Connection failed: " . $this->conn->connect_error);
		} 

		$this->conn->query("SET NAMES 'utf8'");
   }

  	function __destruct() {
       $this->conn->close();
   }


	public function insertNewUser($id)
	{
		$sql = "INSERT INTO user (id) VALUES ('".$id."')";

		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}

		
	}


	public function getUserState($id)
	{

		$sql = "SELECT * FROM user WHERE id = '".$id."'";

		$result = $this->conn->query($sql);

		if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
		    return $row['state'];
	   	}
		else
		{
			return "new";
		}

		
	}


	public function getAllWithState($state)
	{
		$sql = "SELECT * FROM user WHERE state = '".$state."'";

		$i = 0;
		$result = $this->conn->query($sql);
		$results_array = array();

		while ($row = $result->fetch_assoc()) {
		  $results_array[$i] = $row;
		  $i++;
		}

		return $results_array;		
	}


	public function getAllWithStates($states)
	{
		$sql = "SELECT id FROM user WHERE";
		$arrlength = count($states);

  		for($x = 0; $x < $arrlength; $x++) {
  			$sql = $sql." state = '".$states[$x]."' OR";
  		}
  		$sql = substr($sql, 0, -3);


		$result = $this->conn->query($sql);
		$results_array = array();

		while ($row = $result->fetch_assoc()) {
		  $results_array[] = $row;
		}

		return $results_array;		
	}



	public function setUserState($id, $state)
	{
		$sql = "UPDATE user SET state = '".$state."' WHERE id = '".$id."'";


		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}
	}


	public function setUsername($id, $name)
	{
		$sql = "UPDATE user SET name = '".$name."' WHERE id = '".$id."'";


		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}
	}


	public function getUsername($id)
	{

		$sql = "SELECT * FROM user WHERE id = '".$id."'";

		$result = $this->conn->query($sql);

		if ($result->num_rows > 0) {
		    $row = $result->fetch_assoc();
		    return $row['name'];
	   	}
		else
		{
			return "unkown";
		}

		
	}


	public function setFunktion($id, $funktion)
	{
		$sql = "UPDATE user SET funktion = '".$funktion."' WHERE id = '".$id."'";


		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}
	}


	public function getState()
	{
		$sql = "SELECT * FROM state";

		$result = $this->conn->query($sql);

		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    return $row['state'];
	   	}
		else
		{
			return "notready";
		}
	}

	public function getLastChangeTimestamp()
	{
		$sql = "SELECT * FROM state";

		$result = $this->conn->query($sql);

		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    return new DateTime($row['changedAt']);
	   	}
		else
		{
			return "notready";
		}
	}


	public function setState($newState)
	{
		$sql = "UPDATE state SET state = '".$newState."'";


		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}
	}


	public function getLocation($id)
	{
		$sql = "SELECT * FROM user WHERE id = '".$id."'";

		$result = $this->conn->query($sql);

		if ($result->num_rows == 1) {
		    $row = $result->fetch_assoc();
		    return array('lat' => $row['lat'], 'lng' => $row['lng']);
	   	}
		else
		{
			return null;
		}
	}

	public function setLocation($id, $latlng)
	{
		$sql = "UPDATE user SET lat = '".$latlng['lat']."', lng = '".$latlng['lng']."'";


		if ($this->conn->query($sql) === TRUE) {
		    return true;
		} else {
		    error_log("Error: " . $sql . "		" . $this->conn->error);
		    return false;
		}
	}
}
