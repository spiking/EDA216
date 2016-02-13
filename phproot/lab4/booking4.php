<?php
        require_once('database.inc.php');
        
        session_start();
        $db = $_SESSION['db'];
        $userId = $_SESSION['userId'];
        $db->openConnection(); 

		$reservNbr = $db->doReservation($userId, $_SESSION['performance']);
        $db->closeConnection();
?>

<html>
<head><title>Booking 4</title><head>
<body><h1>Booking 4</h1>
	
	
	<!-- fancy javascript -->

	<p id="demo">Display the result here.</p>

	<script>
		nbr = '<?php echo $reservNbr ;?>';	

		if (nbr <= 0) {
    			document.getElementById("demo").innerHTML = "No seats available!";
		} else {
			document.getElementById("demo").innerHTML = "One ticket booked! Booking number: " + nbr;
		}

	</script>
	
	<form method=post action="booking1.php">		
		<input type=submit value="Make a new reservation">
	</form>

        
</body>
</html>
