  <?php
//include this instead of blti if you need api access!
session_start();

$token = $_SESSION['token'];
$domain = $_REQUEST['custom_domain_url'];
if(empty($domain)){
	$domain = $_COOKIE['domain'];
}else{//lost the POST vars when we redirected
	setcookie('domain',$domain,0,'/');
}

require_once '../ims-blti/blti.php';
include "/home/bkinney/includes/lti_mysqli.php";
include '/www/canvas/canvasapi.php';


//'/home/bkinney/includes/get_ud_canvas_endpoint_paginate.php';
//change this to look up in db

$context = new BLTI($secret, true, false);//secret is set in the including page

if($context->valid){
			//set some session variables
	$api = new CanvasAPI($token,$domain,$context->info['custom_canvas_user_id']);
		
		//die();
	
		$context_id = $context->info['context_id'];
		//$domain =$context->info['custom_canvas_api_domain'];
				  $isAdmin = $context->isAdministrator();
			  
				setcookie("context",$context_id,0,'/');
				setcookie("isAdmin",$isAdmin,0,'/');
				setcookie("lti_url","https://apps.ats.udel.edu" .$_SERVER['PHP_SELF'],0,'/');
	
	if(isset($token)){
		
	}else{
			//query db for an existing token. 

  		$query=sprintf("select token from tokens where context='%s' and domain='%s'",$domain,$domain);
			  //echo $query;
		  $result = mysqli_query($link,$query);
		  if(mysqli_num_rows($result)){
			 $row = mysqli_fetch_array($result);
			 
			$token = $_SESSION['token']=$row['token'];
			//$token = $_SESSION['token']= "ejustetesting";
			
			
			
		  }//end token in db

	
	}//end if token in session
	$valid = $api->ready;//isValidToken($domain);
	
	if(!$valid){//no valid token anywhere
		  //set some cookies and then request a new token
			 // die("no token in db");

				header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/canvas/get_token_domain.php&response_type=code");
	}
}//end if context

?>