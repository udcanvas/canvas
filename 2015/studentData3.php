<?php
//
//sometimes we include this, otherwise, must get emplid somewhere
if (empty($emplid)) $emplid =  $_POST['emplid'];//$_COOKIE['emplid'];
//formulate query
include "/home/bkinney/includes/db_peer_sqli.php";
$query= sprintf("SELECT * FROM peer_evals WHERE surveyid in (select surveyid from survey where active=1) and evaluator='%s' && type<>1 && evaluatee>2000 ORDER BY surveyid DESC",mysqli_real_escape_string($link,$emplid));
$query2=sprintf("SELECT * FROM product_evals WHERE surveyid in (select surveyid from survey where active=1) and evaluator='%s'  ORDER BY surveyid DESC, evaluatee_name",mysqli_real_escape_string($link,$emplid));
$result2=mysqli_query($link,$query2);
$result = mysqli_query($link,$query);

	
	if($result){//peer evals
		$currSurvey=0;
		echo "<ul class=formlist>";
		while ($row = mysqli_fetch_assoc($result)) {
		
			if($currSurvey != $row['surveyid']){//new survey
			if($currSurvey !=0) echo "</ul>";
			
				$currSurvey = $row['surveyid'];
			
				echo "<li id=\"stu" . $row['surveyid'] . "\" ><a href=# onclick=showGroup(" . $row['surveyid'] . ",'stu')>" . $row['surveyname'] . "</a></li><ul>";
			}//close if new survey
		//close if new group	
		//if($row['evaluatee'] == $row['evaluator'] && $row['includeself']==0)continue;
			$grade = $row['grade'];
			if($grade==NULL){
				$status = "pending";
			}else{
				$status = "complete";
			}
			echo "<li id=\"";
			echo $row['evaluatee'] . "\" class=\"" . $status . "\">";
			echo $row['evaluatee_name'];
			echo '</li>';
				
			
			
			
		}//close while
		echo "</ul></ul>";
	}else{
		echo "no result " . $query;
	}//close no result
	if($result2){
	echo "<h3>Product Evaluations</h3>";
		echo "<ul class=formlist>";
		$currSurvey = 0;
		while ($row2 = mysqli_fetch_assoc($result2)) {
		
			if($currSurvey != $row2['surveyid']){//new survey
			if($currSurvey !=0) echo "</ul>";
			
				$currSurvey = $row2['surveyid'];
			
				echo "<li id=\"prod" . $row2['surveyid'] . "\" ><a href=# onclick=showGroup(" . $row2['surveyid'] . ",'prod')>" . $row2['surveyname'] . "</a></li><ul>";
			}//close if new survey
		//close if new group	
		//if($row['evaluatee'] == $row['evaluator'] && $row['includeself']==0)continue;
			$grade = $row2['grade'];
			if($grade==NULL){
				$status = "pending";
			}else{
				$status = "complete";
			}
			echo "<li id=\"";
			echo $row2['evaluatee'] . "\" class=\"" . $status . "\">";
			echo $row2['evaluatee_name'];
			echo '</li>';
				
			
			
			
		}//close while
			echo "</ul>";
		}else{
			echo "no result " . $result;
		}//done with product listing
		//now grab the peers
	
mysqli_close($link);
?>


