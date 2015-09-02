<?php
session_start();
$emplid=$_SESSION['cas_data']['EMPLID'];
//echo "what is my " . $evaluatee;
if(!empty($_POST['evaluatee'])){
	
	include "/home/bkinney/includes/db_peer_sqli.php";
	$custom = strip_tags($_POST['custom']);
	$custom = rtrim($custom,",");//remove trailing comma, and any whitespace
	$strippedcomments = strip_tags($_POST['comments']);
	$grade = $_POST['grade'];
	$surveyid=$_POST['surveyid'];
	$evaluator = $_POST['evaluator'];
	$evaluatee = $_POST['evaluatee'];
}else{
//$evaluatee=$emplid;
$strippedcomments = strip_tags($comment);	
}

	$query = sprintf("INSERT INTO evaluation (surveyid,evaluator,evaluatee,grade,comment,custom) VALUES (%u,'%s','%s',%d,'%s','%s') ON DUPLICATE KEY UPDATE grade=VALUES(grade), comment=VALUES(comment), custom=VALUES(custom)",
	$surveyid,
	mysqli_real_escape_string($link,$evaluator),
	mysqli_real_escape_string($link,$evaluatee),
	//$evaluatee,
	$grade,
	mysqli_real_escape_string($link,$strippedcomments),
	mysqli_real_escape_string($link,$custom));
	$result = mysqli_query($link,$query);
	if($result){
		
	}else{
		echo mysqli_error($link);
	}
if(!$submitAll)	mysqli_close($link);
?>