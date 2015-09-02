<?php
require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
$emplid = $_POST['emplid'];//mysql_real_escape_string();
include "/home/bkinney/includes/db_peer_sqli.php";
$formindex=0;
$evaluatee = $_POST['id'];//mysql_real_escape_string();
$surveyid =  $_POST['survey'];

$facid = $_POST['facid'];
$membername = $_POST['name'];
$showall =  $_POST['showall']=="true";
$type = $_POST['type'];
$submitAll = $_POST['submitAll']=="true";
$evaluator=$_POST['evaluator'];
$isProduct=$evaluatee*1<2000;
//if(empty($_POST['submitAll']))$submitAll=false;

$roster = array();
if($submitAll){//echo "true";

	$query = sprintf("select udid from glue where surveyID=%u and groupnum>1",$surveyid);
	$resulta = mysqli_query($link,$query);
	
	if(mysqli_num_rows($resulta)){
		while($row = mysqli_fetch_array($resulta)){
			$sid = $row[0];
			//echo $sid;function_exists(compileSummary);
			$roster[]= (string) $sid;
		}
	}else{
		//echo "no result";
	}
}else{
	//echo "false";
	$roster[]=$_POST['id'];//just one member
	//echo "roster length =" . count($roster);
}




//grab the instnuctions for this survey

if($isProduct ){
	$query=sprintf("SELECT instructions_p, maxscore_p FROM survey WHERE surveyID=%u",$surveyid);
}else{
	$query=sprintf("SELECT instructions, maxscore FROM survey WHERE surveyID=%u",$surveyid);
}

