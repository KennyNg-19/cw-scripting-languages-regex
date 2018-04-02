<?php 
# define name and email related variables and set to empty values
	$name = $email = "";
	$nameError  = $emailError = "";
	$selectedSession = $selectedTopic ="";	
	

# menu section
// create options for menus
function getOptions($query, $selection, $attribute, $announcement){ 
	
  	$options= '<option value="" selected disabled>'.$announcement.'</option>';  	
  	foreach ($query as $row) {
  		if($row['capacity'] != 0){
		    if($selection==$row[$attribute])
		        # the case: mark the selected option and show it in the first place
		        $options.="<option value='".$row[$attribute]."' selected>".$row[$attribute]."</option>\n";
		    else
		      # the case: at very beginning, NOT start to select any option
		     $options.="<option value='".$row[$attribute]."'>".$row[$attribute]."</option>\n";  		
  		}
  	}
   	return $options;
}



# form section
// validate secuirty of data in form with PHP htmlspecialchars(), trim() and stripslashes() 
function testInputSecurity($data) {
    # strip unnecessary characters (extra space, tab, newline) from the user input data 
    $data = trim($data); 
    # remove backslashes (\) from the user input data
    $data = stripslashes($data);
    # converts special characters to HTML entities. 
    $data = htmlspecialchars($data);
    return $data;
}


$validNameRules = "<font size = 1>
		              <br>Consists of letters (a-z and A-Z), '-', apostrophe(') and spaces; 
		              <br>Start with a letter or an apostrophe; no consequtive '-'s or apostrophes. </font>";
$validEmailRules = "<font size = 1><br>Consists only of the letters a-z, A-Z, dot, hyphen and exactly one @.</font>\n";		           
// validate data in form with reular expressions 
function testConstraints($input, $formName){
	global $name, $nameError, $validNameRules;
	global $email, $emailError, $validEmailRules;
	if($_REQUEST["submitButton"] == "submit"){
		// case 1: for the input filed of name
		if($formName === "name"){
			if(empty($input))
				$nameError = "<br><font size = 1>Miss name input</font>";
			elseif(!validNameInput($input))
				$nameError = $validNameRules;		
			else
				return ($name = $input);
		}

		// case 2: for the input filed of email
		if($formName === "email"){
			if(empty($input))
				$emailError = "<br><font size = 1>Miss name input</font>";
			elseif(!validEmailInput($input))
				$emailError = $validEmailRules;					
			else
				return ($email = $input);
		}
					
	}
}

// validate data in name form with reular expressions
function validNameInput($nameInput){
    
    $name = testInputSecurity($nameInput);
    //once pass the security test, due to the name input was trimmed before, now regain the one not trimmed
    $secureName = $nameInput; 
    $valid = false;
    if(isset($secureName) && !(preg_match("/^[^\'a-zA-Z]/", $secureName)) && 
    	!(preg_match("/[^a-zA-Z\-\'\s]|\'{2}|\-{2}/", $secureName)))
    	$valid = true;
	return $valid;	
 }

// validate data in email form with reular expression
function validEmailInput($emailInput){
    $secureEmail = testInputSecurity($emailInput);
    $valid = false;
    if(preg_match("/^[\.\-a-zA-Z]*@[\.\-a-zA-Z]*$/", $secureEmail)){
      $valid = true;
    }
    return $valid;
}



# submit section, prepare for the message alert finally if nece.

// Listen to if submit button is clicked
function clickSubmitBtn(){
    global $pdo, $name, $email, $selectedTopic, $selectedSession, $lastSubmitValid, $reductionSuccess;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST["submitButton"] == "submit"){
	   
      	// detect the submit button is clicked, when the two text fields are filled and content is valid
	    if(sendWarning() == ""){	     
	      	if($reductionSuccess){				          	
	      		recordBooking();				          				          	
	      	}else{	      	
	      		restoreReduction();
	        	echo "<br><b>Unsuccessful request !<br>
	        			Sorry, the session was full just now, please try another</b>\n";
	      	}
	    }else{
	    	if($lastSubmitValid) restoreReduction();
			echo "<b>Unsuccessful request</b> :<br>".sendWarning()."</h5>";
	    }		
	}
}

// check if selections are made
function testSelections(){
	global $selectedTopic, $selectedSession;
	if(!empty($selectedTopic) && !empty($selectedSession))
		return "Both are selected";		
	if(!empty($selectedTopic) && empty($selectedSession))
		return "not select a session<br>";
	if(empty($selectedTopic) && !empty($selectedSession))
		return "not select a topic<br>";			
	if(empty($selectedTopic) && empty($selectedSession))
		return "not select a topic and a session<br>";
	return 	"";		
}

// check if name is input and valid
function testName(){	
	global $name, $nameError;
	if(!empty($name))
		return "Name is input";
	else
		return "input NAME is invalid<br>";
}

// check if email is input and valid
function testEmail(){	
	global $email, $emailError;
	if(!empty($email))
		return "Email is input";
	else
		return "input EMAIL is invalid<br>";		
}

// if the demands above are not met, prepare for sending a warning
function sendWarning(){
	$systemWarning = "";
	if(!(testSelections($selectedTopic,$selectedSession) === "Both are selected"))
		$systemWarning .= testSelections($selectedTopic,$selectedSession);
	if(!(testName("name") === "Name is input")) 
		$systemWarning .= testName("name");
	if(!(testEmail("email") === "Email is input"))
		$systemWarning .= testEmail("email");			
	return $systemWarning;
}

// if the demand above are met and non conflict occurs, record of user is generated
function recordBooking(){
	global $pdo, $name, $email, $selectedTopic, $selectedSession;
	//Create a SQL template
	$sql = "INSERT into users (name, email, topic, time) VALUES (?, ?, ?, ?)";
	//Create a prepared statement
	$record = $pdo->prepare($sql);
	//Run parameters inside database
	$record->execute(array($name, $email, $selectedTopic, $selectedSession));
	echo "<b>Your booking request is successful and the record is generated.</b>";
}

// if conflicts occurs, restore the reduction made before
function restoreReduction(){
	global $pdo, $selectedTopic, $selectedSession;
	//Create a SQL template
	$sql = "UPDATE sessions SET capacity = capacity + 1 
	       	WHERE class = ? AND time = ?";
	//Create a prepared statement
	$restore = $pdo->prepare($sql);
	//Run parameters inside database 
	$restore->execute(array($selectedTopic, $selectedSession));
}





