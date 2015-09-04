<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>email my group</title>

</head>

<body>

<p>
  <?php 
 
session_start();
 $emplid= $_SESSION['cas_data']['EMPLID'];
$from = $_SESSION['cas_data']['USER'] . "@UDel.Edu";

    if( empty($_SESSION['cas_data']['USER'])){
		include "/www/git/canvas/2015/timeout.php";
	die();
  }
  
  if($_POST['to']){
	 		
	 //just getting rid of duplicate email


ini_set("SMTP","mail.udel.edu");
ini_set("sendmail_from",$from);
$fullname=$_SERVER['cas_data']['FIRSTNAME'] . " " . $_SERVER['cas_data']['LASTNAME'];
$headers = "From:" . $fullname . "<" . $_POST['from'] . ">\r\n";
$headers .= "reply-to:" . $fullname . "<" . $_POST['from'] . ">";
$success = mail($_POST['to'],$_POST['subject'],$_POST['body'],$headers);
if($success){
	echo "<p>Your message has been sent.</p>";
	ini_set("sendmail_from","ats-staff@udel.edu");
	echo '<p><a href="/git/canvas/2015/emailgroup.php?'. $_POST['getstr'] . '">Send another</a></p></body></html>';
	exit();
}
  }
  
if($_GET['projectID']){
	  
include "/home/bkinney/includes/db_peer_sqli.php"; 
$query=sprintf("select groupNum from glue where email='%s' and surveyID=%u",
mysqli_real_escape_string($link,$from),
$_GET['projectID']
);

$result =mysqli_query($link,$query);
if($result){
	$row = mysqli_fetch_row($result);

	$groupnum= $row[0];

}else{
	mysqli_close($link);	
	die("invalid project id for this user");
}
mysqli_free_result($result);
$query = sprintf('select email from glue where surveyID=%u and groupNum=%u and email<>"presentation"',$_GET['projectID'],$groupnum);

$result = mysqli_query($link,$query);
	if($result){
	

		$mygroup ="";
		while ($row= mysqli_fetch_assoc($result)){
			
			$mygroup .= $row['email'] . ", ";
			
		}
	//echo $mygroup;
	}else{
		echo "invalid group";
	}
mysqli_free_result($result);
mysqli_close($link);	  
  }
 ?>
 
</p><form name="notifygroup" method="post" action="emailgroup.php">
   
  <p>
    <input type="hidden" value=<?php echo $_SERVER['QUERY_STRING'] ?> name="getstr"/>
  </p>

  <p>To:
   <input type="text" name="to" id="to" size="50" value="<?php echo $mygroup ?>"/>
   
 
  </p>

    <p>
      <input name="from" type="hidden"   value="<?php echo $from ?>">
    From: <?php echo $from ?>

    </p>
    <p>Subject:
      <input type="text" name="subject" id="subject"/>
  </p> 
<p>  <textarea name="body" id="emailbody" cols="70" rows="5"></textarea>
  </p>

  <p>
    <input type="submit" name="submit" id="submit" value="Send">
  </p>
 
</form>


</body>
</html>