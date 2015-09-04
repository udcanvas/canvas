<?php
$inhouse=true;
$tokentype="domain";	 
	 $secret = array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);

//-----------all this will move to general include -----------------
session_start();
include "../lti_mysqli.php";//not the right home
require_once('../ims-blti/blti.php');//no token query here anymore - require_once was causing a problem?
$context = new BLTI($secret,true,false);
if($context->valid){
	$context_id = $context->info['context_id'];
	echo "context_id = " .$context_id;
		//$domain =$context->info['custom_canvas_api_domain'];
				  $isAdmin = $context->isAdministrator();
			  
				setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url","https://tron.ats.udel.edu/~bkinney/" .$_SERVER['PHP_SELF'],0,'/');
	
	if(!isset($token)){
		include "../findsessiontoken.php";
	}
	if(isset($token)){
		$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
		$valid = $api->ready;
		if($valid){
			include "common.php";
		}else{//we found a token somewhere, but it is invalid
			
			echo "I have an invalid token. Try <a href='../logout.php'>logging out</a>";
			echo "<p>You seem to have deleted a token. The one we have is invalid. You may re-authorize by completing the form below</p>";
			include "/www/git/lti/dance.php";
			//logout has a link back here, but session gets lost - what to do?
		}
	}else{
		//should have been redirected to dance.php, which initiates a request for a token
		//echo "something's wrong, I can't get a token via findsessiontoken.php";
	}
	//if no token found, I should end up at a token request
	
}else{
	
	echo "invalid context " . $context->message . " <a href='../logout.php'>Log out</a>";
	
}
//-------------------end include code

exit();
?>
