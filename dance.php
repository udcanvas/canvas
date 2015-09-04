<?php

$domain=$_COOKIE['domain'];
$context = $_COOKIE['context'];
if($_REQUEST['dance']=='temp'){//form in this page has been submitted
	
	
	$state=rand(100,999);//security stuff
		  setcookie('state',$state,0,'/');
				header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/git/canvas/get_temp_token.php&response_type=code&purpose=UD Single_Use_Token&state=" . $state);
}else if(array_key_exists('dance',$_REQUEST)){
	if($_REQUEST['dance']=='domain'){
		if($domain == 'udel.instructure.com')die("this interface can not be used to overwrite the UD admin token. you must enter a new udcanvas owned token to the db manually. Contact Mu or Becky for assistance.");
		setcookie('tokenquery',"insert into tokens (domain, context, token) values ('" . $domain . "', '" . $domain . "', '%s') on duplicate key update token=values(token)",0,'/');
	}else if($_REQUEST['dance']=='context'){
		setcookie('tokenquery',"insert into tokens (domain, context, token) values ('" . $domain . "', '" . $context . "', '%s') on duplicate key update token=values(token)",0,'/');
	}
	 $state=rand(100,999);
		  setcookie('state',$state,0,'/');
				header("Location: https://" .$domain."/login/oauth2/auth?client_id=10000000000369&redirect_uri=https://apps.ats.udel.edu/git/canvas/get_token_domain.php&response_type=code&purpose=UD ".$_REQUEST['dance']." token&state=" . $state);
}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<p>This app needs access to your Canvas data. Please select the type of access you wish to authorize. Only Canvas admins can authorize a domain level token.</p>
<form id="dance" action="/git/canvas/dance.php" method="get">
<?php if($_COOKIE['isAdmin']) echo '<input type="text" value="'.$domain.'">'; ?>
<input type="text" value="<?php echo $context ?>">

<select name="dance">

<option value="temp">single use</option>
<option value="context">context</option>
<option value="domain">domain</option>
</select>
<input type="submit">
</form>
</body>
</html>