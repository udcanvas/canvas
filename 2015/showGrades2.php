<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1); 
require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
//sometimes we include this, otherwise, must get emplid somewhere
if (empty($emplid)) $emplid =  $_POST['emplid'];//$_COOKIE['emplid'];
//session_write_close();
include "/home/bkinney/includes/db_peer_sqli.php";
$formindex=7;
$query0=sprintf("SELECT a.udid, c.surveyid from glue a, glue b, survey c where a.groupNum=b.groupNum and b.udid='%s' and a.udid<2000 and a.surveyid=c.surveyid and b.surveyid=a.surveyid and b.surveyid=c.surveyid and c.active=1 and c.released=1",$emplid);
$result0=mysqli_query($link,$query0);
$extra="";
if(mysqli_num_rows($result0)){
	while($row = mysqli_fetch_assoc($result0)){
		$extra.=" or (surveyid=" . $row['surveyid'] . " and evaluatee=" . $row['udid'] . ")";
	}							 
}
$query = sprintf("select * from released_active where evaluatee='%s'" . $extra . " order by surveyid desc, evaluatee desc", $emplid);
//echo $query;
$result = mysqli_query($link,$query);
function formatGrade($row,&$formindex){
		if($row['evaluatee']*1 >2000){
			if($row['type']==1){//erron
				echo "<p>Peer Evaluations have been disabled for this project</p>";
				return;
			}
			$etype= "Peer";
			$itype="instructions";
		}else{
			if($row['type']==0){//erron
				echo "<p>Product Evaluations have been disabled for this project</p>";
				return;
			}
			$etype= "Product";
			$itype="instructions_p";
		}
	
	$evaluator_name=$etype . " Grade";
	$formindex++;
	$custom = $row['custom'];
		$grade=$row['grade'];
		$comment=$row['comment'];
		$maxscore="";
		$released=true;
		$instructions=$row[$itype];
		$displaytype="accordion";//this should hide the submit?
		include "/home/bkinney/includes/studentform5.php";
}
$countdown =mysqli_num_rows($result);
if($countdown){
	echo '<p class="instructions" >Select a project name to expand a feedback panel</p><div id="accordion3">';
	
	while ($row = mysqli_fetch_assoc($result)) {	

		if($row['type']==1 && $row['evaluatee']>2000)continue;//peer evals disabled- move on
		
		//if($skip==0){
			$name= $row['name'];
		echo '<h3 ><a href="#top">' . $name . '</a></h3>';
		echo '<div>';
		//}
		//echo $row['evaluator'] . " " . $emplid . "<br>";

		formatGrade($row,$formindex);//don
		if($row['type']==2){//look for corresponding product && $skip==0
			$row=mysqli_fetch_assoc($result);
			formatGrade($row,$formindex);
		
			
		}//else{
		echo '</div>';
		//$skip=0;
		//}
	}//close while
	echo "</div>";
		echo '<script type="text/javascript">
	$( "#accordion3" ).accordion({
	
		collapsible: true,
		autoHeight:false,
		active:false});
	</script>';

}else{
	echo '<p class="instructions" >Your feedback will appear below after your instructor has approved it.</p>';
}//close if result

mysqli_close($link);
?>