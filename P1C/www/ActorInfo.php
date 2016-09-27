<?php
  $id = $_GET["id"];
  //include connect.php page for database connection
  include('mysqlConnect.php');
  //Query in the Selected Database;

  $sql = "SELECT * FROM Actor WHERE id = $id;";
  
  $rsActor = $conn->query($sql);
  
  if (!$rsActor) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(0);
  }

  $ActorRow = $rsActor->fetch_assoc();

  $sql = "SELECT CONCAT(M.title, '(',M.year, ')') AS MovieName, M.id, MA.role FROM Movie M,MovieActor MA WHERE MA.aid = $id AND MA.mid = M.id;";
  $rsMovie = $conn->query($sql);
  
  if (!$rsMovie) {
    $errmsg = $conn->error;
    echo "Sql Err: $errmsg";
    exit(1);
  }

  $conn->close();
  
?>



<!DOCTYPE html>
<html>
<head>
   <head>
   <meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ActorInfo</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
</head>
<body>
  <div class="details-left-info">

  <div class="toptitle">
    <h2><?php echo $ActorRow["first"];echo " ";echo $ActorRow["last"];?></h2>    
  </div>

  <div class="detail">
    <div class="story">
      <h4 class="stories "><span>Information</span></h4>
    </div>
    <div>
      <p>First Name : <span><?php echo $ActorRow["first"]; ?></span></p>
      <p>Last Name : <span><?php echo $ActorRow["last"]; ?></span></p>
      <p>Sex : <span><?php echo $ActorRow["sex"]; ?></span></p>
      <p>Date of Birth : <span><?php echo $ActorRow["dob"]; ?></span></p>
      <p>Date of Death : <span><?php echo $ActorRow["dod"] ? $ActorRow["dod"]:" N/A "; ?></span></p>
      <p>Movie : 
        <?php if ($rsMovie->num_rows > 0){
        while($MovieRow = $rsMovie->fetch_assoc()) { ?>
            <a href="MovieInfo.php?id=<?php echo $MovieRow["id"]?>" target="iframe_a"><?php echo $MovieRow["MovieName"]?></a>
            <span style="font-size:10px;color:green"><?php echo $MovieRow["role"]?></span>&nbsp/&nbsp
         <?php }}else{ ?>
            <span>N/A</span>
        <?php } ?>
      </p>
    </div>
    <div class="clearfix"></div>
  </div>
</div>

</body>
</html>