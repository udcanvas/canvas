<?php
include '/home/bkinney/includes/lti_mysqli.php';
include "../ims-blti/blti.php";
$secret = array("table"=>"tokens","key_column"=>"consumer_key","secret_column"=>"secret","context_column"=>"context");
$context = new BLTI($secret, true, false);//do this elsewhere
//print_r($context->info);
$instructor=false;
session_start();
if($context->valid){
	$instructor = $_SESSION['isInstructor'] = $context->isInstructor();
	$contextid = $_SESSION['contextid'] = $context->info['context_id'];
	$uid = $_SERVER['uid']=$context->info['lis_person_sourcedid'];
}else{
	$instructor = $_SESSION['isInstructor'];
	$uid = $_SESSION['uid'];
	$contextid = $_SESSION['contextid'];
}
if(!empty($_FILES["postemfile"])){
	$instructor=true;
	$data = '<table class="stickyHeader" ><thead>';
	$handle=fopen($_FILES["postemfile"]["tmp_name"],"r");
	
	$cols = fgetcsv($handle, 10000, ",");//just the first row
	$data .= "<tr><th>" . implode("</th><th>",$cols) . "</th></tr>";
	$data .= "</thead><tbody>";
	
	while (($cols = fgetcsv($handle, 10000, ",")) !== FALSE) {
		$clean = array_map(htmlspecialchars,$cols);
		$data .= "<tr><td>" . implode("</td><td>",$clean) . "</td></tr>";
	}
	$data .= "</tbody></table>";
	$query = sprintf("insert into files (context_id,postem) values('%s','%s') on duplicate key update postem=values(postem)",$_POST['context_id'],mysqli_real_escape_string($link,$data));
	//echo $query;
$response = mysqli_query($link,$query);
}
?>


<!DOCTYPE html >
<html >
<head>
  

  <meta name="viewport" content="width=device-width" />

<link rel="stylesheet" href="stickyheader.css">
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript" src="stickyheader.jquery.js"></script>

<style>
body,td,th {
	font-family: Verdana, Geneva, sans-serif;
}
h1,h2,h3,h4 {
	
	color:#2e6e9e;
}
h2 {
	font-size: 120%;
	
}
h3 {
	font-size: 110%;
	
}
h4{
	color:#e17009;
	font-size:100%
}
table{
	
	margin-bottom:20px;
	
}
blockquote{
	border: double;
	padding: 10px;
	width: 60%;
	margin: 0 0 20px 15px;
}


tbody tr td{
	background-color:rgb(215, 230, 243);
}
tbody td:nth-of-type(even){
	 background-color: rgb(242, 247, 252);
	 
}
th{
	background-color:rgb(143, 183, 213);
}

th,td{
	text-align:center;
	min-width:30px;
	max-width:300px;
}

table{
	
	border:solid rgb(143,183,213) 3px;
}

thead, tbody{
	border-collapse: separate;
	border-spacing: 2px;
	border-color: gray;
}

</style>
<!--<![endif]--> 
<script>
function toggleSubmit(){
	document.getElementById("submit").disabled=false;
}
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Post 'Em</title>
</head>

<body>
	
			
<h2><?php 
 
if($context->isInstructor()){
	echo "Instructor View";
	}else{
echo $context->info['lis_person_name_given'] . " " . $context->info['lis_person_name_family'];	
}
?></h2>
<div id="report">
<?php 
if(empty($data)){//no upload, look in db

$query = sprintf("select postem from files where context_id='%s'",$context->info['context_id']);
//echo $query;
$result = mysqli_query($link,$query);
$data = mysqli_fetch_field($result);
//echo $data;
}
function cleanCell($cell){
	
	$arr = array("<tbody>","<tr>","<td>");
	$rep = array("","","");
	$cleaned = str_replace($arr,"",$cell);
	//if($cleaned=="")$cleaned="&nbsp;";
	return strtolower($cleaned);
	
}

if(!empty($data)){
		$table = str_replace("<td></td>","<td>&nbsp;</td>",$data);
		$table = str_replace("<tr><td>","<tr><th>",$table);
	
	if($instructor){
		
		echo $table;
	}else{//filter student view
		//echo '<table cellpadding="3">';
		//$f = explode("</tr>",$data);//str_getcsv($data,"\r\n"); ;
		
		//print_r($f);
		preg_match('/<table.+<\/thead>/',$data,$header);
		
		preg_match('/<tbody.+<\/tbody>/',$data,$body);
		$f = split("</tr><tr>",$body[0]);
		
		//array_shift($f);//get rid of the first row
		//preg_match('/(<tr>(.+)<\/tr>){1}/',$body[0],$f);
		//echo count($f);
		$table = $header[0];
	/*	$hl = str_getcsv($header);//explode(",",$header);
		echo $body[0];
		echo "<thead><tr>";
		foreach($hl as $cell){
			echo "<th>" . htmlspecialchars($cell) . "</th>";
		}*/
		
		//$instructor=false;
		foreach($f as $row) {
			$needle = strtolower($context->info['lis_person_contact_email_primary']);
			//$row = str_replace("<tr><tbody>","<tbody>",$row);
			//$row = str_replace("</tbody></tr><tr>","</tbody>",$row);
			//preg_match('/<td>.+<\/td>/',$row,$line);
			$line = explode("</td><td>",$row);//preg_split('<(/?)td>',$row);//str_getcsv($row);;
			$haystack = array_map('cleanCell',$line);
			$n = $uid*1;
			if(count($line)>1 && ($instructor || in_array($uid,$haystack) || in_array($needle,$haystack))){
				$table .=  $row . "</tr><tr>";
			}else{
				//echo gettype($uid) . "," . gettype($line[0]) . "<br>";
			//	echo $haystack[0] . ",";
				//echo gettype(cleanCell);
			}
		}
		
		//$table = str_replace("<tr><tbody>","<tbody>",$table);
		$table = str_replace("</tbody></tr><tr>","</tbody>",$table);
		$table = str_replace("<td></td>","<td>&nbsp;</td>",$table);
		echo $table;
	}
}else{
	echo "<blockquote>No data available.";
	if($context->isInstructor()){
		echo " Upload a csv file containing at least one identifying column. Valid id columns are UD email addresses or UDID.";
	}
	echo "</blockquote>";
	
}

echo '</div>';

if($instructor){

echo '<hr />


<h4>Upload CSV</h4>

<form action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data">
  <p>
    <input type="hidden" size="41" name="context_id" value="'. $contextid . '" />
   
    <input name="postemfile" type="file" id="postemfile" size="50" onchange="toggleSubmit()"  />
 
    <input id="submit" type="submit" name="submit" value="Submit" disabled>
  </p>
</form>';
}
 ?>
 </div>


</body>
</html>