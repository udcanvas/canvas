<?php

//ini_set('display_errors', 1); 
include "/home/bkinney/includes/mysessions.php";
mysession_start();
//$emplid=$_SESSION['cas_data']['EMPLID'];
$emplid=$_POST['emplid'];
	include "/home/bkinney/includes/db_peer_sqli.php";//?;

if(!empty($_POST['roster'])){
	$surveyID = get_val($_POST,'surveyid');
	$strippedinstructions = strip_tags(get_val($_POST,'custom'),'<b><i><a><input><p><strong><div><br><span>');
	$strippedinstructions2=strip_tags(get_val($_POST,'custom2'),'<b><i><a><input><p><strong><div><br><span>');
	$ptype = get_val($_POST,'projecttype');
	$active = get_val($_POST,'active');
	$success = true;
	if($surveyID=="null"){	
		$query = sprintf("INSERT INTO survey (name,instructorID,startdate,enddate,includeself,active,instructions,instructions_p,maxscore,maxscore_p,type) VALUES ('%s','%s','%s','%s',%b,%b,'%s','%s',%u,%u,%u)",
		mysqli_real_escape_string($link,get_val($_POST,'projectName')),
		mysqli_real_escape_string($link,get_val($_POST,'instructorID')),
		get_val($_POST,'startdate'),
		get_val($_POST,'enddate'),
		get_val($_POST,'includeself'),
		$active,
		mysqli_real_escape_string($link,$strippedinstructions),
		mysqli_real_escape_string($link,$strippedinstructions2),
		get_val($_POST,'maxscore'),
		get_val($_POST,'maxscore2'),
		$ptype
		);
		//echo $query;
	}else{
		$rquery = sprintf("DELETE from glue WHERE surveyID=%u",$surveyID);
		mysqli_query($link,$rquery);
		$query = sprintf("UPDATE survey SET name='%s', instructorID='%s', includeself=%b, active=%b, instructions='%s', maxscore=%u, maxscore_p=%u, type=%u, instructions_p='%s' WHERE surveyID=%u",
		mysqli_real_escape_string($link,get_val($_POST,'projectName')),
		mysqli_real_escape_string($link,get_val($_POST,'instructorID')),
		get_val($_POST,'includeself'),
		$active,
		mysqli_real_escape_string($link,$strippedinstructions),
		get_val($_POST,'maxscore'),
		get_val($_POST,'maxscore2'),
		$ptype,
		mysqli_real_escape_string($link,$strippedinstructions2),
		$surveyID
		
		); 
		//echo $query;
	}
	$result = mysqli_query($link,$query);
	if($surveyID=="null"){
$surveyID = mysqli_insert_id($link);
	}
	$groups_array = explode("<nextgroup>",get_val($_POST,'roster'));
	
	for($g=0;$g< count($groups_array); $g++){
		$members = explode('<br>',$groups_array[$g]);
		for($m=0;$m<count($members);$m++){
			$memparts = explode(' | ',$members[$m]);
			$fullname = $memparts[0];
			$udid = $memparts[1];
			$email = $memparts[2];
			$groupNum = $g + 1;
			//echo $fullname . " ";
			$query = sprintf("INSERT INTO glue (Name,udid,email,surveyID,groupNum) VALUES ('%s',%d,'%s',%d,%d)",
																					mysqli_real_escape_string($link,$fullname),
																					$udid,
																					$email,
																					$surveyID,
																					$groupNum);
			//echo $query;
							 $result = mysqli_query($link,$query);
							
			if($result){
				
			} else{
				$success = false;
				echo $query;
			}							
		}//close member loop
	}//close group loop
	if($success) echo "To access your new project, click on its name in the Projects menu.";	
	
}else if(get_val($_POST,'active')!=''){
	$query = sprintf("UPDATE survey SET active=%d WHERE surveyID=%u",
	get_val($_POST,'active'),
	get_val($_POST,'id')
	);
	$result=mysqli_query($link,$query);
	if($result){
		echo "Your survey has been updated";
	}
}else if(get_val($_POST,'id')){

	$query = sprintf("DELETE FROM survey WHERE surveyID=%u",get_val($_POST,'id')*1);
	
	$result = mysqli_query($link,$query);
	if($result){
		//echo "Your survey has been deleted. ";
	}
	$query = sprintf("DELETE FROM evaluation WHERE surveyID=%u",get_val($_POST,'id')*1);
	$result = mysqli_query($link,$query);
		$query = sprintf("DELETE FROM glue WHERE surveyID=%u",get_val($_POST,'id')*1);
	$result = mysqli_query($link,$query);
	if($result){
		echo "Your survey has been deleted";
	}

}else{//if no roster, just list the projects>groups
//$facid = $facid | $emplid;//facid is for testing
//formulate query

if(empty($facid))$facid = get_val($_POST,'facid');
	//$query=sprintf("SELECT * FROM instructor4 WHERE instructorID='%s'",mysqli_real_escape_string($link,$facid));
	$query=sprintf("select c.grade AS grade,a.active AS active,a.includeself AS includeself,a.instructorID AS instructorID,a.name AS surveyName,a.surveyID AS surveyID, a.type as type, a.released as released, b.Name AS studentName,b.udid AS udid,b.groupNum AS groupNum,b.email AS email from (survey a join (glue b left join evaluation c on(((b.surveyID = c.surveyID and c.evaluator='%s') and (b.udid = c.evaluatee) )))) where (a.surveyID = b.surveyID) and a.instructorid = '%s' order by a.active desc,a.surveyID desc,b.groupNum,b.Name",mysqli_real_escape_string($link,$facid),mysqli_real_escape_string($link,$facid));
	//
	$result = mysqli_query($link,$query);

	if($result){
		
		echo "<ul class=formlist>";
		
		$currSurvey = 0;
		 
		while ($row = mysqli_fetch_assoc($result)) {
		
			if($currSurvey != $row['surveyID']){//new survey
			//$include=mysqli_query($link,"select includeself from survey where surveyID=".$row['surveyID']);
			//$includerow=mysqli_$1($link,fetch_assoc($include);
			$includeself=$row['includeself'];
			/////////
			//$releasequery=mysqli_query($link,"select released from survey where surveyID=".$row['surveyID']);
			//$releaserow=mysqli_$1($link,fetch_assoc($releasequery);
			$rstatus=$row['released'];
			//$typequery=mysqli_query($link,"select type from survey where surveyID=".$row['surveyID']);
			//$typerow=mysqli_$1($link,fetch_assoc($typequery);
			$tstatus=$row['type'];
			
			////////////////
			if($currSurvey > 0) echo "</ul>";
			$currGroup = 0;
				$currSurvey = $row['surveyID'];
				//if(true){
					
				if($row['active']==1){
					echo '<li ';
				}else{
					echo '<li class="archived" ';
				}
				echo ' data-self="' . $row['includeself'] . '"';
				echo ' data-released="' . $rstatus . '"';
				echo ' id="eval' .  $row['surveyID'] . '"';
				echo ' data-type="' .  $tstatus . '"';
				//if($row['active'] == 0) echo " class=\"archived\" ";
				echo "><a href=# onclick=showGroup(" . $row['surveyID'] . ",'eval')>" . $row['surveyName'] . "</a></li><ul>";
			}//close if new survey
			if($currGroup != $row['groupNum']){//start new group
				if($currGroup>0)echo "</ul><ul>";//end the previous group ul
				$currGroup = $row['groupNum'];
				echo "<li class='label'>Group " . $row['groupNum'] . "</li>";
			}//close if new group
			
			echo "<li id=\"";
			echo $row['udid'] . "\"";
			if($tstatus==1 && $row['udid']*1>2000) echo ' class="hidden" ';//don't show if type = project only, but leave it in for head count.
			echo ' data-email="' .  $row['email'] . '"';
			/*if($row['active']==0){
				echo " class=\"archived\"";
			} else */
			//echo ' data-grade="' .  $row['grade'] . '"';
			if(isset($row['grade'])){
				echo " class=\"complete\"";
			}else{
				if(!$includeself){
					$xquery = sprintf("SELECT * FROM evaluation where surveyID=%u and Evaluatee='%s' and Evaluatee<>Evaluator",$currSurvey,$row['udid']);
				}else{
					$xquery = sprintf("SELECT * FROM evaluation where surveyID=%u and Evaluatee='%s'",$currSurvey,$row['udid']);
				}
				$xresult = mysqli_query($link,$xquery);
				if($xresult){
				$numsubmitted = mysqli_num_rows($xresult);
				}else{
					$numsubmitted=0;
				}
				echo " class=\"waiting\" data-complete=\"" . $numsubmitted . "\"";
			}
			echo ">";
			echo $row['studentName'];
			echo '</li>';
				
			
			
			
		}//close while
		echo "</ul></ul>";
	}//close no result

}//close no roster
mysqli_close($link);

?>