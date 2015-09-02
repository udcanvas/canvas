<?php
header('Content-Disposition: attachment; filename="Critboard:' . date("j-m-y")  . '.html"');
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Saved Critboard <?php echo date('j-m-y') ?></title>

 
<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css">
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js"></script>
<link href="http://apps.ats.udel.edu/canvas/critboard/critboard.css" rel="stylesheet" type="text/css">
  <script>
  $(function() {
    
  
 
	
	$("#dialog").dialog({
		autoOpen:false,
		width:'auto',
		height:'auto'
	});
	$("#critboard").tabs();
	
});
function openFull(img){
	$("#dialog").html('<img src="'+img + '">').dialog("open");
}
  </script>
</head>

<body>
<div id="dialog">
</div>
<?php echo $_POST['critlist'] ?>
</body>
</html>
