<?php



if($_GET['courseid']){
	echo '<h3>Assignments</h3>';
	$step=0;
	$uri="/api/v1/courses/" . $_GET['courseid'] . "/assignments?";


	$roster = $api->get_canvas($uri);


//show groups as links
	if(count($roster)){
		
	$foundsome = false;
		foreach($roster as $grp){
			
		//echo '<p><a href="' . $host . $grp['outcomes_url'] . '">' . $grp['title'] . '</a></p>';
		if(isset($grp['rubric'])){
			$str = "";
			$foundsome=true;
			foreach($grp['rubric'] as $item){
				$str .= $item['description'] . ":" . $item['points'] . ",";
			}
			echo '<p><a href="#" onclick="loadAssignment(\'' . $str . '\')">' . $grp['name'] . '</a></p>';
			//echo '<p><a href="getasstrubrics.php?json=' . $str . '">Test ' . $grp['name'] . '</a></p>';
		}
		}
	}
	
		if(!$foundsome)echo "No assignment rubrics found";
		
	
	//end if count

}else if($_GET['json']){
	$json = json_decode($_GET['json'],true);
	echo '<div class="custom" >
<p contenteditable="false" class="general instructions">Place your cursor in the instruction text below to edit it. To edit rubric categories, click the associated pencil icon.</p>';
	echo '<p>Please evaluate this individual by rating his or her performance in each category. Maximum possible score for each category is indicated in parentheses.</p>';
	
	$arr = explode(",",$_GET['json']);
	array_pop($arr);
	foreach($arr as $cat){
		$arr2=explode(":",$cat);
		$pt = $arr2[1];
		$desc = $arr2[0];
		
		
		echo  '<p contenteditable="false">
    <a class="pencil"></a><input type="number" max="' . $pt . '" required>
    (' . $pt . '): ' . $desc . ' </p>';
	}
	echo '</div>';
}

?>

<script>

function loadAssignment(cat){
	currentEditor =$("div[id*=custominstructions]:visible");
    
	
	currentEditor.load("/git/lti/2014/getasstrubrics.php","json="+cat);
}

</script>