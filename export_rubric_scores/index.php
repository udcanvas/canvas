<?php
/*
This is a one file lti tool. The only dependent files are the shared ones located in /canvas, plus the db access file above the site root.

This file requests tokens on the fly when implemented as a course nav item. The intention is to allow other institutions to adopt it, as well as improve the visibility for our own instructors.

this first part checks to see which version of the app I want, if the token=temp custom variable is set, it means we are going to use instructor-specific temporary tokens.
*/
error_reporting(E_ALL & ~E_NOTICE);
session_start();
if(array_key_exists("custom_token",$_POST)){
	if($_POST['custom_token']=="temp"){
		
		echo $context->info['context_id'];
		session_start();
		$domain = $_REQUEST['custom_domain_url'];
		setcookie('domain',$domain,0,'/');//need to put this somewhere that can be seen from another server
		//setcookie('tokenquery','temptoken');
		$temptoken=true;
		if(!$_SESSION['temptoken'])$_SESSION['temptoken']=true;
		//print_r($_SESSION);
		//exit($_SESSION['token']);
	}

}else if ($_GET['temptoken']){//redirected from get_temp_token
//since this is a temp token, I feel okay about putting it into a url. I tried making it a session token in get_temp_token, but for some reason, it ended up in a different session. I don't understand why
	$_SESSION['temptoken']=$_SESSION['token'] = $_GET['temptoken'];
	//echo "temp token found";
}else if(!$_SESSION['token']){
	print_r($_SESSION);
	exit();
}
//trying to use this same file for all scenarios where a token is required
include "canvas_dance_include_shared.php";
//getting rid of temp tokens. Should never be invoked for a permanent token!
if(array_key_exists("logout",$_GET)){
		if($_SESSION['token'] != $_SESSION['temptoken']){//don't delete!
	
		echo "You seem to be using a reusable token. This can happen when you have an active session in another UD hosted LTI application, such as Peer Evaluation or reThinQ. No tokens have been deleted. Check your user settings if you wish to manually delete unwanted tokens. In most cases, you will not see any unwanted tokens, since the permanent tokens associated with other applications are owned by a UD Canvas administrator.";
	
		
		
	}else{//delete the token
		$response = $api->post_canvas("/login/oauth2/token","DELETE");
		//print_r($response);
		echo '<blockquote>Your Canvas access token has been deleted. Refresh your browser to relaunch this tool.</blockquote>';
	}
	
//either way, end the session	
		session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);


	exit();
}else{//not logout
	
}
//both $context and $api are created in canvas_dance_include_shared file
if(!$context->valid ){
	
	die("<p>This page must be loaded from within Canvas. For help using this tool contact Becky Kinney or her replacement at ATS.</p>");
}

if(array_key_exists("courseid",$_REQUEST)){//form submission from within the tool. this is where the real functionality of this tool begins

function sortByName($a, $b) {
   				 return $b['user']['sortable_name']>$a['user']['sortable_name'];
			}

			

	$submissions = $api->get_canvas('/api/v1/courses/'.$_REQUEST['courseid'].'/assignments/'.$_REQUEST['asstid'].'/submissions?include[]=rubric_assessment&include[]=user');
	
	if($submissions['errors']){
		echo "<pre><blockquote>";
		foreach($submissions['errors'] as $error){
			echo $error['message'] . "\r";
		}
		echo "</blockquote></pre>";
		echo 'There seems to be a problem with this request. Perhaps you are not authorized to view grades for this assignment.';
	}else if($submissions[0]['rubric_assessment']){
		//sort alphabetically by student name
		uasort($submissions,"sortByName");
	
		//set header for csv export
		header("content-disposition:attachment;filename=rubric_scores_" . $_REQUEST['asstid'] . ".csv");
		header("content-type:text/csv");
		$rows = array();
		$firstrow="name,sisid,asstid,attempt,late,submit date,";
		//$api = new CanvasAPI($token,'udel.instructure.com');
	
	

/*$user = $submissions[0]['user'];
foreach ($user as $key => $value){
	echo $key . ':' . $value;
}*/
	$sample = $submissions[0]['rubric_assessment'];
	$n=0;
	//create column headers for rubric values. I don't have a way to get the real names from this endpoint, plus theiy will tend to be long, so I'm just calling them "r#"
	foreach ($sample as $value){
		$firstrow .= 'r'.++$n . ",";
	}
	foreach($submissions as $key => $submission){
		if($submission['user']['sortable_name']=="Student, Test")continue;
		$column = array();
		$column[]='"' . $submission['user']['sortable_name'] . '"';
		$column[]=$submission['user']['login_id'];
		$column[]=$submission['assignment_id'];
		$column[]=$submission['attempt'];
		$column[]=$submission['late'];
		$column[]=$submission['submitted_at'];
		
		$rubric = $submission['rubric_assessment'];
		
		foreach($rubric as $r){
			
			
				$column[]=$r['points'];
			
		}
		
		//echo implode(',',$column) . "\r";
		$rows[]=implode(',',$column);
	}
	function byname($a,$b){
		
		return strcmp($a[0],$b[0]);
	}
	uasort($rows,"byname");
	array_unshift($rows,$firstrow);
	echo implode("\r",$rows);
	exit();
	}else{//no rubric
		echo "<p>This assignment does not appear to be associated with a rubric.</p>";
	}
}else{
	
}
?>
<html>
<head>
<script>
//this functon just pulls the course and asst ids out of the url
function parseurl(url){

	var arr = url.split("/");
	var courseid = arr[4];
	var asstid = arr[6];
	document.getElementById('one').value = courseid;
	document.getElementById('two').value = asstid;
}


</script>
<style>
body{max-width:650px}
blockquote{
	  background-color: cornsilk;
  padding: 20px;
  margin: 5px;
}
</style>
</head>
<body>
<!--conditional html, depending on whether this is the admin or instructor tool--> 
<?php if($context->info['custom_token']):?>
<blockquote>

<?php //echo $_SESSION['token'] ?>
<div id="unload"> <a href="index.php?logout=1" ><button  title="Failure to delete will result in a clutter of unusable tokens in your user account settings." >Delete my token</button></a></div>


<p>It is highly recommended that you delete the access token you have just authorized after completing all desired downloads.</p>
<p>It is possible to exit and then re-enter this tool to obtain assignment urls without issuing a new token, as long as you do not delete your token or close your browser, however, you may find it easier to browse to assignments in a <a href="//<?php echo $domain ?>" target="browse">second browser window or tab</a>.</p>

<?php else : ?>


<p>This app is only available to Canvas admins, and it utilizes an admin authorization token. Output will contain all rubric scores submitted by instructors for the assignment chosen. It is your responsibility to ensure that the instructor to whom you send the output has instructor access to the course. Please do not send data directly to TAs.</p>
<?php endif ?>
</blockquote>
<p>Enter an assignment url and click <strong>extract</strong>. Check course and assignment ids and correct if necessary, and then click <strong>Submit</strong> to download your data. Repeat as necessary. </p>
<!--By now we shuld have a token stored as a session variable, so just post back to index, see line 57-->
<form action="index.php" >
  <p>Assignment URL:
    <input type="text" size="70" id="url" name="parseme" > <input type="button" value="extract" onClick="parseurl(parseme.value)">
    
  </p>
  <p>Course ID:
    <input type="text" name="courseid" id="one" />
  </p>
  <p>Assignment ID:
    <input type="text" name="asstid" id="two" />
  </p>
  <p><input type="submit"></p>
</form>

</body>
</html>