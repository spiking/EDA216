<?php
        require_once('database.inc.php');
        
        session_start();
        $db = $_SESSION['db'];
        $userId = $_SESSION['userId'];
        $db->openConnection(); 

		$movieName = $_POST["movieName"];  // get movieName from POST 
		$_SESSION['movieName'] = $movieName;  // set session to movieName
		$performanceDates = $db->getPerformanceDates($movieName);  // get performance dates for specific movie
		$db->closeConnection();
?>

<html>
<head><title>Booking 2</title><head>
<body><h1>Booking 2</h1>
        Current user: <?php print $userId ?>
	<br>
	<br> 
	Selected movie: <?php print $movieName ?>
	<p>
        Performance dates:
        <p>
        <form method=post action="booking3.php">
                <select name="date" size=10>
                <?php
                        $first = true;
                        foreach ($performanceDates as $date) {
                                if ($first) {
                                        print "<option selected>";
                                        $first = false;
                                } else {
                                        print "<option>";
                                }
                                print $date;
                        }
                ?>
                </select>
                <input type=submit value="Select date">
        </form>
</body>
</html>
