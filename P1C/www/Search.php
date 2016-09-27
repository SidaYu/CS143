<?php
  if (isset($_POST["submit"])) {
    $keys = $_POST['keys'];
    $keywords = explode(' ',$keys); 

    if (!$_POST['keys']) {
      $errKeys = 'Please Enter the keywords';
    }

    $validInput = !$errKeys; 
  }
?>

<?php
if($validInput){
     //include connect.php page for database connection
    include('mysqlConnect.php');
    //Query in the Selected Database;   

    $sql = generateSql("Movie",$keywords,"title");
    $rsMovie = $conn->query($sql);
  
    if (!$rsMovie) {
      $errmsg = $conn->error;
      echo "Sql Err: $errmsg";
      exit(0);
    }

    $table = " (SELECT CONCAT_WS(' ',first,last) AS ActorName, sex, dob, id FROM Actor) S ";

    $sql = generateSql($table,$keywords,"S.ActorName");
    $rsActor = $conn->query($sql);
  
    if (!$rsActor) {
      $errmsg = $conn->error;
      echo "Sql Err: $errmsg";
      exit(1);
    }

    $conn->close();
}

function generateSql($table,$keywords,$attributes){
    $sql = "SELECT * FROM "."$table"." WHERE ";
    $arrlength = count($keywords);

    for($x = 0; $x < $arrlength; $x++) {
        $sql .= "$attributes LIKE '%".replaceName($keywords[$x])."%'";
        if($x != $arrlength-1){
          $sql .= " AND ";
        }
    }
    return $sql;
}

function replaceName($name){
  $name =  str_replace("'", "''", "$name");
  return $name;
}

?>


<!DOCTYPE html>
<html>
<head>
   <head>
   <meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
  </head>
</head>
<body>

  <div class="details-left-info">
              <div class="toptitle">
                <div>
                  <h2>Search Movie/Actor</h2>
                </div>
                <div >
                  <form lass="form-horizontal" role="form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                    <div class="col-sm-4">
                      <input type="text" id="keys" name="keys" class="form-control" value="<?php echo $_POST['keys'];?>" placeholder="Search">
                    </div>
                    <button type="submit" id="submit" name="submit" class="btn btn-default">Quick Search</button>
                  </form>
                </div>
                
              </div>

      
              <div class="detail">
                <div class="story">
                  <h4 class="stories "><span>Movies (<?php echo $rsMovie ? $rsMovie->num_rows:0 ?>)</span></h4>
                </div>
                <div>
                  <table class="table table-condensed">
                     <thead>
                        <tr>
                           <th>Title</th>
                           <th>Year</th>
                           <th>Company</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if ($rsMovie->num_rows > 0){
                          while($MovieRow = $rsMovie->fetch_assoc()) { ?>
                        <tr>
                           <td><a href="MovieInfo.php?id=<?php echo $MovieRow["id"];?>" target="iframe_a"><?php echo $MovieRow["title"]?></a></td>
                           <td><?php echo $MovieRow["year"]?></td>
                           <td><?php echo $MovieRow["company"]?></td>
                        </tr>
                        <?php } }?>
                     </tbody>
                  </table>
                </div>
              </div>
              
              <div class="detail">
                <div class="story">
                  <h4 class="stories "><span>Actor/Actress (<?php echo $rsActor ? $rsActor->num_rows:0 ?>)</span></h4>
                </div>
                <div>
                  <table class="table table-condensed">
                     <thead>
                        <tr>
                           <th>Name</th>
                           <th>Sex</th>
                           <th>Date Of Birth</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php if ($rsActor->num_rows > 0){
                          while($ActorRow = $rsActor->fetch_assoc()) { ?>
                        <tr>
                           <td><a href="ActorInfo.php?id=<?php echo $ActorRow["id"];?>" target="iframe_a"><?php echo $ActorRow["ActorName"]?></a></td>
                           <td><?php echo $ActorRow["sex"]?></td>
                           <td><?php echo $ActorRow["dob"]?></td>
                        </tr>
                        <?php } }?>
                     </tbody>
                  </table>
                </div>
                <div class="clearfix"></div>
              </div>

              <div class="clearfix"></div>

  </div>
  <div class="clearfix"></div>



</body>
</html>