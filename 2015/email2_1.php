<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Send mail</title>
<style>
p.instructions {
background-color: #dfeffc;
padding-left: 8px;
margin: 0 0 5px 0;
text-align: center;
border-top: #2e6e9e solid thin;
border-bottom: #2e6e9e solid thin;
}

</style>
</head>

<body>

<p>
  <?php 
if(session_id() == '') {
    session_start();
};
$emplid = $_REQUEST['emplid'];


$from = $_SESSION['cas_data']['USER'] . "@udel.edu";
    if( $from == $_REQUEST['from'] || $from == $_POST['from']){

  }else{
	include "/git/canvas/2015/timeout.php";
	die();
  }
  
  if(isset($_POST['to'])){
	 		
	 //just getting rid of duplicate email
	 $all = str_replace($_POST['from'],'',$_POST['to']);
	
	 		
	  $fullname=$_POST['fullname'];
//ini_set("SMTP","mail.udel.edu");
//ini_set("sendmail_from",$_POST['from']);
//$headers = "From:" . $fullname . "<" . $_POST['from'] . ">\r\n";
$headers = "From:" . $fullname . "via: apps.ats.udel.edu <ats-staff@udel.edu>\r\n";
$headers .= "reply-to:" . $fullname . "<" . $_POST['from'] . ">\r\n";
$headers .= "bcc:" . $_POST['to'];
$success = mail($_POST['to'],$_POST['subject'],$_POST['body'],$headers);
if($success){
	echo '<p align="center" class="instructions">Your message has been sent.</p>';
	ini_set("sendmail_from","ats-staff@udel.edu");
	
	exit();
}
  }
  
if($_REQUEST['projectID']){
	  
include "/home/bkinney/includes/db_peer_sqli.php"; 
$query = sprintf('select email, groupNum from glue where surveyID=%u and email<>"presentation" order by groupNum asc',$_REQUEST['projectID']);

$result = mysqli_query($link,$query);
if($result){
	$g=0;
	
	$all ="";
	$options ="";
	$nextgroup = "";
	while ($row = mysqli_fetch_assoc($result)) {
		if($row['groupNum']!=$g){
			//echo $nextgroup . "<br>";
			if($nextgroup!=""){
				 $options .= "<option value='";
				$options .= $nextgroup;
				$options .= "'>group " . $g;
				$options .= "</option>";
				$nextgroup = "";
			}
			$g = $row['groupNum'];
			
		}
		$nextgroup .= $row['email'] . ", ";
		$all .= $row['email'] . ", ";
	}
	//get last group
	 $options .= "<option value='";
				$options .= $nextgroup;
				$options .= "'>group " . $g;
				$options .= "</option>";
				

}else{
	echo "no result";
}
mysqli_free_result($result);
//$query = sprintf("select email from non_responders where surveyid='%u'",$_REQUEST['projectID']);
$query = sprintf("select distinct g.email AS email,g.groupNum,g.surveyID from (glue g join student_evals_all e) where e.evaluatee>2000 and g.groupnum>1 and ((g.surveyID = '%s' and e.surveyID=%s) and (g.udid = e.evaluator) and isnull(e.grade));",$_REQUEST['projectID'],$_REQUEST['projectID']);
$result=mysqli_query($link,$query);
if($result){
	$nextgroup ="";
	while ($row= mysqli_fetch_assoc($result)){
		
		$nextgroup .= $row['email'] . ", ";
	}
		 $options .= "<option value='";
				$options .= $nextgroup;
				$options .= "'>non-responders</option>";
}else{
	echo "no missing evaluations for this project";
}
mysqli_free_result($result);
mysqli_close($link);	  
  }
 ?>

</p><form name="notifygroup" method="post" action="/git/canvas/2015/email2_1.php" target="frame">
   
  <p>
    <input type="hidden" value=<?php echo $_SERVER['QUERY_STRING'] ?> name="getstr"/>
  </p>
  <p>To: <?php echo $_REQUEST['from'] ?> </p>
  <p>Bcc:
    <select onChange="populateTo()" name="tolist" id="tolist" >
 
      <option value="<?php echo $all ?>" >all</option>
      <?php echo $options ?>
    </select>
    <input name="to" type="text" id="to" size="90">
   
 
  </p>
<p> From: ats-staff@udel.edu</p>
    <p>
      Reply To: <input name="from" type="hidden"   value="<?php echo $_REQUEST['from'] ?>">
    <?php echo $_REQUEST['from'] ?>

    </p>
    <p>Subject:
      <select onChange="setBody(this.value)" name="subject">
    <option value="Peer Evaluations Due">Peer Evaluations Due </option>
    <option value="Peer Feedback Available for View">Peer Feedback Available for View</option>
    </select>
  </p> 
<p>  <textarea name="body" id="emailbody" cols="70" rows="5">Please log in to http://apps.ats.udel.edu/peer/student.php and complete peer evaluations for all members of your <?php echo $_REQUEST['projName'] ?> group.</textarea>
  </p>

  <p>
    <input type="submit" name="submit" id="submit" value="Send">
  </p>
  <input type="hidden" name="fullname" id="fullname" value="<?php echo $_REQUEST['fullname'] ?>">
</form>

<script>
function populateTo(){
	document.getElementById("to").value =document.getElementById("tolist").value;
}
function setBody(v){
	var href = "<?php echo $_REQUEST['redirect'] ?>";
	
	
	switch(v){
		case "Peer Evaluations Due":
		
		
		if(href.indexOf("peer")==1){
		document.getElementById("emailbody").value="Please log in to http://apps.ats.udel.edu<?php echo $_REQUEST['redirect'] ?> and complete peer evaluations for <?php echo $_REQUEST['projName'] ?> group.";	
		}else{
		document.getElementById("emailbody").value="Please log in to https://apps.ats.udel.edu/peer/student.php and complete peer evaluations for all members of your <?php echo $_REQUEST['projName'] ?> group.";
		}
		break;
		case "Peer Feedback Available for View":
		if(href.indexOf("peer")==1){
		document.getElementById("emailbody").value="<?php echo $_REQUEST['projName'] ?> peer feedback is ready for your review at  https://apps.ats.udel.edu<?php echo $_REQUEST['redirect'] ?>.";	
		}else{
		document.getElementById("emailbody").value="<?php echo $_REQUEST['projName'] ?> peer evaluation feedback is ready for your review at https://apps.ats.udel.edu/peer/student.php.";
		break;
		}
		
	}
}
setBody("Peer Evaluations Due");
populateTo();
</script> <iframe name="frame" id="frame" width="96%" height="40" scrolling="no" frameborder="0"></iframe>
</body>
</html>