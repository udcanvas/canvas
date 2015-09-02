<?php
session_start();
//$emplid=$_SESSION['cas_data']['EMPLID'];
$emplid=$_POST['emplid'];
	include "/home/bkinney/includes/db_peer_sqli.php";
	$custom = strip_tags($_POST['custom']);
	$custom = rtrim($custom,",");//remove trailing comma, and any whitespace
	$strippedcomments = strip_tags($_POST['comments']);
	$query = sprintf("INSERT INTO evaluation (surveyid,evaluator,evaluatee,grade,comment,custom) VALUES (%u,'%s','%s',%d,'%s','%s') ON DUPLICATE KEY UPDATE grade=VALUES(grade), comment=VALUES(comment), custom=VALUES(custom)",
	$_POST['surveyid'],
	mysqli_real_escape_string($link,$_POST['evaluator']),
	mysqli_real_escape_string($link,$_POST['evaluatee']),
	$_POST['grade'],
	mysqli_real_escape_string($link,$strippedcomments),
	mysqli_real_escape_string($link,$custom));
	$result = mysqli_query($link,$query);
	if($result){
		echo "Thank you";
	}else{
		echo mysqli_error($link);
	}
	mysqli_close($link);
?>