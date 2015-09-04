<?php
/**
 * Send a POST requst using cURL
 * @param string $url to request
 * @param array $post values to send
 * @param array $options for cURL
 * @return string
 */
 //this page gets hit by canvas after we hit the /login/oauth2/auth page (dance step1), which should return a code. if it does, we send back a curl to /login/oauth2/token to get the token. see instructions: https://canvas.instructure.com/doc/api/file.oauth.html
 /**
 dedicated post fn. could be using canvasapi, but I wrote this first, and it works, so, if it aint broke...
 
 **/
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
//so, there are steps to the dance. Step 1, we redirect, and Canvas posts back to us this page is the redirect page we sent in step one, initiated in one of the canvas_dance scripts.
	
if($_REQUEST['error']){//something is wrong, write it out
	die($_REQUEST['error']);
}else if(isset($_REQUEST['code'])){//now we exchange the code for a token. See docs
	//as recommended, check the state, but in case I'm linking from somewhere else, putting a dodge in - should remove this at some point. I'm setting the state in canvas_dance pages

if(array_key_exists('state',$_REQUEST) && $_REQUEST['state'] != $_COOKIE['state'])die("invalid state, possible attack" . $_COOKIE['state']. ",". $_REQUEST['state']);

setcookie('state',null,-1,'/');//delete state
$url = "https://" . $_COOKIE['domain'] ."/login/oauth2/token";
$postdata =  array(
		"client_id" => 10000000000369,
		"redirect_uri" => "https://apps.ats.udel.edu/git/canvas/get_temp_token.php",
		"client_secret" => "st1FSMA3hgkysbeE4ajmb4YPmy4nFRvfB9RfWaev7kKCSWswq4DR7sNxQsKto8iN",
		"code" => $_REQUEST['code']
 
	);
	$response = curl_post($url,$postdata);//executing step 2
	$entry = json_decode($response,true);
	//print_r($entry);
	//print_r($_REQUEST);
	//step 3 - if successful, we now have an access token
	if(isset($entry['access_token'])){//got the token, store in db and return to app
	$tokentype=$_COOKIE['tokentype'];
		 $_SESSION['token']['tokentype']=$entry['access_token'];
		// $_SESSION['temptoken']=$entry['access_token'];
		//this is where I would have made a db entry if this were not a temp token
			//die("here I am");
			//setcookie("temptoken",$entry['access_token']);
			 //redirect back to original destination
			 //header("Location: " . $_COOKIE['lti_url']."?temptoken=".$entry['access_token']);
			 header("Location: " . $_COOKIE['lti_url']);//not sure why I was using GET for this
		//	print_r($_SESSION);
		//generate key and secret, and store in db
		
		//echo "Your secret is: " . $entry['secret'] . "<br>Please store it securely.";
	}
	}else{//did not get the token, debug


	foreach($_REQUEST as $key -> $val){
		echo $key . "=" . $val;
	}
}// end if code
?>
<html>
<head/>
<body>get_temp_token. I'm stuck</body></html>
