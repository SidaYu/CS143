<?php
	$hostname="localhost"; //local server name default localhost
	$username="cs143";  //mysql username default is root.
	$password="";       //blank if no password is set for mysql.
	$database="CS143";  //database name which you created
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $database);
// Check connection
	if ($conn->connect_error) {
    	die("Connection failed: " . $conn->connect_error);
	} 
?>