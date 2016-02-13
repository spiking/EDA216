<?php
        require_once('database.inc.php');
        
        session_start();
        $db = $_SESSION['db'];
        $userId = $_SESSION['userId'];
        $db->openConnection(); 

		$movieName = $_SESSION['movieName'];
		$date = $_POST['date'];
		$performance = $db->getPerformanceData($movieName, $date);
		$_SESSION['performance'] = $performance;
        $db->closeConnection();
?>

<html>
<head><title>Booking 3</title><head>
<body><h1>Booking 3</h1>
        Current user: <?php print $userId ?>
        <br> 
       <p> Data for selected performance: </p>
	
	Movie: <?php print $performance["movieName"] ?> 
	<br>
	Date: <?php print $performance["date"] ?>
	<br>
	Theater:<?php print $performance["theaterName"] ?>
	<br>
	Free seats:<?php print $performance["seatsLeft"] ?>
	<br>

	 <form method=post action="booking4.php">
        	<br>     
		 <input type=submit value="Book ticket!">
        	<br>
	</form>


	
</body>
</html>


