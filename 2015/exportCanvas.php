<?php

require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
if($_SESSION['facid'] == $_GET['facid']){//identity confirmed//valid login
		$projectid = $_GET['proj'];
		
	
}else{//load into a div, showing just the links  
	die("Unauthorized. This page can only be accessed from within the Peer Evaluation Page.");
	
}
//session_start();
$emplid=$_SESSION['cas_data']['EMPLID'];
include "/home/bkinney/includes/db_peer_sqli.php";	
//header commands moved inside the result loop
	


	header("content-disposition:attachment;filename=peer_evals" . $projectid . ".csv");
header("content-type:text/csv");	


$query=sprintf("select name, evaluatee, grade from pending where surveyID=%u and !isNull(grade) && evaluatee>2000",$projectid);
//$query = sprintf("select * from grades where surveyid=%u",$projectid);
$result = mysqli_query($link,$query);
if(mysqli_num_rows($result)){

	
	echo "Student,ID,SIS User ID,SIS Login ID,Section,peer" . $projectid . "\r\n";
	//echo ",,,,," . $_GET['maxscore'] . "\r\n";
	while ($row = mysqli_fetch_assoc($result)) {
		
		echo '"' . $row['name'] . '",,';
		echo '"' . $row['evaluatee'] . '",,,';

	
		echo $row['grade'] . "\r\n";
		
	}//close while

}else{//close if result
  echo mysqli_error($link);
}
	
mysqli_close($link);
?>
