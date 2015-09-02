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
	


	header("content-disposition:attachment;filename=peerevals-compliance-" . $projectid . ".csv");
header("content-type:text/csv");	


//$query=sprintf("SELECT g.name as name, g.groupnum as groupnum, count(e.evaluatee) as submitted,( select count(groupnum) from glue where surveyid=g.surveyid and groupnum=g.groupnum group by groupnum) as groupsize from glue g, evaluation e where g.udid = e.evaluator and g.surveyid=e.surveyid and g.surveyid=%d group by evaluator order by groupnum",$projectid);
//$query = sprintf("select * from grades where surveyid=%u",$projectid);
//echo $query;
$query=sprintf("select distinct g.udid, g.name, g.groupnum, count(evaluation.evaluatee) as submitted, ( select count(groupnum) from glue where surveyid=g.surveyid and groupnum=g.groupnum group by groupnum) as groupsize from glue g LEFT JOIN evaluation on g.surveyid=evaluation.surveyid and g.udid=evaluation.evaluator where g.surveyid=%d group by g.udid order by g.groupnum",$projectid);
$result = mysqli_query($link,$query);
$rowdone=false;
if(mysqli_num_rows($result)){
echo "udid,name,groupnum,submitted,groupsize \r\n"; 
	while($row = mysqli_fetch_array($result)){
		
		echo $row[0] . '"' .$row[1].'"' . "," . $row[2] . "," . $row[3] . "," . $row[4] . "\r\n";
	//echo ",,,,," . $_GET['maxscore'] . "\r\n";
	

		
		
	}//close while
	//print_r($row);

}else{//close if result
  echo mysqli_error($link);
}
	
mysqli_close($link);
?>
