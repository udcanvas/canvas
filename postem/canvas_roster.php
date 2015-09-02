<?php

/*$url="https://udel.instructure.com/api/v1/courses/" . $_GET['courseid'] . "/users?enrollment_type=student&include[]=email";
$token="&access_token=" . $_SESSION['token'];
$json = file_get_contents($url . $token . "&per_page=50");
$page=1;
$max=50;$roster = $proster = json_decode($json,true);*/

session_start();

$info = $_SESSION['_basic_lti_context'];
$courseid= $info['custom_canvas_course_id'];

header("content-disposition:attachment;filename=roster_" . $courseid . ".csv");
header("content-type:text/csv");

//include '/www/canvas/get_canvas.php';
include "../canvasapi.php";
$api = new CanvasAPI($_SESSION['token'],$info['custom_domain_url']);
$roster = $api->get_canvas("/api/v1/courses/" . $courseid . "/users?enrollment_type=student",true);
//echo "<pre>" . print_r($roster). "</pre>";
echo '"Name","SISID';
$d = '","';
foreach($roster as $member){
	echo '"
"' . $member['sortable_name'] . $d
	. $member['sis_user_id'];
}
echo '"';
?>