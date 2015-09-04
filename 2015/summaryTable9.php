<?php
$disposition=$_GET['disposition'];
require_once "/home/bkinney/includes/mysessions.php";
mysession_start();
$emplid=$_SESSION['cas_data']['EMPLID'];
//if(true){
	
	
	
if($_SESSION['facid'] == $_GET['facid']){
	$facid=$_GET['facid'];//mysqli_real_escape_string($_GET['facid']);
	$projectid=$_GET['proj'];
	
}else{//reauthenticate
//print_r($_SESSION);
include "/www/git/canvas/2015/timeout.php";
die();
}

include "/home/bkinney/includes/db_peer_sqli.php";	
	
//quick check to see whether we need to build this at all
/*$query=sprintf("SELECT * FROM evaluation WHERE surveyID=%u",$projectid);
$result=mysqli_query($link,$query);
if(!$result){
	echo "No evaluations have been submitted for this project.";
	exit();
}*/
$query = sprintf("SELECT * FROM glue WHERE surveyID=%u and udid>2000 ORDER BY  groupNum ASC, Name ASC",$projectid);
//added conditional on udid to eliminate products 2/22/13
$result = mysqli_query($link,$query);

	if(mysqli_num_rows($result)){
		
		
	$tee = "";
	$g = 1;
	$groupline="";
	$datarow="";
	$dataline="";
	$grouprows=array();
	$grouplines=array();
	$groupdatarows=array();
	$groupdatalines=array();
	$csv="";
	$peer="";
	$nameused=false;
	$members=array();
	$membernames=array();
	$str_membernames=array();//dealing with commas in names. yikes
	$table = '<table cellpadding="2" border="1">';
	$groupMax=0;
	//first we collect the data
	while ($row = mysqli_fetch_assoc($result)) {
		
		if($row['groupNum']==$g){//current row
			
			$members[$g][] = $row['udid'];
			$membernames[$g][] = $row['Name'];
			$str_membernames[$g][] = '"' . $row['Name'] . '"';
		}else{
			//add instructor to the old group
			$members[$g][]=$facid;
			$membernames[$g][]="Summary";
			$str_membernames[$g][]="Summary";
			$groupMax = max($groupMax,count($members[$g]));
			//construct previous group row

			$g++;
			
			//grab first member of next row. this is your only chance!
			$members[$g][] = $row['udid'];
			$membernames[$g][] = $row['Name'];
			$str_membernames[$g][] = '"' . $row['Name'] . '"';
		}
	}
	//grab last group, including summary
	$members[$g][]=$facid;
	$membernames[$g][]="Summary";
	$str_membernames[$g][]="Summary";
	$groupMax = max($groupMax,count($membernames[$g]));
	/*$grouprow = "<tr><td>Group " . $g . "</td><td>" . implode("</td><td>",$membernames[$g]);
			$grouprows[]=$grouprow;
			$groupline = "Group " . $g . "," . implode(",",$membernames[$g]) . "\r\n";
			$grouplines[]=$groupline;*/
			
		
			
	//start new loop, building the table from the data arrays collected above
/*	if($groupMax<=2){
		echo "No multi-member groups" ;
		exit();
	}*/
	for($g=1;$g<=count($members);$g++){
		//if(count($members[$g])==2)continue; //skip single member groups
		$firstcolspan = $groupMax-count($members[$g]) + 1;
		$commas = "";
		for($c=1;$c<$firstcolspan;$c++){
			$commas.=",";
		}
			$grouprow = "<tr><td colspan='" . $firstcolspan . "'>Group " . $g . "</td><td>" . implode("</td><td>",$membernames[$g]) . "</tr>";
			$groupline = "Group " . $g . "," . $commas . implode(",",$str_membernames[$g]) . "\r\n";
			$grouplines[]=$groupline;
			$grouprows[]=$grouprow;

	$gdata="";
	$ldata="";
		for($r=0;$r<count($members[$g]);$r++){
			if($membernames[$g][$r]=="Summary"){
				$gdata.='<tr><td colspan="' . ($groupMax +1) . '" bgcolor="#999999" height="10px"></td></tr>';
				$ldata .="\r\n";
				continue;
			}
			
			$datarow="<tr><td colspan='" . $firstcolspan . "'>" . $membernames[$g][$r] . "</td>";
			$dataline=  $str_membernames[$g][$r] . "," . $commas;
			$query2= sprintf("select * from evaluation where surveyID=%u and Evaluatee='%s' order by Evaluator ASC",$projectid,mysqli_real_escape_string($link,$members[$g][$r]));
	
			
			$result2=mysqli_query($link,$query2);
			if($result2){
				//if(mysqli_num_rows($result2)){
					$cells=array();
					while ($row2 = mysqli_fetch_assoc($result2)) {
						//find the right cell
						
						for($p=0;$p<count($members[$g]);$p++){
							if($row2['Evaluator']==$members[$g][$p]){
	
								$cells[$p]= $row2['custom'] . "<br>" . $row2['Grade'] ."<br>" .  $row2['comment'];
							}
						}
						
							
						
					}
					for($p=0;$p<count($members[$g]);$p++){
						$datarow .= "<td>" . $cells[$p] . "</td>";
						$cleaned = str_replace('"',"'",$cells[$p]);
						$dataline .= '"' . $cleaned.'",';
					}
				
				}//no data at all for this member
					else{
						for($i=0;$p<count($members[$g]);$i++){
							$datarow .= "<td></td>";
						}
					}
				$datarow .= "</tr>";
				$dataline .= "\r\n";
					$gdata .= $datarow;
					$ldata .= $dataline;
		
					
				
				
		
		}//end member loop
		//grab last group
		
		$groupdatarows[] = $gdata;	
		$groupdatalines[] = $ldata;
			
	
	}//end group loop	
		
	for($g=0;$g<count($grouprows);$g++){
		/*$numExtra= $groupMax - count($members[$g]) ;
		for($ii=0;$ii<$numExtra; $ii++){
			$grouprows[$g] .= "<td></td>";
		}
		$grouprows[$g] .=  "</tr>";*/
		//for($i=count($members[$g]);$i<$groupMax;$i++){
			//if($g>0)
		//}
		//if($g>0)
		//$grouprows[0] = "";
		$table .= $grouprows[$g] . $groupdatarows[$g];
		$table = str_replace("<td></td>","<td>&nbsp;</td>",$table);
		$csv .= $grouplines[$g] . $groupdatalines[$g];
	}
	$table .= "</table>";

}
mysqli_close($link);
if($disposition=="download"){
	header("content-disposition:attachment;filename=peer_summary" . $projectid . ".csv");
	//header("content-disposition:attachment;filename=peer_evals.csv");
header("content-type:text/csv");
	$csv = str_replace("<br>","   ",$csv);
	echo $csv;
}else{
		//echo $firstrow;
	echo "<html><head/><body >" . $table . "</body></html>";
}


?>