<?php
//not using session utils here, because other scripts might call this
if (!isset($_SESSION)) session_start(); 

$lti = isset($_POST['lis_person_sourcedid']) || isset($_SESSION['_basic_lti_context']);
                if ($lti ){//bypass cas
					if(!isset($secret)) $secret = array("table"=>"key_token_view","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");
					include "/www/git/lti/canvas_include.php";
					$blti=$context->valid;
				}else{
					$blti=false;
					include "/www/auth-cas-2.7.2.php";
				}
				
?>