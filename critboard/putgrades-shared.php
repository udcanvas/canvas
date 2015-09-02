<?php

	require_once "/home/bkinney/includes/mysessions.php";
	mysession_start();

	$token = $_SESSION['token'];
	
	include_once("/www/canvas/canvasapi.php");
	
	
	$access_key = $_SESSION['token'];
	$domain = $_SESSION['_basic_lti_context']['custom_domain_url'];
	$api = new CanvasAPI($access_key,$domain);
	if(get_val($_GET,'endpoint')){
		$endpoint = str_replace('https://'.$domain,'', $_GET['endpoint']);
		//echo $endpoint;
		$queueStatus = $api->get_canvas($endpoint,false);
		echo '<p>status: ' . $queueStatus['workflow_state'];
		echo '</p><p>percent completed: ' . $queueStatus['completion'] . '</p>';
		if($queueStatus['completion']*1<100){
			echo '<a href="#" onclick="$(\'#success\').load(\'putgrades-shared.php?endpoint='. $endpoint.'\')">Check again</a>';
		}
		exit();
	}

	
	$endpoint='/api/v1/courses/' . $_SESSION['_basic_lti_context']['custom_canvas_course_id'] .'/assignments/' . $_POST['aid'] . '/submissions/update_grades';
	$args = array();
		foreach($_POST['grades'] as $grade){
			//grade 
			
	//echo "Student,ID,SIS User ID,SIS Login ID,Section,peer" . $projectid . "\r\n";
	//echo ",,,,," . $_GET['maxscore'] . "\r\n";
	
		$args['grade_data'][$grade[0]]['posted_grade']=$grade[1];
		$args['grade_data'][$grade[0]]['text_comment']=$grade[2];
		//$args .= '&grade_data['.$grade[0].'][text_comment]='.urlencode($grade[2]);
		
	
		}
		$result = $api->post_canvas($endpoint,"POST",$args);
		//put_canvas("/api/v1/courses/301991/assignments/4612095/submissions/1273346?submission[posted_grade]=8&comment[text_comment]=a: 5 b: 3 :");
			if($result){
				echo 'Your grades have been queued for posting. If you have a large student roster, it may take a few minutes to complete. You can safely close your browser without interrupting the update.  <a href="#" onclick="$(\'#success\').load(\'putgrades-shared.php?endpoint=' . $result['url'].'\')">Check status</a>';
		
}else{//close if result
  echo mysqli_error($link);
}
	?>