

<?php
/*the security of this page is shaky. I'm allowing the projectid and facid to come from a $GET, but really should probably only look at the session variables. This page trusts the get, once it has matched it against the session. There is no good reason I can think of to do it this way. Should probably stop sending the GET at all, but the match check serves much the same purpose, so I'm leaving well enough alone for now */
require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
if($_SESSION['facid'] == $_GET['facid']){//identity confirmed//valid login
		$projectid = $_GET['proj'];
		$facid = $_GET['facid'];
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
echo '<p >Grade downloads will contain only grades that the instructor has APPROVED. Check the <b>Summary</b> column in the Project Summary table to see which of your grades has been approved. To approve additional grades, select unchecked student names from the Projects menu, and look for the <b>approve</b> button at the bottom of the summary form.</p>';
//echo '<p> can? = '. $facid  .strpos($facid,'can') . '</p>';
if($ptype!=1 && strpos($facid,'can')!== false){
	
	echo '<p><span style="color:orange">NEW! </span><a title="automatically create and populate a Canvas gradebook column with summary grades and comments" href="/git/lti/2015/postGradesToCanvas.php?proj=' . $_GET['proj']. '&facid=' . $_GET['facid']. '&maxscore=' . $maxscore . '" target="canpost">Post Peer Grades to Canvas Gradebook</a></p><iframe src="/git/lti/2015/pending.html" id="canpost" name="canpost" width="100%" height="80" border="0" scrolling="auto"></iframe>';
}
echo '<p><a href="/git/lti/2015/exportCanvas.php?type=0&proj=' . $_GET['proj']. '&facid=' . $_GET['facid']. '&maxscore=' . $maxscore . '" title="Export Canvas compliant csv. You will not need this link if you are launching Peer Evaluation from within Canvas. This legacy download includes grades, but not comments.">Download Peer Grades for Canvas</a></p>';
if($ptype !=1){
	echo '<p><a href="/git/lti/2015/exportSakai6.php?type=0&proj=' . $_GET['proj']  .  '&maxscore2=' . $maxscore_p . '&facid=' . $_GET['facid'] . '">Download Peer Grades with Comments</a></p>';
	
}
echo '<div style="color:#014598">';
if ($ptype!=0){
	echo '<p><a href="/git/lti/2015/exportSakai6.php?type=1&proj=' . $_GET['proj'] . '&maxscore=' . $maxscore .  '&maxscore2=' . $maxscore_p .  '&facid=' . $_GET['facid'] . '">Download Product Grades</a> (instructor submitted)</p>';
	echo '<p><a href="/git/lti/2015/exportSakai6.php?type=1.5&proj=' . $_GET['proj'] . '&maxscore=' . $maxscore .  '&maxscore2=' . $maxscore_p .  '&facid=' . $_GET['facid'] . '">Download Product Evaluations</a> (all student submissions)</p>';
}
if ($ptype==2){
	echo '<p><a href="/git/lti/2015/exportSakai6.php?type=2&proj=' . $_GET['proj'] . '&maxscore=' . $maxscore .  '&maxscore2=' . $maxscore_p .  '&facid=' . $_GET['facid'] . '">Download Combined Grades</a></p>';
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


$export = mysqli_query ($link,$select) ;
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

