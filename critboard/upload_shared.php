<?php
if(empty($_POST['folder'])){
header('Content-Disposition: attachment; filename="Critboard:' . date("j-m-y")  . '.html"');
}else{
	
	include "/www/git/lti/canvasapi.php";
	function canvas_upload_critboard($data,$api){
		$folderpath = "/critboard/".$_POST['folder'];
		$handle2 = fopen("/home/bkinney/writable/temp.html","w");
		if(!$handle2)echo "failed to open";
		$file = array();
		$file['size']= fwrite($handle2,$data);
		
		$file['tmp_name']="/home/bkinney/writable/temp.html"; 
		$file['name']='temp.html';
		$file['type']="text/html";
		$courseid = $_REQUEST['courseid'];
		//print_r($file);
		$uri='/api/v1/courses/'.$courseid .'/files?name='.date("j-m-y-g:i") . '.html&parent_folder_path='.$folderpath;
		
		fclose($handle2);
		
		return $api->upload($uri,$file);
		
	}
//ob_start();//start buffering content
}
$html='
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Saved Critboard ' . date("j-m-y") . '</title>


<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js"></script>
<link href="//apps.ats.udel.edu/canvas/critboard/critboard.css" rel="stylesheet" type="text/css">
  <script>
  $(function() {
    
  
 
	
	$("#dialog").dialog({
		autoOpen:false,
		width:800,
		height:800
	});
	$("#critboard").tabs({
  show: function( event, ui ) {
	  //alert("open");
	  fixHeight("#tab" +ui.index);
	  }
	  });
	
	
});
function fixHeight(str){
	var div = $(str);
	//div.addClass("tab");
	div.find("li[data-score=1]").appendTo("div.ul");
	var newh = div.find("li:last").position().top - div.position().top;

	div.css("height",newh+"px").css("background-color","gray").css("padding","1em 0 0 0");
}
function openFull(img){
	$("#dialog").html("<img src=\'"+img + "\'>").dialog("open");
}
  </script>
</head>

<body><div id="dialog"></div>'

. $_POST['critlist'] .
'</body>
</html>';

session_start();

	$token = $_SESSION['token'];
	$domain = $_COOKIE['domain'];
$api = $api = new CanvasAPI($token,$domain,$_SESSION['_);
echo  canvas_upload_critboard($html,$api);

?>