<?php
include '/home/bkinney/includes/lti_mysqli.php';
include "../ims-blti/blti.php";
$secret = array("table"=>"tokens","key_column"=>"consumer_key","secret_column"=>"secret","context_column"=>"context");
$context = new BLTI($secret, true, false);//do this elsewhere
//print_r($context);
$instructor=false;
if($context->valid){
	$instructor = $context->isInstructor();
}
if(!empty($_FILES["postemfile"])){
	$instructor=true;
	$data=file_get_contents($_FILES["postemfile"]["tmp_name"]);
	$query = sprintf("insert into files (context_id,postem) values('%s','%s') on duplicate key update postem=values(postem)",$_POST['context_id'],mysqli_real_escape_string($link,$data));
	//echo $query;
$response = mysqli_query($link,$query);
}
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
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
	padding:3px;
	margin-bottom:20px;
	
}
blockquote{
	border: double;
	padding: 10px;
	width: 60%;
	margin: 0 0 20px 15px;
}

#report{
overflow:auto;
max-height:400px;
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
td:nth-child(1){
	background-color:rgb(206, 218, 235);
}
th,td{
	text-align:center;
	min-width:30px;
	max-width:300px;
}

table{
	min-width:60%;
	border:solid rgb(143,183,213) 3px;
}
thead, tbody{
	border-collapse: separate;
	border-spacing: 2px;
	border-color: gray;
}

</style>
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

$result = mysqli_query($link,$query);
//changelog there is no mysqli_result
//$data = mysql_result($result,0);
$data = mysqli_fetch_field($result);//should return the first and only result
//echo $data;
}
if(!empty($data)){
	echo '<table cellpadding="3">';
	$f = str_getcsv($data,"\r\n");// explode("\n",$data);
	//echo count($f);
	$header = array_shift($f);
	//echo count($f);
	$hl = str_getcsv($header);//explode(",",$header);
	echo "<thead><tr>";
	foreach($hl as $cell){
		echo "<th>" . htmlspecialchars($cell) . "</th>";
	}
	echo "</tr></thead><tbody>";
	foreach($f as $row) {
		$needle = strtolower($context->info['lis_person_contact_email_primary']);
		//$row='"' . $row;
		$line = str_getcsv($row);//explode(",",$row);
		$haystack = array_map('strtolower',$line);
		
		if(count($line)>1 && ($instructor || in_array($context->info['lis_person_sourcedid'],$line) || in_array($needle,$haystack))){
			echo "<tr>";
			foreach ($line as $cell) {
					echo "<td>" . htmlspecialchars($cell) . "</td>";
			}
			echo "<tr>\n";
		}
	}
	
	echo "\n</tbody></table>";
}else{
	echo "<blockquote>No data available.";
	if($context->isInstructor()){
		echo " Upload a csv file containing at least one identifying column. Valid id columns are UD email addresses or UDID.";
	}
	echo "</blockquote>";
	
}
?>
</div>
<?php if($instructor): ?>

<hr />
<h4>Upload CSV</h4>

<form action="index.php" method="post" enctype="multipart/form-data">
  <p>
    <input type="hidden" size="41" name="context_id" value="<?php echo $context->info['context_id']; ?>" />
   
    <input name="postemfile" type="file" id="postemfile" size="50" onchange="toggleSubmit()"  />
 
    <input id="submit" type="submit" name="submit" value="Submit" disabled>
  </p>
</form>
<?php endif ?>
</body>
</html>