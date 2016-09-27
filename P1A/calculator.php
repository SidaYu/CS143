<!DOCTYPE HTML> 
<html>
<head>
</head>
<body> 

<h1>PHP Simple Calculater</h1>
<p>Project1A in CS143 10/07/2015 by Sida.Yu SID:704592981)</p>
<p>Type an expression in the following box (e.g., 10.5+20*3/25).</p>
<form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
   Please Input: <input type="text" name="exp" size=20 maxlength=20>
   <input type="submit" name="submit" value="Calculate"> 
</form>
<ul>
  <li>Only numbers and +,-,* and / operators are allowed in the expression. </li>
  <li>The evaluation follows the standard operator precedence.</li>
  <li>The calculator does not support parentheses.</li>
  <li>The calculator handles invalid input "gracefully". It does not output PHP error messages.</li>
</ul>  
<p> Here are some(but not limit to)reasonable test cases:</p >
<ol>
  <li>A basic arithmetic operation: 3+4*5 = 23</li>
  <li>An expression with floating point or negative sign: -3.2+2*4-1/3=4.466666667,3*-2.1*2=-12.6</li>
  <li>Some typos inside operation(e.g. alphabetic letter): Invalid Input Expression! </li>
</ol> 

<?php
// define variables and set to empty values
$exp = "";
$result = "";
$Err = "";

if ($_SERVER["REQUEST_METHOD"] == "GET") {
   $exp = test_input($_GET["exp"]);
   if(empty($exp)){
      //First judge whether the expression is empty, print err.
      $Err = "Input is Empty!";
      echo "<h2>Result:</h2>";
      echo $Err; 
      echo "<br>";
   }else if(preg_match("/\/0/", $exp) && !preg_match("/\/0\.0*[1-9]+/", $exp)){
      //Then if there is a Zero division err.
      $Err = "Zero Division Err!";
      echo "<h2>Result:</h2>";
      echo $Err; 
      echo "<br>";
   }else if(preg_match("/^((-?([1-9]\d*|0))(\\.\\d+)?)([\+\-\*\/]((-?([1-9]\d*|0))(\\.\\d+)?))*$/",$exp)){
      //if there is no problem described before, check that if it is a leagal mathematical expression.
      //if leagal, Calculate it!
      $expx = preg_replace("/-{2}/", "+", $exp);
      //before calculation, replace "--" with "+", 
      //which is regarded leagal(minus a negative num),but cannot be matched by Eval();
      $result = eval("return $expx;");
      echo "<h2>Result:</h2>";
      echo $exp,' = ',$result;
      echo "<br>";
   }else{
      //if not, print Invalid Msg. 
      $Err = "Invalid Input Expression!";
      echo "<h2>Result:</h2>";
      echo $Err; 
      echo "<br>";
   }   
}

function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   return $data;
}

?>


</body>
</html>
