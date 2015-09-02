<html><head>
<style >
td{
	vertical-align:top;
}
h4{
	font-size:100%;
	font-weight:bold;
	line-height:1em;
	margin-bottom:.6em;
}
</style>
</head>
<body>

<?php
session_start();
$emplid=$_SESSION['cas_data']['EMPLID'];
include "/home/bkinney/includes/db_peer_sqli.php";
$proj=$_GET['proj'];
//formulate query

$query= sprintf("SELECT groupNum, name FROM glue WHERE surveyID=%u ORDER BY groupNum ASC",$proj);

$result = mysqli_query($link,$query);

	if($result){
	
		echo '<table cellpadding="20" border="1" width="100%"><tr><td><h4>Group 1</h4>';
		$currGroup = 0;
		$cols = 4;
		while ($row = mysqli_fetch_assoc($result)) {
		
			if($currGroup != $row['groupNum']){//new group
			
			if($currGroup > 0){
				 echo "</td>";
			
				if($currGroup % $cols == 0){//start a new row
					echo "</tr><tr><td>";
				}else{
					echo "<td>";//new cell in same row
				}
				echo "<h4>Group " .  $row['groupNum'] . "</h4>";
			}
				$currGroup = $row['groupNum'];
			
			}//close if new survey
		//close if new group	
		//if($row['evaluatee'] == $row['evaluator'] && $row['includeself']==0)continue;
		
			
			echo $row['name'];
			echo '<br>';
				
			
			
			
		}//close while
		if($currGroup % $cols ==0){
			echo "</table>";
		}else{
			while($currGroup % $cols >0){
				echo "<td></td>";
				$currGroup++;
			}
			echo "</tr></table>";
		}
	}else{
		echo "no result " . $result;
	}//close no result
mysqli_close($link);
?>
</body>
</html>


