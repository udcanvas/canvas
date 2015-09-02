<?php

require_once "/home/bkinney/includes/mysessions.php";

mysession_start();



if($_SESSION['facid'] == $_GET['facid']){//identity confirmed//valid login
		$projectid = $_GET['proj'];
		$outof = $_GET['maxscore'];
		$aid=$_GET['aid'];//need to generate this first
		$uri = "/api/v1/courses/". $_SESSION['_basic_lti_context']['custom_canvas_course_id'] ."/assignments";
			$token = $_SESSION['token'];
	$domain = 'udel.instructure.com';
	include "../canvasapi.php";
	
	$api = new CanvasAPI($token,$domain,$_SESSION['_basic_lti_context']['custom_canvas_user_id']);
	if(get_val($_GET,'endpoint')){
		$endpoint = str_replace('https://'.$domain,'', $_GET['endpoint']);
		//echo $endpoint;
		$queueStatus = $api->get_canvas($endpoint,false);
		echo '<p>status: ' . $queueStatus['workflow_state'];
		echo '</p><p>percent completed: ' . $queueStatus['completion'] . '</p>';
			if($queueStatus['completion']*1<100){
			echo '<a href="/git/lti/2015/postGradesToCanvas.php?endpoint='. $endpoint.'&facid=' . $_GET['facid'].'" >Check again</a>';
			//<a title="automatically create and populate a Canvas gradebook column with summary grades and comments" href="/peer/2015/postGradesToCanvas.php?proj=' . $_GET['proj']. . '&maxscore=' . $maxscore . '" target="canpost">
		}
		exit();
	}
	if(!get_val($_GET,"aid")){
	//display list of assignments
		
	
	
		$assignments = $api->get_canvas($uri);
		$foundone=false;

		foreach($assignments as $assignment){
		
			if($assignment['integration_id']=="peer".$_GET['proj']){
				$foundone=true;
				$aid=$assignment['id'];
				break;
				
			}
		}
	
		if(!$foundone){//create new assignment
	
			$endpoint=$uri . "&assignment[name]=peer" .$projectid . "&assignment[integration_id]=peer" . $projectid . "&assignment[points_possible]=" . $outof . "&assignment[grading_type]=points&assignment[published]=1";
			echo  '<br>' . $endpoint;
			$assignment = $api->post_canvas($endpoint,"POST");
			//echo '<br>';
			//print_r($assignment);
			$aid = $assignment['id'];
		}else{
			//echo "found existing assignment #" . $aid;
		}
	}else{
		//echo "selected aid";
	}

}else{//load into a div, showing just the links  
	die("Unauthorized. This page can only be accessed from within the Peer Evaluation Page.");
	
}

//session_start();
$emplid=$_SESSION['cas_data']['EMPLID'];
include "/home/bkinney/includes/db_peer_sqli.php";	
//header commands moved inside the result loop
	


	//header("content-disposition:attachment;filename=peer_evals" . $projectid . ".csv");
//header("content-type:text/csv");	


$query=sprintf("select name, evaluatee, grade, comment from pending where surveyID=%u and !isNull(grade) && evaluatee>2000",$projectid);
//$query = sprintf("select * from grades where surveyid=%u",$projectid);
$result = mysqli_query($link,$query);
if(mysqli_num_rows($result)){

	$args='/api/v1/courses/' . $_SESSION['_basic_lti_context']['custom_canvas_course_id'] .'/assignments/' . $aid . '/submissions/update_grades?as_user_id='.$_SESSION['_basic_lti_context']['custom_canvas_user_id'];
	//echo "Student,ID,SIS User ID,SIS Login ID,Section,peer" . $projectid . "\r\n";
	//echo ",,,,," . $_GET['maxscore'] . "\r\n";
	while ($row = mysqli_fetch_assoc($result)) {
		$args .= '&grade_data[sis_user_id:'.$row['evaluatee'].'][posted_grade]='.$row['grade'];
		
		$args .= '&grade_data[sis_user_id:'.$row['evaluatee'].'][text_comment]='.urlencode($row['comment']);
		
	}//close while
	
	//create assignment
	//$endpoint="/api/v1/courses/COURSEID/assignments/ASSIGNMENTID/submissions/update_grades?
	//grade_data[sis_user_id:$row['evaluatee'][posted_grade]=&$row['grade'];
	//echo $args;
$update = $api->post_canvas($args,"POST");
		//echo '<br>';
		if($update)echo 'Your grades have been queued for posting. If you have a large student roster, it may take a few minutes to complete. You may safely exit this interface or close your browser without interrupting the update.  <a href="/git/lti/2015/postGradesToCanvas.php?facid=' . $_GET['facid'] . '&endpoint='.$update['url'].'">Check status</a>';
		
}else{//close if result
  echo mysqli_error($link);
}
	
mysqli_close($link);
?>
