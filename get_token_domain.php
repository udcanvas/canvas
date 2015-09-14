<?php
/** 
 * Send a POST requst using cURL 
 * @param string $url to request 
 * @param array $post values to send 
 * @param array $options for cURL 
 * @return string 
 */ 
 //this page gets hit by canvas after we hit the /login/oauth2/auth page, which should return a code. if it does, we send back a curl to /login/oauth2/token to get the token. see instructions: https://canvas.instructure.com/doc/api/file.oauth.html
function curl_post($url, array $post = NULL, array $options = array()) 
{ 
    $defaults = array( 
        CURLOPT_POST => 1, 
        CURLOPT_HEADER => 0, 
        CURLOPT_URL => $url, 
        CURLOPT_FRESH_CONNECT => 1, 
        CURLOPT_RETURNTRANSFER => 1, 
        CURLOPT_FORBID_REUSE => 1, 
        CURLOPT_TIMEOUT => 4, 
        CURLOPT_POSTFIELDS => http_build_query($post) 
    ); 

    $ch = curl_init(); 
    curl_setopt_array($ch, ($options + $defaults)); 
    if( ! $result = curl_exec($ch)) 
    { 
      echo "curl_exec failed " . $url;  
      trigger_error(curl_error($ch)); 
    } 
    curl_close($ch); 
    return $result;
} 

require_once("/home/bkinney/includes/dkey.php");//this sits above the html root and provides my developer id and secret
if($_REQUEST['error']){
	die($_REQUEST['error']);
}else if(isset($_REQUEST['code'])){
	//as recommended, check the state, but in case I'm linking from somewhere else, putting a dodge in - should remove this at some point. I'm setting the state in canvas_dance_include_shared
if(array_key_exists('state',$_REQUEST) && $_REQUEST['state'] != $_COOKIE['state'])die("invalid state, possible attack" . $_COOKIE['state']. ",". $_REQUEST['state']);

setcookie('state',null,-1,'/');
$url = "https://" . $_COOKIE['domain'] ."/login/oauth2/token";
$postdata =  array(
		"client_id" => $developerID,
		"redirect_uri" => "https://apps.ats.udel.edu/canvas/get_token_domain.php",
		"client_secret" => $developerSecret,
		"code" => $_REQUEST['code']
 
	);
$response = curl_post($url,$postdata);
$entry = json_decode($response,true);
//print_r($entry);
//print_r($_REQUEST);
	if(isset($entry['access_token'])){//got the token, store in db and return to app
		
		//return;
		$tokentype=$_COOKIE['tokentype'];
		$context = $_COOKIE['isAdmin']==1 ? $_COOKIE['domain'] : $_COOKIE['context'];
		if(empty($context))die($_COOKIE['isAdmin']);
		if(gettype($link)!="resource") include "/home/bkinney/includes/lti_mysqli.php";
		//$entry['secret'] = rand(1000000,9999999);
		//$query=sprintf("insert into tokens (context, token) values('%s', '%s') on duplicate key update token=values(token)",$context,$entry['access_token']);
		if(isset($_COOKIE['tokenquery'])){
			if($_COOKIE['tokenquery']=='none'){
				//don't touch the db - we are going to update our personal tokens manually
				 session_start();
				 $_SESSION['token'][$tokentype] = $entry['access_token'];
			// setcookie("token",$entry['access_token']);
			 //redirect back to original destination
			 header("Location: " . $_COOKIE['lti_url']);
			}
			$query = sprintf($_COOKIE['tokenquery'],$entry['access_token']);
			//this should really always be here, since I've coded it into canvas_dance_include_shared
			$response = mysqli_query($link,$query);
			if($response){//this is always happening, even when db update fails
				 
			 
			 session_start();
			 $_SESSION['token'][$tokentype]=$entry['access_token'];
			// setcookie("token",$entry['access_token']);
			 //redirect back to original destination
			 header("Location: " . $_COOKIE['lti_url']);
			}else{
				echo mysql_error();
				echo "<br>" . $query;
			}
		}
		mysql_close($link);
		//generate key and secret, and store in db
		
		//echo "Your secret is: " . $entry['secret'] . "<br>Please store it securely.";
	}
}else{


	foreach($_REQUEST as $key -> $val){
		echo $key . "=" . $val;
	}
}
?>
<html>
<head/>
<body>get_token_domain. I'm stuck</body></html>
