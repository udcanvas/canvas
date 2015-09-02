<?php
//include this instead of blti if you need api access!
session_start();
//echo "test version, where am I";
// if we want to issue a token and not store it - set in the primary page

$token = $_SESSION['token'];
//custom var I added
$domain = $_REQUEST['custom_domain_url'];
//
if(empty($domain))$domain=$_REQUEST['custom_canvas_api_domain'];
//echo 'empty=' . $domain;
if(empty($domain)){
	//echo "from cookie";
	$domain = $_COOKIE['domain'];
}else{//lost the POST vars when we redirected
	setcookie('domain',$domain,0,'/');
}
//echo "cookie=" . $_COOKIE['domain'];
$testing = $domain=="udel.test.instructure.com";
if(empty($_COOKIE['context_id']))setcookie('context_id',$_REQUEST['context_id'],0,'/');
include "/home/bkinney/includes/lti_mysqli.php";
require_once 'ims-blti/blti.php';//this is the beta version
include 'canvasapi.php';//this is the beta version


//'/home/bkinney/includes/get_ud_canvas_endpoint_paginate.php';
//change this to look up in db

//changelog - use same secret for all
$secret =array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");

$context = new BLTI($secret, true, false);//try redirect

	if($context->valid){
			//set some session variables
	//echo "valid";
		
		//die();
	
		$context_id = $context->info['context_id'];
	//echo $context_id;
		//these are used to update db by get_token_domain
				  $isAdmin = $context->isAdministrator();
			  
				setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');
	//changelog - get token from token table
	$shared = array_key_exists('custom_shared',$context->info);
	//this is a Haywood J token. He is an instructor in Becky Test
	//if($asinstructor)$_SESSION['token']='25~18Dk6lWaa44bnPTWe1xbRydhRJ9zKOc3g3mfbqazxPpgw7MOJ4qGCSRGjJSKQewq';
	if($_SESSION['token']){//use whatever I've already got from previous trips
		
		$api = new CanvasAPI($_SESSION['token'],$domain);
		$valid = $api->ready;
		$tokenstatus="found in session" . $api->status . "," . $api->is_valid_token();
	}else if(!$_SESSION['temptoken']){/*new plan, use an admin token unless I've set the shared flag*/
		if($isAdmin  && !$shared){//only an admin can create an admin token, and only if we're not sharing
			if($domain=="udel.instructure.com"){
				setcookie('tokenquery','none',0,'/');
			}else{
				setcookie('tokenquery',"insert into tokens (domain, context, token) values ('" . $domain . "', '" . $domain . "', '%s') on duplicate key update token=values(token)",0,'/');
			}
		}else if($context->isInstructor()){//only instructors can create context tokens, but an admin can be an instructor
		setcookie('tokenquery',"insert into tokens (domain, context, token) values ('" . $domain . "', '" . $context_id . "', '%s') on duplicate key update token=values(token)",0,'/');
		}
	//search for the appropriate token
	if(!$link) include "/home/bkinney/includes/lti_mysqli.php";
		if($shared){
			$query = sprintf("select token from tokens where context='%s' and domain='%s'",$context_id,$domain);
			//echo $query;
			
		}else{
		
			$query = sprintf("select token from tokens where context='%s' and domain='%s'",$domain,$domain);		
		}
		//echo $query;
		$result = mysqli_query($link,$query);//overwrite result
		//now $result could be the return from either query
		if(mysqli_num_rows($result)==1){
				
			
			
			$row = mysqli_fetch_assoc($result);
			//print_r($row);
			$token = $_SESSION['token']= $row['token'];
			if(isset($token)){
				$tokenstatus = "found in db " . $query;
				$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
				
				//print_r($api->courseinfo);
				$valid = $api->ready;
				//if($testing)echo $api->error;
			}else{
				$valid=false;
				echo "no tokens";
				$tokenstatus= "not found in db " . $query;
			}//end if token in session
	
		}else{
			$tokenstatus = "no matching rows "  . mysqli_num_rows($result);
			//echo $tokenstatus;
		}
	}
		
	
	if(!$valid){//no valid token anywhere
	//die($tokenstatus . $api->error .  $domain);
		  //request a new token - will this work in accounts not held in instructure? I'm thinking no
		  $state=rand(100,999);
		  setcookie('state',$state,0,'/');
				header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/canvas/get_token_domain.php&response_type=code&state=" . $state);
	}
	}else{
		echo "invalid context";
		
		//print_r($_REQUEST);
}//end if context

?>