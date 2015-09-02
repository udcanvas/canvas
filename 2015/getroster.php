<?php

$url="https://udel.instructure.com/api/v1/courses/" . $_GET['courseid'] . "/users?enrollment_type=student&include[]=email";
$token="&access_token=25~QBDKGeH5kk5Y33jDPN6fD1uIBGsSvUcDQMxzULvYTG8TXMxqjzWqGs8yyvSsgzq5";
$json = file_get_contents($url . $token . "&per_page=50");
$page=1;
$max=50;

$roster = $proster = json_decode($json,true);
while(count($proster)== $max && isset($json)){//get the next page
	$page++;
	$urlp = $url . "&page=" . $page . "&per_page=" . $max ; 
	//echo $urlp . "<br>";
	$json = file_get_contents($urlp . $token);
	$proster = json_decode($json,true);
	foreach($proster as $val){
		$roster[] = $val;
	}
	//$roster += $proster; + $roster;
}

$d = " | ";
foreach($roster as $member){
	if(empty($member['sis_user_id']))continue;
	echo "<li>" . $member['sortable_name'] . $d
	. $member['sis_user_id'] . $d
	. strtolower($member['email']) . "</li>";
}

?>