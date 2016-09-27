<!DOCTYPE HTML> 
<html>
<head>
	<title>PHP Web Query Interface</title>
	<style>
	table, th, td {
    	border: 1px solid black;
	}
	</style>
</head>
<body> 

<h1>PHP Web Query Interface</h1>
<p>Project1B in CS143 10/19/2015 by Sida.Yu SID:704592981)</p>
<p>Type an SQL query in the following box:
<p>Example: SELECT * FROM Actor WHERE id=10;</p>
<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
 	<textarea name="sqlexp" rows="5" cols="40"></textarea>
   	<input type="submit" name="submit" value="submit"> 
</form>

<p> Note: tables and fields are case sensitive. All tables in Project 1B are availale.</p >
<p> If there is Connection Err or Quary Err, it will show in the screen</p >


<?php

$exp = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
   $exp = $_GET["sqlexp"];
   echo "$exp";
   echo "<br>";
}

//Connect to the Database;
$db_connection = mysql_connect("localhost", "cs143", "");
//Connecting Exception Handling;
if(!$db_connection) {
    $errmsg = mysql_error($db_connection);
    print "Connection Failed: $errmsg <br />";
    exit(1);
}

//Choose Database; Develop -> "TEST", Submit -> "CS143"
mysql_select_db("CS143", $db_connection);

//Query in the Selected Database;
$query = "$exp";
$rs = mysql_query($query, $db_connection);
//Querying Exception Handling;
if(!$rs) {
    $errmsg = mysql_error();
    print "Query Failed: $errmsg <br />";
    exit(1);
}

//Show Result in a Html Table.
showresult($rs);

//Close the connection after one query.
mysql_close($db_connection);


/*
* This function is to show all result in a html table;
* As well as the totol num of the result;
*/
function showresult($rs){
	//show the totol num of the result;
	$num=mysql_num_rows($rs);
	echo "total:".$num."<br />";

	//show table;
	echo "<table>";
	//Traverse all fields, show the headline of table;
	echo "<tr>";
	while ($property = mysql_fetch_field($rs)){
		echo "<th>"."$property->name"."</th>";
	}
	echo "</tr>";
	
	//Traverse all the rows in results, insert them in the table!
	while($row = mysql_fetch_row($rs)) {
		echo "<tr>";
		foreach ($row as $value){
			if (is_null($value)) {
				echo "<td>"."N/A"."</td>";
			}else{
				echo "<td>".$value."</td>";
			}
		}
    	echo "</tr>";
	}
	//Table ended.
	echo "</table>"; 
}

?>


</body>
</html>
