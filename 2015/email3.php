
  <?php 
  session_start();
  
$from = $_SESSION['cas_data']['USER'] . "@udel.edu";
    if($from == $_POST['from']){
		
		if($_POST['to']){
	 
			 $fullname=$_POST['fullname'];
			//ini_set("SMTP","mail.udel.edu");
			//ini_set("sendmail_from",$_POST['from']);
			$headers = "From:" . $fullname . "<" . $_POST['from'] . ">\r\n";
			$headers .= "reply-to:" . $fullname . "<" . $_POST['from'] . ">\r\n";
			$headers .= "cc:" . $fullname . "<" . $_POST['from'] . ">\r\n";
			$success = mail($_POST['to'],$_POST['subject'],$_POST['body'],$headers);
			if($success){
				echo "<p>Your message has been sent.</p>";
				ini_set("sendmail_from","ats-staff@udel.edu");
				//exit();
			}else{
				echo "error";
			}
		}
		

  }else{
	include "/www/git/canvas/2015/timeout.php";
	echo "<p>You may wish to copy the contents of this email onto your system clipboard before refreshing. Otherwise, you will have to re-type your message. </p>";
	die();
  }



 ?>

