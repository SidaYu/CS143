<?php
//include connect.php page for database connection
include('mysqlConnect.php');
//Query in the Selected Database;
$sql = "SELECT CONCAT(title,'(',year,')') AS MovieName, id FROM Movie;";
$AllMovie = $conn->query($sql);

if (!$AllMovie) {
$errmsg = $conn->error;
echo "Sql Err: $errmsg";
exit(0);
}


$sql = "SELECT CONCAT(first, ' ',last)AS ActorName, id FROM Actor ORDER BY first ASC;";
$AllActor = $conn->query($sql);

if (!$AllActor) {
$errmsg = $conn->error;
echo "Sql Err: $errmsg";
exit(1);
}

$conn->close();
?>

<?php
//if submit is not blanked i.e. it is clicked.
if (isset($_POST["submit"])) {
	$role = replaceName($_POST['role']);
	$actor = $_POST['actor'];
	$movie = $_POST['movie'];
		
		//Check if message has been entered
		if (!$_POST['role']) {
			$errRole = 'Please enter the Role in Movie';
		}
		
		//Check if message has been entered
		if (!$_POST['actor']) {
			$errActor = 'Please choose actor';
		}

		if (!$_POST['movie']) {
			$errMovie = 'Please choose movie';
		}
	
	$validInput = !$errRole && !$errActor && !$errMovie;
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

	$sql = "INSERT INTO MovieActor VALUES ('$movie', '$actor', '$role');";
				
	$rsInsert = $conn->query($sql);
    if (!$rsInsert) {
  		$errmsg = $conn->error;
  		echo "Sql Err: $errmsg";
  		exit(2);
  	}else{
  		$result = "You have successfully added 1 record to MovieActor!";
  	}
  	
	$conn->close();	
}
?>



<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Movie/Actor Relations</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
  <body>
  	<div class="details-left-info">
  		<div class="toptitle">
            <h2>Add Movie/Actor</h2>     
        </div>
		<div class="input-detail">
			
		<form class="form-horizontal" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<div class="form-group">
					<label for="movie" class="col-sm-2 control-label">Movie</label>
					<div class="col-sm-10">
						<select class="form-control" id="movie" name="movie">
							<option selected value="">Please Select</option>
							<?php
								if ($AllMovie->num_rows > 0) {
								// output data of each row
								while($row = $AllMovie->fetch_assoc()) {
							?>
								<option value = <?php echo $row["id"] ?>><?php echo $row["MovieName"] ?></option>
							<?php	}
								}else{
							?>
								<option>No Movie</option>
							<?php } ?>
				</select>
					<?php echo "<p class='text-danger'>$errMovie</p>";?>
					</div>
			</div>
			<div class="form-group">
					<label for="actor" class="col-sm-2 control-label">Actor</label>
					<div class="col-sm-10">
						<select class="form-control" id="actor" name="actor">
 							<option selected value="">Please Select</option>
 							<?php
								if ($AllActor->num_rows > 0) {
								// output data of each row
								while($row = $AllActor->fetch_assoc()) {
							?>
								<option value = <?php echo $row["id"] ?>><?php echo $row["ActorName"] ?></option>
							<?php	}
								}else{
							?>
								<option>No Actor</option>
							<?php } ?>
				</select>
					<?php echo "<p class='text-danger'>$errActor</p>";?>
					</div>
			</div>
			<div class="form-group">
				<label for="role" class="col-sm-2 control-label">Role</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="role" name="role" placeholder="Role in the Movie">
					<?php echo "<p class='text-danger'>$errRole</p>";?>
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<input id="submit" name="submit" type="submit" value="Add" class="btn btn-primary">
				</div>
			</div>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<?php if($result) { ?> 
					<div class="alert alert-success"><strong>SUCCESS: </strong><?php echo $result ?></div>
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