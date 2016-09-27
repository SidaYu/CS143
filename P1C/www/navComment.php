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

$conn->close();
?>


<?php
//if submit is not blanked i.e. it is clicked.
if (isset($_GET["submit"])) {
	$mid = $_GET["movie"];
		
	//Check if message has been entered
	if (!$_GET['movie']) {
		$errMovie = 'Please choose a movie.';
	}
	
	$validInput = !$errMovie;
}
?>

<?php
if($validInput){
	$url = "addComment.php?id=$mid";  
	header("Location: $url"); 
	exit(1);  
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>navComment</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
  <body>
  	<div class="details-left-info">
  		<div class="toptitle">
            <h2>Add Comment to Which Movie? </h2>       
        </div>
  		<div class="input-detail">
	  		<form class="form-horizontal" role="form" method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
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
					<div class="col-sm-10 col-sm-offset-2">
						<input id="submit" name="submit" type="submit" value="Go!" class="btn btn-primary">
					</div>
				</div>
			</form> 
		</div>
	</div>  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
  </body>
</html>