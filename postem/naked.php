<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>help</title>
</head>

<body>
<?php 
$data = '<table class="stickyHeader" ><thead>';
	$url="https://docs.google.com/spreadsheet/pub?key=0AnYGEQq7vP34dGxpVy14VXFrMzJZNi1kWmdkN25ic0E&single=true&gid=0&output=csv";
	
	$handle=fopen($url,"r");
	
	$cols = fgetcsv($handle, 10000, ",");//just the first row
	$data .= "<tr><th>" . implode("</th><th>",$cols) . "</th></tr>";
	$data .= "</thead><tbody>";
	
	while (($cols = fgetcsv($handle, 10000, ",")) !== FALSE) {
		$clean = array_map(htmlspecialchars,$cols);
		$data .= "<tr><td>" . implode("</td><td>",$clean) . "</td></tr>";
	}
	$data .= "</tbody></table>";
	echo $data;

?>
</body>
</html>