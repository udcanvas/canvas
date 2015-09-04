<?php
session_start();
include "/home/bkinney/public_html/canvas/canvasapi.php";
//$_SESSION['token']['temp']=12335;
//$tokentype = "context";
$domain = $_REQUEST['custom_domain_url'];
//custom var canvas adds for public apps
if(empty($domain))$domain=$_REQUEST['custom_canvas_api_domain'];
if(empty($domain)){
	$domain="udel.test.instructure.com";
	//$domain = $_COOKIE['domain'];
}else{//lost the POST vars when we redirected
	setcookie('domain',$domain,0,'/');
}

//check for any stored tokens from previous trips
function findSessionToken($type_arr,&$tokentype){
	if(array_key_exists('temptoken',$_GET)){
		$tokentype="temp";
	$_SESSION['token'][$tokentype] = $_GET['token'];
		return $_GET['temptoken'];
	}
	foreach($type_arr as $t){
	
		if(isset($_SESSION['token'][$t])){
			$tokentype=$t;
			 return $_SESSION['token'][$t];
		}
	}
	return false; //if no tokens are found
}
//find an appropriate token in the database
function getToken($link,$domain,$context,&$tokentype){
	// this function always prefers domain
	
	if($tokentype=="context"){
		$query = sprintf("select token from tokens where context='%s' and domain='%s'",$context,$domain);
		//echo $query;
		$tokentype="context";
	}else{//look for an admin token
		$tokentype="domain";
		$query = sprintf("select token from tokens where context='%s' and domain='%s'",$domain,$domain);		
	}
	//echo $query;
	$result = mysqli_query($link,$query);//overwrite result
	//now $result could be the return from either query
	if(mysqli_num_rows($result)==1){
			
		
		
		$row = mysqli_fetch_assoc($result);
		//print_r($row);
		$token = $_SESSION['token'][$tokentype]= $row['token'];
		
		if(isset($token)){
			
			$tokenstatus = "found in db " . $query;
			$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
			
			//print_r($api->courseinfo);
			$valid = $api->ready;
			//echo $api->error;
		}else{
			$valid=false;
			echo "no tokens";
			$tokenstatus= "not found in db " . $query;
		}//end if token in session

	}else{
		$tokenstatus = "no matching rows "  . mysqli_num_rows($result);
		//echo $tokenstatus;
	}
	echo $tokenstatus;
	return $token;
}


$token = findSessionToken(array('context','domain','temp'),$tokentype);
if($token){
	echo "<br>Found a ".$tokentype ." token = " . $token;
	//print_r($_SESSION['token']);
}else{
	echo "<br>no session token, checking db";
	if(!$link) include "/home/bkinney/public_html/canvas/lti_mysqli.php";//re-establish link to db
	$token = getToken($link,$domain,$context_id,$tokentype);
	if(!$token){//here is where we will trigger the dance
	setcookie("domain",$domain,0,'/');
	setcookie("context",$context_id,0,'/');
	setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');//once token is acquired, get_temp_token.php will redirect
	//header("Location: dance.php");
	}else{
		//echo "<br>" .  $tokentype . " token found and validated";
	}
}

?>
<html><head></head><body>
<?php 
if(!$token)include "dance.php";

?>
</body></html>