<?php
//if submit is not blanked i.e. it is clicked.
if (isset($_POST["submit"])) {
	$title = replaceName($_POST['title']);
	$year = $_POST['year'];
	$rating = $_POST['rating'];
	$company = replaceName($_POST['company']);
	$genre_arr = $_POST['genre'];
		
		// Check if title has been entered
		if (!$_POST['title']) {
			$errTitle = 'Please enter the title of Movie';
		}
		
		// Check if year has been entered and is valid
		if (!$_POST['year'] || $year < 1800) {
			$errYear = 'Please enter a valid production year';
		}
		
		//Check if message has been entered
		if (!$_POST['rating']) {
			$errRating = 'Please enter rating';
		}
		
		//Check if message has been entered
		if (!$_POST['company']) {
			$errCompany = 'Please enter company';
		}

		if (!$_POST['genre']) {
			$errGenre = 'Please choose genre';
		}
	
	$validInput = !$errTitle && !$errYear && !$errRating && !$errCompany && !$errGenre;
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
	$sql = "SELECT * FROM MaxMovieID;";
	$rsSelect = $conn->query($sql);
	//Querying Exception Handling;
	if(!$rsSelect) {
		$errmsg = $conn->error;
		$result = "Sql Err1: $errmsg";
		$flag = false;
	}else{
		$row = $rsSelect->fetch_assoc();
		$newID = $row["id"];

		$sql = "UPDATE MaxMovieID SET id = id + 1;";
		$rsUpdate = $conn->query($sql);
		
		if(!$rsUpdate) {
    		$errmsg = $conn->error;
			$result = "Sql Err2: $errmsg";
			$flag = false;	
		}else{
			$sql = "INSERT INTO Movie VALUES ('$newID', '$title', '$year', '$rating', '$company');";
			foreach ($genre_arr as $k=>$v){ 
				$sql .= "INSERT INTO MovieGenre VALUES ('$newID', '$v');";
			}
			$rsInsert = $conn->multi_query($sql);
    		if (!$rsInsert) {
  				$errmsg = $conn->error;
				$result = "Sql Err3: $errmsg";
				$flag = false;	
  			}else{
				$result = "You have successfully added record to Movie and MovieGenre! mid: $newID";
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
            <h2>Add Movie</h2>     
        </div>
		<div class="input-detail">

		<form class="form-horizontal" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<div class="form-group">
				<label for="title" class="col-sm-2 control-label">Title</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="title" name="title" placeholder="Title">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errTitle</p>";?>
				</div>
			</div>
			<div class="form-group">
				<label for="year" class="col-sm-2 control-label">Year</label>
				<div class="col-sm-5">
					<input type="number" class="form-control" id="Year" name="year" placeholder="Year">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errYear</p>";?>
				</div>
			</div>
			<div class="form-group">
				<label for="rating" class="col-sm-2 control-label">Rating</label>
				<div class="col-sm-5">
					<select class="form-control" id="rating" name="rating">
						<option selected value="">Please Select</option>
 						<option>surrendere</option>
					 	<option>R</option>
 						<option>G</option>
 						<option>PG</option>
 						<option>PG-13</option>
						<option>NC-17</option>
					</select>
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errRating</p>";?>
				</div>
			</div>
			<div class="form-group">
				<label for="company" class="col-sm-2 control-label">Company</label>
				<div class="col-sm-5">
					<input type="text" class="form-control" id="company" name="company" placeholder="company name">
				</div>
				<div class="col-sm-5">
					<?php echo "<p class='text-danger'>$errCompany</p>";?>
				</div>
			</div>
			<div class="form-group">
					<label for="genre" class="col-sm-2 control-label">Genre</label>
					<div class="col-sm-10">
						<div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Action">Action
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Adult">Adult
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Adventure">Adventure
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Animation">Animation
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Thriller">Thriller
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="War">War
							</div>
						</div>
						
						<div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Comedy">Comedy
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Crime">Crime
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Western">Western
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Drama">Drama
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Family">Family
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Fantasy">Fantasy
							</div>
						</div>

						<div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Horror">Horror
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Musical">Musical
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Mystery">Mystery
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Romance">Romance
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Sci-Fi">Sci-Fi
							</div>
							<div class="col-sm-2">
								<input type="checkbox" id="genre[]" name="genre[]" value="Short">Short
							</div>
						</div>
						
						<div>
							<div class="col-sm-3">
								<input type="checkbox" id="genre[]" name="genre[]" value="Documentary">Documentary
							</div>
							<div class="col-sm-9">
							</div>
						</div>

						<div>
							<?php echo "<p class='text-danger'>$errGenre</p>";?>
						</div> 						
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