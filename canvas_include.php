<?php
//include this instead of blti if you need api access!
session_start();


$token = $_SESSION['token'];
//custom var I added
$domain = $_REQUEST['custom_domain_url'];
//custom var canvas adds for public apps
if(empty($domain))$domain=$_REQUEST['custom_canvas_api_domain'];
if(empty($domain)){
	$domain = $_COOKIE['domain'];
}else{//lost the POST vars when we redirected
	setcookie('domain',$domain,0,'/');
}
include "/home/bkinney/includes/lti_mysqli.php";
include '../ims-blti/blti.php';//no token query here anymore - require_once was causing a problem?
include '../canvasapi.php';


//'/home/bkinney/includes/get_ud_canvas_endpoint_paginate.php';
//change this to look up in db

//try this with the redirect. Does that help with masquerading??/
$context = new BLTI($secret, true, false);//secret is set in the including page

if($context->valid){
			//set some session variables
	
		
		//die();
	
		$context_id = $context->info['context_id'];
		//$domain =$context->info['custom_canvas_api_domain'];
				  $isAdmin = $context->isAdministrator();
			  
				setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');
	
	if(isset($token)){//this should alway be false
		$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
		$valid = $api->ready;
	}else{
			//query db for an all purpose token. 
//I'm trusting the domain because I'm in a validated context
  		$query=sprintf("select token from tokens where domain='%s' and context='%s'",$domain,$domain);
			 
		  $result = mysqli_query($link,$query);
		  if(mysqli_num_rows($result)){
			 $row = mysqli_fetch_array($result);
			 
			$token = $_SESSION['token']['domain']=$row['token'];
			//$token = $_SESSION['token']= "ejustetesting";
			
			
			
		  }//end token in db
		$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
		$valid = $api->ready;
	
	}//end if token in session

	
	if(!$valid){//no valid token anywhere
		  //set some cookies and then request a new token
		  //I really don't want users to generate new tokens from this page. just complicates my life
		  
		  echo "<p>Your access token is not valid. Please contact a Canvas administrator.</p>";
		  include "/www/peer/2015/logout.php";
			  die();
//die($_COOKIE['tokenquery']);
//$domain = $context->info['custom_canvas_api_domain'];//overwrite insecure cookie! 
//reset cookie, no time for hackers to change this?
				//header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/canvas/get_token_domain.php&response_type=code");
	}
}//end if context

?>