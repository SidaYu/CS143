<?php
	$id = $_GET['id'];
  //include connect.php page for database connection
  include('mysqlConnect.php');
  //Query in the Selected Database;

  $sql = "SELECT * FROM Movie WHERE id = '$id';";
  
  $rsMovie = $conn->query($sql);
  
  if (!$rsMovie) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(0);
  }

  $MovieRow = $rsMovie->fetch_assoc();

  $sql = "SELECT AVG(rating) AS avRating FROM Review WHERE mid = $id;";
  $rsAvg = $conn->query($sql);
  
  if (!$rsAvg) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(1);
  }

  $AvgRow = $rsAvg->fetch_assoc();

  $conn->close();
  
?>

<?php
//if submit is not blanked i.e. it is clicked.
if (isset($_GET["submit"])) {
	$name = replaceName($_GET['name']);
	$rating = $_GET['rating'];
	$comment = replaceName($_GET['comment']);
	$mid = $_GET["id"];
	
		
		// Check if title has been entered
		if (!$_GET['name']) {
			$errName = 'Please enter your name';
		}
		
		// Check if year has been entered and is valid
		if (!$_GET['rating'] || $rating > 5 || $rating < 0) {
			$errRating = 'Please enter a valid rating';
		}
		
		
		//Check if message has been entered
		if (!$_GET['comment']) {
			$errComment = 'Please enter comment';
		}
	
	$validInput = !$errName && !$errRating && !$errComment;
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
	
	$sql = "INSERT INTO Review VALUES ('$name', CURRENT_TIMESTAMP(), '$mid', '$rating', '$comment');";
	$rsInsert = $conn->query($sql);
	//Querying Exception Handling;
	if(!$rsInsert) {
		$errmsg = $conn->error;
		echo "Sql Err: $errmsg";
		exit(2);
	}else{
		$title = $MovieRow["title"];
		$result = "You have successfully added 1 Comment to <Strong>$title</strong> (mid: $mid)";
	}


	$sql = "SELECT AVG(rating) AS avRating FROM Review WHERE mid = $id;";
  	$rsAvg = $conn->query($sql);
  
  	if (!$rsAvg) {
    	$errmsg = $conn->error;
    	echo "Sql Err: $errmsg";
    	exit(3);
  	}

  	$AvgRow = $rsAvg->fetch_assoc();

	$conn->close();	
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
	<meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>addComment</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
  <body>
  	<div class="details-left-info">
  		<div class="toptitle">
            <h3>Add Comment to: </h3>
            <h2><?php echo $MovieRow["title"]?></h2>
            <p><span>
   				<?php $format_number = number_format($AvgRow["avRating"], 1, '.', ''); 
                echo $format_number; ?></span>
                <?php echo showStar($AvgRow["avRating"]);?>
            </p>
            <a href="MovieInfo.php?id=<?php echo $MovieRow["id"];?>">
                  <button type="button" class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-pencil"></span> View All Comments
                  </button>
                </a>        
        </div>
  			<div class="input-detail">
				<form class="form-horizontal" role="form" method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Your Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" id="name" name="name" value="Anonymous" placeholder="name">
							<?php echo "<p class='text-danger'>$errName</p>";?>
						</div>
					</div>
					<div class="form-group">
						<label for="rating" class="col-sm-2 control-label">Rating</label>
						<div class="col-sm-10">
      						<select class="form-control" id="rating" name="rating">
         						<option value='5'>5-Excellent</option>
        					 	<option value='4'>4-Good</option>
         						<option value='3'>3-Just soso</option>
         						<option value='2'>2-Worth it</option>
         						<option value='1'>1-I hate it</option>
							</select>
							<?php echo "<p class='text-danger'>$errRating</p>";?>
						</div>
					</div>
					<div class="form-group">
						<label for="comment" class="col-sm-2 control-label">Comment</label>
						<div class="col-sm-10">
							<textarea class="form-control" rows="3" id="comment" name="comment" placeholder="comment"></textarea>
							<?php echo "<p class='text-danger'>$errComment</p>";?>
						</div>
					</div>
					<div class="form-group">
						<div class="col-sm-10 col-sm-offset-2">
							<input type="hidden" name="id" id="id" value=<?php echo $MovieRow["id"]?> >
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
	<?php
	function showStar($score){
    	$x = floor($score);
    	$star = "";
    	for ($i=0; $i < $x; $i++) { 
    		$star = $star.'<span class="glyphicon glyphicon-star" style="color:Gold;text-shadow: black 1px 1px 1px;"></span>';
    	}
    	for (; $i < 5; $i++) { 
    		$star = $star.'<span class="glyphicon glyphicon-star-empty" style="color:Gold;text-shadow: black 1px 1px 1px;"></span>';
    	}
    	return $star;
    }
	?>  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
  </body>
</html>