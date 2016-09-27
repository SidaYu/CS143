<?php
//if submit is not blanked i.e. it is clicked.
if (isset($_POST["submit"])) {
	$role = $_POST['role'];
	$last = replaceName($_POST['last']);
	$first = replaceName($_POST['first']);
	$sex = $_POST['sex'];
	$dob = $_POST['dob'];
	$dod = $_POST['dod'];
		
		if (!$_POST['role']) {
			$errRole = 'Please choose the Role of the person';
		}	

		// Check if title has been entered
		if (!$_POST['last']) {
			$errLast = 'Please enter the last name of the person';
		}
		
		// Check if year has been entered and is valid
		if (!$_POST['first']) {
			$errFirst = 'Please enter the first name of the person';
		}
		
		//Check if message has been entered
		if ($role == "Actor" && !$_POST['sex']) {
			$errSex = 'Please enter the sex of the Actor';
		}
		
		//Check if message has been entered
		if (!$_POST['dob']) {
			$errDob = 'Please enter a date';
		}elseif (!validTime($dob)) {
			$errDob = 'Please enter a date as yyyy-mm-dd';
		}

		//Check if message has been entered
		if ($_POST['dod'] && !validTime($_POST['dod'])) {
			$errDod = 'Please enter a date as yyyy-mm-dd';
		}
	
	$validInput = !$errRole && !$errLast && !$errFirst && !$errSex && !$errDob && !$errDod;
}
function validTime($time){
	$patten = "/\d{4}[-](0?[1-9]|1[012])[-](0?[1-9]|[12][0-9]|3[01])$/";
	if (preg_match($patten, $time)) { 
   		return true;
  	} else { 
   		return false; 
  	} 
}
function replaceName($name){
	$name =  str_replace("'", "''", "$name");
	return $name;
}
?>

<?php
if ($validInput) {
	//include connect.php page for database connection
	include('mysqlConnect.php');
	//Query in the Selected Database;
	$sql = "SELECT * FROM MaxPersonID;";
	$rsSelect = $conn->query($sql);
	//Querying Exception Handling;
	if(!$rsSelect) {
		$errmsg = $conn->error;
		$result = "Sql Err1: $errmsg";
		$flag = false;
	}else{
		$row = $rsSelect->fetch_assoc();
		$newID = $row["id"];

		$sql = "UPDATE MaxPersonID SET id = id + 1;";
		$rsUpdate = $conn->query($sql);
		
		if(!$rsUpdate) {
    		$errmsg = $conn->error;
			$result = "Sql Err2: $errmsg";
			$flag = false;	
		}else{
			if($role == "Actor"){
				if(!$dod){
					$sql = "INSERT INTO Actor VALUES ('$newID', '$last', '$first', '$sex', '$dob', null);";
				}else{
					$sql = "INSERT INTO Actor VALUES ('$newID', '$last', '$first', '$sex', '$dob', '$dod');";
				}
			}else if($role == "Director"){
				if(!$dod){
					$sql = "INSERT INTO Director VALUES ('$newID', '$last', '$first', '$dob', null);";
				}else{
					$sql = "INSERT INTO Director VALUES ('$newID', '$last', '$first', '$dob', '$dod');";
				}
			}
			$rsInsert = $conn->query($sql);
    		if (!$rsInsert) {
  				$errmsg = $conn->error;
  				$result = "Sql Err3: $errmsg";
				$flag = false;
  			}else{
  				$result = "You have successfully added 1 record to $role ! id: $newID";
				$flag = true;
  			}
  		}
  	}
  	$finished = ture;
	$conn->close();	
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 3 Responsive Design Tutorial | RevillWeb.com</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
  <body>
  	<div class="details-left-info">
  		<div class="toptitle">
            <h2>Add Actor/Director</h2>     
        </div>

		<div class="input-detail">
		<form class="form-horizontal" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<div class="form-group">
				<label for="col-sm-10" class="col-sm-2 control-label">Role</label>
				<div class="col-sm-10">
					<div class="radio">
							<label><input type="radio" name="role" value="Actor">Actor</label>
					</div>
					<div class="radio">
							<label><input type="radio" name="role" value="Director">Director</label>
					</div>
					<?php echo "<p class='text-danger'>$errRole</p>";?>
				</div>
			</div>
			
			<div class="form-group">
				<label for="first" class="col-sm-2 control-label">FirstName</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="first" name="first" placeholder="First Name">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errFirst</p>";?>
				</div>
			</div>

			<div class="form-group">
				<label for="last" class="col-sm-2 control-label">LastName</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="last" name="last" placeholder="Last Name">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errLast</p>";?>
				</div>
			</div>

			<div class="form-group">
					<label for="sex" class="col-sm-2 control-label">Sex</label>
					<div class="col-sm-5">
						<select class="form-control" id="sex" name="sex">
	 						<option>Female</option>
						 	<option>Male</option>
						</select>
					</div>
					<div class="col-sm-5">
						<?php echo "<p class='text-danger'>$errSex</p>";?>
					</div>
			</div>

			<div class="form-group">
				<label for="dob" class="col-sm-2 control-label">DateOfBirth</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="dob" name="dob" placeholder="yyyy-mm-dd">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errDob</p>";?>
				</div>
			</div>
			<div class="form-group">
				<label for="dod" class="col-sm-2 control-label">DateOfDeath</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="dod" name="dod" placeholder="yyyy-mm-dd (leave blank if alive now)">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errDod</p>";?>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<input id="submit" name="submit" type="submit" value="Add" class="btn btn-primary">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<?php if($finished && $flag) { ?> 
					<div class="alert alert-success"><strong>SUCCESS: </strong><?php echo $result ?></div>
					<?php }elseif ($finished && !$flag) { ?>
					<div class="alert alert-danger"><strong>FAILURE: </strong><?php echo $result ?></div>
					<?php } ?>
				</div>	
			</div>
		</form> 
	</div>
	</div>   
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
  </body>
</html>