$result0 =  mysqli_query($link,$query);
$row = mysqli_fetch_array($result0, MYSQL_NUM);
$instructions = $row[0];
if($_POST['copy']=="true"){
	echo $instructions;
	mysqli_close($link);
	exit();
}else{
	$maxscore=$row[1];
}
mysqli_free_result($result0);
//grab max score for this survey
//$query=sprintf("SELECT maxscore FROM survey WHERE surveyID=%u",$surveyid);
/*$query=sprintf("SELECT %s FROM survey WHERE surveyID=%u",
$isProduct ? "maxscore_p" : "maxscore",
$surveyid);

$result0 =  mysqli_query($link,$query);
//$maxscore = mysqli_result($result0,0);
mysqli_free_result($result0);*/
foreach($roster as $stuid){
		$grade=NULL;
	$accepted=0;
	$released=0;
	//if($stuid == $emplid && $submitAll)continue;
	
	$comment="";

	$query=sprintf("SELECT * FROM grades WHERE evaluatee='%s' AND surveyid=%u",mysqli_real_escape_string($link,$stuid),$surveyid);//grades is a view that only shows instructor submitted summary scores
	//$query=sprintf("select s.surveyID AS surveyID,s.name AS name,e.Grade AS grade,e.custom AS custom,e.comment AS comment,e.Evaluatee AS evaluatee,e.released AS released from (survey s join evaluation e) where ((e.evaluatee='%s' and s.surveyID=%u and s.surveyID = e.surveyID) and (s.instructorID = e.Evaluator))",
	//mysqli_real_escape_string($link,$stuid),$surveyid);

	$resultb = mysqli_query($link,$query);
	if(mysqli_num_rows($resultb)){
		while ($row = mysqli_fetch_assoc($resultb)) {
		//echo $row['evaluator'] . " " . $emplid . "<br>";
		
			$grade = $row['grade'];
			$comment = $row['comment'];
			$custom = $row['custom'];
			$released = $row['released'];
			$accepted = 1;
		
		}//close while
		//mysqli_free_result($resultb);

	}//close if resul	

//different query for products vs peers
	if($stuid*1>2000){//original query
		
	$query=sprintf("SELECT * FROM student_evals_all WHERE evaluatee='%s' AND surveyid=%u",
	mysqli_real_escape_string($link,$stuid),$surveyid);
	}else{
		$query=sprintf("SELECT * FROM product_evals_all WHERE evaluatee='%s' AND surveyid=%u",
	mysqli_real_escape_string($link,$stuid),$surveyid);
	}

//calculate average results
	$resultq = mysqli_query($link,$query);
	$groupsize = mysqli_num_rows($resultq);
//echo "gs=" . $groupsize . " id=" . $query;
	if(mysqli_num_rows($resultq)){
		
		$n=0;
		$sum = 0;
		$pending_comment="";
		
			
		$srows = array();
		$categories = array();	
			while ($row = mysqli_fetch_assoc($resultq)) {
				//echo $row['evaluator'] . " " . $row['evaluatee'] . "<br>";
				//if(!$row['evaluator']!=$emplid){add these up
					$srows[] =$row ;
					
					if($row['grade']!=NULL){
						$pending_comment .= $row['comment'] . " :: ";
						$temp = explode(",",$row['custom']);
						//array_pop($temp);//I got rid of the trailing comma
						if(count($categories)==0){
							for($x=0;$x<count($temp);$x++ ){
								$categories[]=0;
							}
						}
						for($x=0;$x<count($temp);$x++){
							$categories[$x] += $temp[$x]*1;
							
						}
							
						$sum += $row['grade'];
						$n++;
					}
				
			}//close while
			
		if(!$accepted ){//average all other scores

			if($n){
				$average = $sum/$n;
				$grade = round($average*10)/10;
				$custom = "";
				//echo count($categories);
				foreach($categories as $x){
					$entry = $x/$n;
					//echo $entry;
					$custom .= round($entry*10)/10 . ',';
				}
				
			}else{
				$grade="";
				$custom="";
				$pending_comment = "no evaluations";
			}
			$comment = $pending_comment;
		}
			
		
		if($type=="eva"){//person is coming in as a faculty member
			$evaluatee = $stuid;
			//$evaluator = $emplid;
			$displaytype = "faculty";
			//echo $custom;
			if($submitAll){
				//$evaluator=$facid;
				include "/www/git/lti/2015/submitEval2.php";
			}else{
				include "/home/bkinney/includes/studentform5.php";
			//echo $accepted . "case3";
			//now show all the rest of the results
			echo '<div class="for">';
			echo "<h3>Feedback Submitted for " . $membername . "</h3>";
			$missing = 0;
			$pending = "<b>No evaluation submitted by: </b>";
			echo '<div id="accordion" >';
			for($s=0;$s<count($srows);$s++){
				$grade = $srows[$s]['grade'];
				if($grade == NULL){
					$missing++;
					$pending .= $srows[$s]['evaluator_name'] . ", ";
				}else{
					$comment = $srows[$s]['comment'];
					$evaluator_name = $srows[$s]['evaluator_name'];
					$displaytype="accordion";
					$custom = $srows[$s]['custom'];
					include "/home/bkinney/includes/studentform5.php";
	//echo $accepted . "case0";
				}
				
			}
			echo '</div>';
			if($missing) echo $pending;
			echo '</div>';//end .by -> need to hide this when the accordion is empty
			echo '<script>
			if($("#accordion").html()=="")$(".for").hide();
			</script>';
			}
		}/*else if($stuid == $emplid){//just the student
				//echo "Grade: " . $average . " (average of " . $n . " submitted scores)";
			if($released){
				echo "<p>Summary Grade: " . $grade . "</p>";
				echo "Comment: " . $comment;
			}else{
				echo "This grade has not been released";
			}
			echo "<hr>";
		}//close if eva*/
	}else if($type=="eva" && !$submitAll){
	$grade=$comment=$custom="";
	$displaytype =  "faculty";
	//$evaluator=$emplid;
	include "/home/bkinney/includes/studentform5.php";
	}//close if resultq
	// now run again for feedback submitted by
//mysqli_free_result($result);
	if($type=="eva" && !$submitAll){//
		$query=sprintf("SELECT * FROM assigned_evals WHERE evaluator='%s' AND surveyid=%u",
		mysqli_real_escape_string($link,$stuid),$surveyid);
		echo '<div class="by">';
		echo "<h3>Feedback Submitted by " . $membername . "</h3>";
		$result = mysqli_query($link,$query);
		if(mysqli_num_rows($result)){
			
			
			echo '<div id="accordion2" >';
			$pending = "<b>No evaluations for: </b>";
			$missing = 0;
			while ($row = mysqli_fetch_assoc($result)) {
			
				$grade = $row['grade'];
				
				if($grade == NULL){
					$missing++;
					$pending .= $row['evaluatee_name'] . ", ";
				}else{
					$comment = $row['comment'];
				$evaluator_name = $row['evaluatee_name'];
					$displaytype="accordion";
					$custom = $row['custom'];
					include "/home/bkinney/includes/studentform5.php";
				//echo $accepted . "case1";
				}//close if grade
			
		
			}//close while
			echo "</div>";//close the accordion
			if($missing) echo $pending;
			echo "</div>";//end .for
			
		}else{
			echo '<script>
			$(".by").hide();
			</script>';
		}
		
		// end submitted by
	}else if(!$submitAll && $type=="stu"){//student view, why !submitall?
		$query=sprintf("SELECT * FROM evaluation WHERE evaluatee='%s' AND surveyID=%u AND evaluator='%s'",
			mysqli_real_escape_string($link,$stuid),$surveyid,mysqli_real_escape_string($link,$emplid));
					$evaluatee = $stuid;
				//$evaluator = $emplid;
			$result = mysqli_query($link,$query);
			
			if(mysqli_num_rows($result)){
				$row = mysqli_fetch_assoc($result) ;
				$released = $row['released'];
				$grade=$row['Grade'];
				$comment=$row['comment'];
				$custom=$row['custom'];
				
			}else{
				
				$grade="";
				$comment="";
				$custom="";
				
			}
		include "/home/bkinney/includes/studentform5.php";
		//echo $accepted . "case2";
	}//close not showall
}// close if submitAll
if($submitAll){
	$status = $_POST['status']; 
$query =sprintf("update survey set released=%u where surveyID=%u",$status, $surveyid);
	$result = mysqli_query($link,$query);
//$query =sprintf("update survey, evaluation set survey.released=%u, evaluation.released=%u where survey.surveyid=%u and evaluation.surveyid=%u",$status, $status, $surveyid, $surveyid);
	$result = mysqli_query($link,$query);	
	if($result){
		 echo "Your grades have been released";
	}else{
		echo "Your query has failed. Please contact bkinney@udel.edu<br>" .$query;
	}
}
mysqli_close($link);
?>