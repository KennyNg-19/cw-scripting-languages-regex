<?php
require 'db.php';
include 'functions.php';			              	

try{
	$pdo = new PDO($dsn,$db_username,$db_password,$opt);
	$pdo->beginTransaction();		
?>

<!DOCTYPE html>
<html>
<head> 
  <title>Book Your Sessions</title>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
<h1>Session Booking System</h1>
<div>
	<form name="form" method='post'>
    
	<?php
		$lastSubmitValid = $reductionSuccess = false;
	    // works if this is not the first time visit
		// The system is desgined to keep all the input when user all input is CORRECT before
	    // After the button is clicked and page is refresed, Pre-define a prepared statement to do reduction derived from the sound information last page, before the 'select' sql statements which generate the menus. In this case, two menu ideally can list only topics for which there are sessions with places left, and sessions with places avaiable.
	    if($_REQUEST['submitButton'] && $_REQUEST['selectTopic'] && 
	    	$_REQUEST['selectSession'] && validNameInput($_REQUEST['name']) && 
	    	validEmailInput($_REQUEST['email'])){
	    	// User all input is CORRECT before
	    	$lastSubmitValid = true;
	    	//Create a SQL template
	    	$sql = "UPDATE sessions SET capacity = capacity - 1 
	        		WHERE capacity > 0 AND class = ? AND time = ?;";
	        //Create a prepared statement
			$reduction = $pdo->prepare($sql);	 
			//Run parameters inside database  
			$reduction->execute(array($_REQUEST['selectTopic'], $_REQUEST['selectSession']));

			// For later need of restoring if necessary, the prepared statment excution returns TRUE on success or FALSE on failure.
			if($reduction)	$reductionSuccess = true;
		}
		// Contrarily, without this, the sql stmts below of genrating the menus will show the sessions without places one page later, turning out that the requirement is not meet in time.
	?>
		
	<?php
		//Firstly check the if all bookings are full
	    $sql = "SELECT * FROM sessions WHERE capacity > 0 GROUP BY class;";	    
	    $openClasses = $pdo->prepare($sql);		
	    $openClasses->execute();		
	    if($openClasses->rowCount() == 0){
	    	//If none of the sessions has places left, then the system should inform the user about this and not show any drop-down menus or text fields
	    	$allFull = "<h1>None of the sessions has places left, please wait</h1>";
	    	echo $allFull;
	    }else{
	    	//Otherwise, the class topics options available are to be shown, so are other fields
	?>

		
		<!-- Menu for topic selections -->
		Topic
		<select name="selectTopic" class="menu" onChange="this.form.submit()">
			<?php
			// Assign the selected option to a global variable if selection occurs
			if(isset($_REQUEST["selectTopic"]))  $selectedTopic = $_REQUEST["selectTopic"];
			// Put returned class topic(s) options into the menu.
			echo getOptions($openClasses, $selectedTopic, "class", "Select class topic");  
			?>
		</select><br><br>
		

		<!-- Menu for session time selections -->
		Time 
		<select name="selectSession" class = "menu" >
			<?php
			//In terms of the selected topic above, get the day(s) and time(s) from database 
			$sql = "SELECT * FROM sessions WHERE capacity > 0 AND class = ? ";
			//Create a prepared statement
			$openSessions = $pdo->prepare($sql);
			//Run parameters inside database  
			$openSessions->execute(array($selectedTopic));
			
			//Assign the selected option to a global variable if selection occurs			
			if(isset($_REQUEST['selectSession']))  $selectedSession = $_REQUEST['selectSession'];
			//Put returned day(s) and time(s) of session options into the menu.
			echo getOptions($openSessions, $selectedSession, "time", "Select class session");	
			?>
		</select><br><br>

		
		<!-- Two text fields -->
		<?php 
			$namePlaceholder = "Start: a letter/apostrophe. Plus: letters (a-z and A-Z), -, apostrophe and spaces";
			$emailPlaceholder = "Contains: a-z, A-Z, dot, hyphen and exactly one @ ";
		?>
		<!-- the text input field for name -->
		Name		
		<input type="text" name="name" placeholder= "<?php echo $namePlaceholder; ?>"
		value = "<?php echo testConstraints($_REQUEST['name'], 'name'); ?>" size="100">
		<span class="error">*<?php echo $nameError; ?></span><br><br>
		

		<!-- the text input field for email -->
		Email
	    <input type="text" name="email" placeholder= "<?php echo $emailPlaceholder; ?>"	
	    value = "<?php echo testConstraints($_REQUEST["email"], "email"); ?>" size="100">       
	    <span class="error">*<?php echo $emailError; ?></span><br><br>
		
		
		<!-- the submit button -->
		<input type="submit" name="submitButton" value="submit" >
		<span align="center"><br><b>System:</b></span>
	<?php
		clickSubmitBtn();

	}			
	?>

	</form>
	</div>
	<h3>User Records</h3> 
	<table class="striped">
        <tr class="header">
            <td>User ID</td>
            <td>User Name</td>
            <td>User Email</td>
            <td>Class Topic</td>
            <td>Class Time</td>
        </tr>
        <?php
           $query = $pdo->query("SELECT * FROM users");
           foreach($query as $row) {
               
               echo "<tr class='alt'>";
               echo "<td>".$row['id']."</td>";
               echo "<td>".$row['name']."</td>";
               echo "<td>".$row['email']."</td>";
               echo "<td>".$row['topic']."</td>";
               echo "<td>".$row['time']."</td>";
               echo "</tr>";    
           }
        ?>
    </table>

	<?php 
	echo "-------------------------------------------------------------<br>";
	    $stmt = $pdo->query("SELECT * FROM sessions");
	?>
		<h3>Updated Session Information</h3>
	    <table class="striped">
        <tr class="header">
            <td>User ID</td>            
            <td>Class Topic</td>
            <td>Class Time</td>
            <td>Capacity</td>            
        </tr><?php 
	    foreach($stmt as $row) {
	           echo "<tr class='alt'>";
	           echo "<td>".$row['id']."</td>";
               echo "<td>".$row['class']."</td>";
               echo "<td>".$row['time']."</td>";
               echo "<td>".$row['capacity']."</td>";
               echo "</tr>";  
	     }
	     ?></table>
	  <?php 

	  $pdo->commit();
	  $pdo = NULL;
	  }catch(PDOException $e) {
	  	// Rollback the transaction
		$pdo->rollBack();
	    exit("PDO Error: ".$e->getMessage()."<br>");
	  }


	?>
</body>

</html>