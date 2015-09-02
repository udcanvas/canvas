<?php
/*
moving analytics links to a new location. This page started as a copy of exportSakai6,


the security of this page is shaky. I'm allowing the projectid and facid to come from a $GET, but really should probably only look at the session variables. This page trusts the get, once it has matched it against the session. There is no good reason I can think of to do it this way. Should probably stop sending the GET at all, but the match check serves much the same purpose, so I'm leaving well enough alone for now

 */
require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
if($_SESSION['facid'] == $_GET['facid']){//identity confirmed//valid login
		$projectid = $_GET['proj'];
	
}else if ($_GET['type']!="unknown"){
	echo $_SESSION['facid'];
	die("Your session has timed out. <a href='javascript:history.go(-1)' >Reauthenticate</a>");
	
}

$emplid=$_SESSION['cas_data']['EMPLID'];
include "/home/bkinney/includes/db_peer_sqli.php";
//header commands moved inside the result loop
	if($_GET['type']=="unknown"){//load into a div, showing just the links  
	//get type from db and display links	
	$query0=sprintf("select type, maxscore, maxscore_p from survey where surveyID=%u",$projectid);
$result0 = mysqli_query($link,$query0);
$row = mysqli_fetch_assoc($result0);
$ptype=$row['type'];	
$maxscore=$row['maxscore'];
$maxscore_p=$row['maxscore_p'];
echo '<p >Analytics downloads are provided at the request of faculty. For example, the Peer Category Scores data was requested by a professor in the Engineering department, who wanted to compare average rubric scores across groups. Compliance data was requested by a member of the Physics department who uses it to hold student accountable for completion of all required evaluations.</p><p> Mouse over each link to learn more about the data included. </p><p> Contact <a href="mailto:bkinney@udel.edu">Becky Kinney</a> or submit  a <a href="https://www.google.com/moderator/#15/e=1aae07&t=1aae07.40">Feature Request</a> if you require other data from this system.</p>';

echo '<div style="color:#014598">';


if(true){//$ptype==0 - categories get a bit weird if both, but we'll let users sort that out for themselves
	echo '<p title="This generates a large file containing all student submitted rubric scores, but no comments." ><span style="color:orange">NEW!</span> <a href="/git/lti/2015/exportCategories.php?proj=' . $_GET['proj']  .  '&facid=' . $_GET['facid'] . '">Download Peer Category Scores by Group</a></p>';
	}
	if($ptype==0){//this won't work well when product evals are required
	echo '<p title="Data for comparing the number of peer evaluations completed by each student against his/her group size. Please note that the required submissions will be one less than the group size when self-evaluation is not required." ><span style="color:orange">NEW!</span> <a href="/git/lti/2015/accountability.php?proj=' . $_GET['proj']  .  '&facid=' . $_GET['facid'] . '">Download Compliance Data</a></p>';
	}
echo '</div>';	
mysqli_close($link);
	exit();
	}else{//return requested
	$ptype=$_GET['type'];
	$maxscore=$_GET['maxscore'];
	$maxscore_p=$_GET['maxscore2'];
	header("content-disposition:attachment;filename=peer_evals" . $projectid . ".csv");
header("content-type:text/csv");	
		





	}
if($ptype==1.5){
	$select=sprintf("SELECT evaluatee_name, evaluator_name, custom, grade, comment FROM `product_evals_all` WHERE evaluator<>%s and surveyid=%d and !isnull(grade) order by evaluatee_name",$_GET['facid'],$_GET['proj']);


$export = mysqli_query ($link, $select ) ;
$header = 'name,evaluator,rubric,grade,comment';

while( $row = mysqli_fetch_row( $export ) )
{
    $line = '';
    foreach( $row as $value )
    {                                            
        if ( ( !isset( $value ) ) || ( $value == "" ) )
        {
            $value = ",";
        }
        else
        {
            $value = str_replace( '"' , '""' , $value );
			$value = str_replace( "\n" , "  " , $value );
			$value = str_replace( "\r" , "  " , $value );
            $value = '"' . $value . '"' . ",";
        }
        $line .= $value;
    }
    $data .= trim( $line ) . "\n";
}

if ( $data == "" )
{
    $data = "\n(0) Records Found!\n";                        
}



print "$header\n$data";
mysqli_close($link);
die();
}

$query=sprintf("select * from pending where surveyID=%u",$projectid);
//$query = sprintf("select * from grades where surveyid=%u",$projectid);
$result = mysqli_query($link,$query);
if(mysqli_num_rows($result)){

	if($ptype==0){	
		echo "Student ID,Name,comments,peer" . $projectid . " [" . $maxscore . "]\r\n";
		while ($row = mysqli_fetch_assoc($result)) {
			
			echo $row['evaluatee'] . ",";
			echo '"' . $row['name'] . '",';
			echo '"' . $row['comment'] . '",';
		
			echo $row['grade'] . "\r\n";
			
		}//close while
	}else if($ptype==1){//project grades
		echo "Student ID,Name,comments,product" . $projectid . " [" . $maxscore_p . "]\r\n";
		while ($row = mysqli_fetch_assoc($result)) {
			if($row['evaluatee']*1>2000)continue;//there should be none of these, but in case type was changed...
			echo $row['evaluatee'] . ",";
			echo '"' . $row['name'] . '",';
			echo '"' . $row['comment'] . '",';
		
			echo $row['grade'] . "\r\n";
			
		}//close while
	}else{//both
	
	$groupgrades = array();//store product grades here
	$groupcomments = array();
		echo "Student ID,Name,peer" . $projectid . " [" . $maxscore . "], product" . $projectid . " [" . $maxscore_p . "]\r\n";
		$datacells = "";
		while ($row = mysqli_fetch_assoc($result)) {
			if($row['evaluatee']*1 <2000){
				$gn = $row['groupNum']*1;
				$groupgrades[$gn]=$row['grade'];
				
				
				continue;
			
			}//close if
			$numgroups = max($numgroups,$row['groupNum']*1);
			$datacells .= $row['evaluatee'] . ",";
			$datacells .= '"' . $row['name'] . '",';
			
		
			$datacells .= $row['grade'] .",gg" . $row['groupNum'] . ",gc" . $row['groupNum'] . "\r\n";
			
		}//close while
		for($i=0;$i<=$numgroups;$i++){
			
			$datacells = str_replace("gg" . $i,$groupgrades[$i] ? $groupgrades[$i] : "",$datacells);
			$datacells = str_replace("gc" . $i,$groupcomments[$i] ? $groupcomments[$i] : "",$datacells);
		}
			
		echo $datacells;
	}//close if both

}else{//close if result
  echo mysqli_error($link);
}
	
mysqli_close($link);
?>
