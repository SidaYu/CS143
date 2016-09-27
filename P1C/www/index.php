<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!-- Set the viewport so this responsive site displays correctly on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CS143 Project1C: Movie database Web site</title>
    <!-- Include bootstrap CSS -->
    <link href="includes/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="includes/style.css" rel="stylesheet">
</head>

<body>
    <div class="bg">
        <!-- Site header and navigation -->
        <div class="nav">
            <div class="navbar-brand"></div>    
            <nav class="navbar navbar-inverse navbar-fixed-top">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <a class="navbar-brand" href="index.php">Movie Website</a>
                    </div>
                <div>
                <ul class="nav navbar-nav">
                    <li class="active"><a href="index.php">Home</a></li>
                    <li><a href="Search.php" target="iframe_a">Search</a></li>
                    <li><a href="MovieInfo.php?id=234" target="iframe_a">MovieInfo</a></li> 
                    <li><a href="ActorInfo.php?id=3521" target="iframe_a">ActorInfo</a></li>
                    <li><a href="navComment.php" target="iframe_a">Comment</a></li>
                    <li class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">Back—End<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="addMovie.php" target="iframe_a">Add Movie</a></li>
                            <li><a href="addPerson.php" target="iframe_a">Add Person</a></li>
                            <li><a href="addMA.php" target="iframe_a">Add Movie/Actor</a></li>
                            <li><a href="addMD.php" target="iframe_a">Add Movie/Director</a></li> 
                        </ul>
                    </li> 
                </ul>
                </div>
                <div>
                    <form class="navbar-form navbar-right" role="form" method="post" action="Search.php" target="iframe_a">
                        <div class="form-group">
                            <input type="text" id="keys" name="keys" class="form-control" placeholder="Search">
                        </div>
                        <button type="submit" id="submit" name="submit" class="btn btn-default">Quick Search</button>
                    </form>
                </div>
                </div>
            </nav>   
        </div>
        <!-- Site banner -->
        <!-- Middle content section -->
        <div class="middle">
        <div class="container">
            <div class="col-md-2">
                <div>
                    <h2>Quick Link</h2>
                    <ul class="nav nav-pills nav-stacked">
                        <li><a href="Search.php" target="iframe_a">Search</a></li>
                        <li><a href="MovieInfo.php?id=234" target="iframe_a">MovieInfo</a></li>
                        <li><a href="ActorInfo.php?id=3521" target="iframe_a">ActorInfo</a></li>
                        <li><a href="navComment.php" target="iframe_a">Comment</a></li>
                        <li><a href="addPerson.php" target="iframe_a">Add Person</a></li>
                        <li><a href="addMovie.php" target="iframe_a">Add Movie</a></li>
                        <li><a href="addMA.php" target="iframe_a">Add Movie/Actor</a></li>
                        <li><a href="addMD.php" target="iframe_a">Add Movie/Director</a></li>
                    </ul>
                </div>
                <div>
                    
                </div>
            </div>
            <div class="col-md-10">
                <iframe src="Search.php" name="iframe_a" width="100%" height="650px" frameborder="0"></iframe>
            </div>
        </div>
    </div>

        <!-- Site footer -->
    <div class="bottom">
        <div class="container">    
                <h3><a href="index.php">Movie Database Website</a></h3>
                <p>CS143 Project1C BY Sida Yu UID:704592981 2015-10 @ UCLA</p>
        </div>
    </div>

    </div>


    <!-- Include jQuery and bootstrap JS plugins -->
    <script src="includes/jquery/jquery-2.1.0.min.js"></script>
    <script src="includes/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>