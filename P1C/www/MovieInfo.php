<?php
  $id = $_GET["id"];
  //include connect.php page for database connection
  include('mysqlConnect.php');
  //Query in the Selected Database;

  $sql = "SELECT * FROM Movie WHERE id = $id;";
  
  $rsMovie = $conn->query($sql);
  
  if (!$rsMovie) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(0);
  }

  $MovieRow = $rsMovie->fetch_assoc();

  $sql = "SELECT CONCAT(D.first, ' ',D.last) AS DirectorName, D.id FROM Director D,MovieDirector MD WHERE MD.mid = $id AND D.id = MD.did;";
  $rsDirector = $conn->query($sql);
  
  if (!$rsDirector) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(1);
  }


  $sql = "SELECT CONCAT(A.first, ' ',A.last) AS ActorName, A.id, MA.role FROM Actor A,MovieActor MA WHERE MA.mid = $id AND A.id = MA.aid;";
  $rsActor = $conn->query($sql);
  
  if (!$rsActor) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(3);
  }

  $sql = "SELECT genre FROM MovieGenre WHERE mid = $id;";
  $rsGenre = $conn->query($sql);
  
  if (!$rsGenre) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(4);
  }


  $sql = "SELECT * FROM Review WHERE mid = $id;";
  $rsReview = $conn->query($sql);
  
  if (!$rsReview) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(5);
  }

  $sql = "SELECT AVG(rating) AS avRating FROM Review WHERE mid = $id;";
  $rsAvg = $conn->query($sql);
  
  if (!$rsAvg) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(6);
  }

  $AvgRow = $rsAvg->fetch_assoc();

  $conn->close();
  
?>



<!DOCTYPE html>
<html>
<head>
   <head>
   <meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MovieInfo</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
</head>
<body>



  <div class="details-left-info">
              <div class="toptitle">
                <h2><?php echo $MovieRow["title"]; ?></h2>
                <p><span>
                  <?php $format_number = number_format($AvgRow["avRating"], 1, '.', ''); 
                    echo $format_number ?></span>
                  <?php echo showStar($AvgRow["avRating"]);?>
                </p> 
                <a href="addComment.php?id=<?php echo $MovieRow["id"];?>">
                  <button type="button" class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-pencil"></span> Add Comment
                  </button>
                </a>    
              </div>
      
              <div class="detail">
                <div class="story">
                  <h4 class="stories "><span>Information</span></h4>
                </div>
                <div>
                <p>Year : <span><?php echo $MovieRow["year"]; ?></span></p>
                <p>Genre : <span>
                  <?php 
                    if($rsGenre->num_rows > 0){
                    while($GenreRow = $rsGenre->fetch_assoc()) { ?>
                  <?php echo $GenreRow["genre"];?>&nbsp&nbsp
                  <?php } }else{ echo "N/A";}?>
                </span></p>
                <p>Rating : <span><?php echo $MovieRow["rating"]; ?></span></p>
                <p>Company : <span><?php echo $MovieRow["company"]; ?></span></p>
                <p>Director : <span>
                  <?php 
                    if($rsDirector->num_rows > 0){
                    while($DirectorRow = $rsDirector->fetch_assoc()) { ?>
                  <?php echo $DirectorRow["DirectorName"]?>&nbsp&nbsp
                  <?php } }else { echo "N/A";}?>
                </span></p>
                <p>Actor : 
                  <?php 
                  if($rsActor->num_rows > 0){
                  while($ActorRow = $rsActor->fetch_assoc()) { ?>
                  <a href="ActorInfo.php?id=<?php echo $ActorRow["id"];?>" target="iframe_a"><?php echo $ActorRow["ActorName"]?></a>
                  <span style="font-size:10px;color:green"><?php echo $ActorRow["role"]?></span>&nbsp/&nbsp
                  <?php } }else{ ?>
                  <span>N/A</span>
                  <?php } ?>
                </p>
                </div>
                <div class="clearfix"></div>
              </div>
              

              <div class="comments">
                <div class="story">
                  <h4 class="stories "><span>comments (<?php echo $rsReview->num_rows ?>)</span></h4>
                </div>
                <!---->

                <?php while($CommentRow = $rsReview->fetch_assoc()) { ?>
                  <div class="comment">
                  <div class="cheader">
                      <div class="comment-people">
                      <h5><span class="glyphicon glyphicon-user"> <?php echo $CommentRow["name"] ?> :</h5>  
                    </div>
                    <div class="comment-time">
                      <h5><?php echo $CommentRow["time"] ?></h5>
                    </div>
                    <div class="clearfix"></div>
                  </div>
                  
                  <div class="cbottom">
                    <div class="rate">
                      <p><?php echo $CommentRow["comment"] ?></p>
                    </div>
                    <div class="comment-score">
                     <p><?php echo $CommentRow["rating"] ?>.0</p>
                    </div>
                    <div class="clearfix"></div>
                  </div> 
                  <div class="clearfix"></div>
              </div>
                <?php } ?>

            </div>

  <?php
  function showStar($score){
      $x = floor($score);
      $star = "";
      for ($i=0; $i < $x; $i++) { 
        $star = $star.'<span class="glyphicon glyphicon-star" style="color:Gold;text-shadow: black 3px 1px 1px;"></span>';
      }
      for (; $i < 5; $i++) { 
        $star = $star.'<span class="glyphicon glyphicon-star-empty" style="color:Gold;text-shadow: black 3px 1px 1px;"></span>';
      }
      return $star;
    }
  ?> 

</body>
</html>