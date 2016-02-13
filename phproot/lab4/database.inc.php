<?php
/*
 * Class Database: interface to the movie database from PHP.
 *
 * You must:
 *
 * 1) Change the function userExists so the SQL query is appropriate for your tables.
 * 2) Write more functions.
 *
 */
class Database {
	private $host;
	private $userName;
	private $password;
	private $database;
	private $conn;
	
	/**
	 * Constructs a database object for the specified user.
	 */
	public function __construct($host, $userName, $password, $database) {
		$this->host = $host;
		$this->userName = $userName;
		$this->password = $password;
		$this->database = $database;
	}
	
	/** 
	 * Opens a connection to the database, using the earlier specified user
	 * name and password.
	 *
	 * @return true if the connection succeeded, false if the connection 
	 * couldn't be opened or the supplied user name and password were not 
	 * recognized.
	 */
	public function openConnection() {
		try {
			$this->conn = new PDO("mysql:host=$this->host;dbname=$this->database", 
					$this->userName,  $this->password);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			$error = "Connection error: " . $e->getMessage();
			print $error . "<p>";
			unset($this->conn);
			return false;
		}
		return true;
	}
	
	/**
	 * Closes the connection to the database.
	 */
	public function closeConnection() {
		$this->conn = null;
		unset($this->conn);
	}

	/**
	 * Checks if the connection to the database has been established.
	 *
	 * @return true if the connection has been established
	 */
	public function isConnected() {
		return isset($this->conn);
	}
	
	/**
	 * Execute a database query (select).
	 *
	 * @param $query The query string (SQL), with ? placeholders for parameters
	 * @param $param Array with parameters 
	 * @return The result set
	 */
	private function executeQuery($query, $param = null) {
		try {
			$stmt = $this->conn->prepare($query);
			$stmt->execute($param);
			$result = $stmt->fetchAll();
		} catch (PDOException $e) {
			$error = "*** Internal error: " . $e->getMessage() . "<p>" . $query;
			die($error);
		}
		return $result;
	}
	
	/**
	 * Execute a database update (insert/delete/update).
	 *
	 * @param $query The query string (SQL), with ? placeholders for parameters
	 * @param $param Array with parameters 
	 * @return The number of affected rows
	 */
	private function executeUpdate($query, $param = null) {
		try {
			$stmt = $this->conn->prepare($query);
  			$stmt->execute($param);
  			$rows = $stmt->rowCount();
  		} catch (PDOException $e) {
			$error = "*** Internal error: " . $e->getMessage() . "<p>" . $query;
			die($error);
		}
		return $rows;
	}


	/**
	 * Check if a user with the specified user id exists in the database.
	 * Queries the Users database table.
	 *
	 * @param userId The user id
	 * @return true if the user exists, false otherwise.
	 */
	public function userExists($userId) {
		$sql = "select userName from Users where userName = ?";
		$result = $this->executeQuery($sql, array($userId));
		return count($result) == 1; 
	}

	/*
	 * *** Add functions ***
	 */

	public function getMovieNames() {
		$sql = "SELECT * FROM movies";
		$result = $this->executeQuery($sql);

		foreach ($result as $res) {
			$movieNames[] = $res["name"];
		}
        //echo '<pre>'; print_r($movieNames); echo '</pre>';
		return $movieNames;
	}
	

	public function getPerformanceDates($movieName) {
		$sql = "SELECT date FROM performances WHERE movieName = ?";
		$result = $this->executeQuery($sql, array($movieName));

		foreach ($result as $res) {
			$performanceDates[] = $res["date"];
		}
        //echo '<pre>'; print_r($performanceDates); echo '</pre>';
		return $performanceDates;
	}


	public function getPerformanceData($movieName, $date) {
		$sql = "SELECT movieName, date, theaterName, seatsLeft FROM performances WHERE movieName = ? AND  date = ?";
		$result = $this->executeQuery($sql, array($movieName, $date));
		
		// only get one performance, add a counter to check for results?

        foreach ($result as $res) {
			$movieName = $res["movieName"];
			$date = $res["date"];
			$theaterName = $res["theaterName"];
			$seatsLeft = $res["seatsLeft"];
        }
		
		$performance["movieName"] = $movieName;
		$performance["date"] = $date;
		$performance["theaterName"] = $theaterName;
		$performance["seatsLeft"] = $seatsLeft;

        //echo '<pre>'; print_r($performanceData); echo '</pre>';
		return $performance;

	}

	public function doReservation($userName, $performance) {

		$this->conn->beginTransaction(); // start transaction

		$sql = "SELECT seatsLeft FROM performances WHERE movieName = ? AND date = ? FOR UPDATE"; // write lock
		$result = $this->executeQuery($sql, array($performance["movieName"], $performance["date"]));	
        //echo '<pre>'; print_r($result); echo '</pre>';  
		
		foreach ($result as $res) {
			$seatsLeft = $res["seatsLeft"];
			if($seatsLeft <= 0) {
                        	echo "FAIL";
                        	$this->conn->rollBack();
        	        		return 0;
			} else {

				$sql = "INSERT INTO reservations(date, movieName, userName) VALUES (?, ?, ?)";
                //echo '<pre>'; print_r($performance); echo '</pre>';     
	        	$result = $this->executeUpdate($sql, array($performance["date"], $performance["movieName"], $userName));                
		        $sql = "SELECT last_insert_id() AS last_id";
           		$result = $this->executeQuery($sql);           
           		foreach($result as $res){
                 		$reservNbr = $res["last_id"];
           		}

				$sql = "UPDATE performances SET seatsLeft = seatsLeft - 1 WHERE movieName = ? AND date = ?";
                		$result = $this->executeUpdate($sql, array($performance["movieName"], $performance["date"]));
                        $this->conn->commit();
                }
		}
    
		return $reservNbr;

	}
}

?>
