<?php
//print_r($_FILES['upload']);
	$filepath=$_FILES['upload']['tmp_name'];
	//step 1
	$postdata = array(
		"size" => $_FILES['upload']['size'],
		
		"content_type" => $_FILES['upload']['type'],
		"parent_folder_path" => "/postem",
		"name" => "postem.csv"
);


     $result = post_canvas($uri,"POST",$domain,$postdata); 

	$conn = curl_init();
	$postdata = $result['upload_params']; //Load returned upload parameters
	$postdata['file'] = '@'.$filepath;
	curl_setopt($conn, CURLOPT_URL, $result['upload_url']); //URL for request
	curl_setopt($conn, CURLOPT_POST, TRUE); //Set POST method
	curl_setopt($conn, CURLOPT_POSTFIELDS, $postdata); //Set POST data
	curl_setopt($conn, CURLOPT_FOLLOWLOCATION, TRUE);//THIS IS THE KEY!
	curl_setopt($conn, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($conn, CURLOPT_HEADER, true); //Show return headers in result
    if( ! $result2 = curl_exec($conn)) 
    { 
        trigger_error(curl_error($conn)); 
        echo "error";
    } 

            preg_match('/Location:(.*?)\n/', $result2, $matches);
            $newurl = trim(array_pop($matches));
  
	curl_close($conn); //Close CURL session
    
	//print_r($postdata);
	//echo count($matches);
	//echo "result2 <br>";
	//echo $newurl;echo '<br>------------<br>'; 
	$ch = curl_init($newurl);


	
	//curl_setopt($conn, CURLOPT_POST, TRUE); //Set POST method
	//curl_setopt($conn, CURLOPT_POSTFIELDS, $postdata); //Set POST data
	curl_setopt( $ch, CURLOPT_HTTPHEADER, 'Authorization: Bearer ' . $_SESSION['token']);
	$result = curl_exec($ch);
	$roster = json_decode($result);


?>