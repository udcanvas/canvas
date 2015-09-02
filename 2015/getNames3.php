<html>
   <head>

   </head>
   <body>
<?php
if(empty($_POST['idlist'])){
	//include "importoptions.php";
	exit();
}
$idlist = $_POST['idlist'];

$ds=ldap_connect("ldap.udel.edu");  // must be a valid LDAP server!
	
if ($ds) {
	$response='<div contenteditable="true">';
	$r=ldap_bind($ds); 

		$group = $idlist;
		$type = preg_match("/[a-zA-z]+/",$idlist);
		//echo $type;
		$query = "(|";
		//$idlist = preg_replace("/\s/g",",",$idlist);
		//$id_arr = explode(",", $idlist);
		$id_arr = preg_split("/\s/",$group);
		$n = count($id_arr);
		
		
		//$response .= "group " . $g . "has " . $n . " members:";
		for($i=0;$i<$n;$i++){
			if($type==1){
				if(!empty($id_arr[$i]))$query .= "(mail=" . $id_arr[$i] ."@udel.edu)";
			}else{
			if(!empty($id_arr[$i]))$query .= "(udemplid=" . $id_arr[$i] .")";
			}
		}
		$query .= ")";
	
	// basic sequence with LDAP is connect, bind, search, interpret search
	// result, close connection
	

		     // this is an "anonymous" bind, typically
								// read-only access
	 
		 // Search surname entry
		 $sr=ldap_search($ds,"o=udel.edu", "$query");  
	 
		 $info = ldap_get_entries($ds, $sr);
		 //echo $info['count'];
		 if ($info["count"] == 0){
			 $status="error";
			 $detail="no matching entries";
		 }else{
			 $groupsize = $info['count'];
			
		 }
	
		 for ($i=0; $i<$info["count"]; $i++) {
		 // The department must be gleaned from the DN list, as it is part
			 // of the DN itself, rather than an attribute.	 
			$ln = $info[$i]["sn"][0];
			$fn = $info[$i]["givenname"][0];
			$name_array = explode(" ",$info[$i]["cn"][0]);
			 
		 
			 $name = $ln . ", " . $fn ;
			 if($name_array[1] != $ln)$name .=" " . $name_array[1];
		
		/*	 foreach($info[0] as $x){
				 print $x . " " . $info[0][$x][0];
				 print "<br>";
			 }*/
			 $fid=$info[$i]["udemplid"][0];
			 $email=$info[$i]["mail"][0];
			
		
			 // Escaping quotes gets lost when passed via JS, so just get rid of
			 // them.
		
				 $name=ereg_replace("'|\"", "", $name);
			 $email=ereg_replace("'|\"", "", $email);
				 $response .=  $name;
				 $response .=  " | ". $fid ." | " . $email; 
				if($i < $groupsize -1){
					$response .=  "<br>";
				}else {
					$response .=  "</div>";
				}
	
		}//close info loop
		
 	if($status=="error"){
		
		echo "LDAP error " . $detail . " " . $idlist;
	}else{

		echo $response;
	}

} else {//no ldap connection
    echo 'LDAP error: no connection';
}//close ldaploop
	
  
 
 ldap_close($ds);
  
  
 ?>
 
</body>
</html>
