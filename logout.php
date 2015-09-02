<html><head></head><body><blockquote>
<?php
if($_SESSION['token']['temp']){
	include "canvasapi.php";
	$api = new CanvasAPI($_SESSION['token']['temp'],$_COOKIE['domain']);
	$response = $api->post_canvas("/login/oauth2/token","DELETE");
		//print_r($response);
		echo 'Your Canvas access token has been deleted. ';
}
//no, this won't work. Have to get _parent to reload with javascript
echo 'You have logged out. Please refresh your browser no reload';
  session_start();
    session_unset();
    session_destroy();
    session_write_close();
    setcookie(session_name(),'',0,'/');
    session_regenerate_id(true);

?>
</blockquote></body></html>