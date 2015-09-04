<?PHP

 if (isset($_SESSION['CAS_logoutdest'])){
   $goto_url = $_SESSION['CAS_logoutdest'];
 }
 $_SESSION = array();
 if (isset($_COOKIE[session_name()])) {
   setcookie(session_name(), '', time()-42000, '/');
   setcookie(session_name(), '', time()-42000, '/peer');
   setcookie(session_name(), '', time()-42000, '/peer/2015');
   setcookie(session_name(), '', time()-42000, '/peer/beta');
 }
 
 //setcookie('emplid', null,time()-42000,"/test/");
 setcookie('emplid', null,time()-42000,"/git/canvas");
 setcookie('usertype', null,time()-42000,"/git/canvas");
 setcookie('redirect', null,time()-42000,"/git/canvas");
  setcookie('emplid', null,time()-42000,"/");
 setcookie('usertype', null,time()-42000,"/");
 setcookie('redirect', null,time()-42000,"/");
  setcookie('emplid', null,time()-42000,"/git/canvas/2015");
 setcookie('usertype', null,time()-42000,"/git/canvas/2015");
 setcookie('redirect', null,time()-42000,"/git/canvas/2015");
   setcookie('emplid', null,time()-42000,"/peer/beta");
 setcookie('usertype', null,time()-42000,"/peer/beta");
 setcookie('redirect', null,time()-42000,"/peer/beta");
 
 
 
 session_destroy();
 if (isset($goto_url)){
   //header("Location: $goto_url");
 }
 $usertype="loggedout";

?>