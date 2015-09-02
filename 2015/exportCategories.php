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


$query=sprintf("SELECT g.groupnum, e.evaluator, e.evaluatee, e.custom FROM `evaluation` e, glue g WHERE e.surveyid=%d and e.evaluator<>%d and g.surveyid=e.surveyid and g.udid=e.evaluatee and g.groupnum>1 order by g.groupnum",$projectid,$_GET['facid']);
//$query = sprintf("select * from grades where surveyid=%u",$projectid);
//echo $query;
$result = mysqli_query($link,$query);
$rowdone=false;
if(mysqli_num_rows($result)){

	while($row = mysqli_fetch_array($result)){
		if(!$rowdone){
			$custom = $row[3];
			$numcat = count(explode(",",$custom));
			echo "group,evaluator,evaluatee,";
			for($i=1;$i<=$numcat;$i++){
				echo "cat" . $i . ",";
			}
			echo "\r\n";
			$rowdone=true;
		}
		echo $row[0] . "," . $row[1] . "," . $row[2] . "," . $row[3] . "\r\n";
	//echo ",,,,," . $_GET['maxscore'] . "\r\n";
	

		
		
	}//close while
	//print_r($row);

}else{//close if result
  echo mysqli_error($link);
}
	
mysqli_close($link);
?>
