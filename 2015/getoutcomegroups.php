
<?php

$secret = array("table"=>"key_token_view","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");

include '/www/canvas/canvas_dance_include.php';



	$host = "https://udel.instructure.com";

if($_GET['courseid']){
	echo '<h3>Outcome Groups</h3>';
	$step=0;
	$uri="/api/v1/courses/" . $_GET['courseid'] . "/root_outcome_group?";
	//$uri = "/api/v1/accounts/101695/outcome_groups/170719";


	$roster = $api->get_canvas($uri,false);
	$foundsome=false;

//show groups as links
	if(count($roster)){
		
		$outcomes = $api->get_canvas($roster['subgroups_url']) ;
		if(count($outcomes))$foundsome=true;
		foreach($outcomes as $grp){
		//echo '<p><a href="' . $host . $grp['outcomes_url'] . '">' . $grp['title'] . '</a></p>';
		echo '<p><a href="#" onclick="loadCategory(' . $grp['id']. ' )">' . $grp['title'] . '</a></p>';
		}
	}
	if(!$foundsome){
		echo "No rubrics found";
		//echo $host . $roster['outcomes_uri'];
	}
	//end if count

}else if($_GET['catid']){
	$uri = '/api/v1/courses/'. $_GET['cid'].'/outcome_groups/'.$_GET['catid'];
	$desc = $api->get_canvas($uri,false);
	echo '<div class="custom" >
<p contenteditable="false" class="general instructions">Place your cursor in the instruction text below to edit it. To edit rubric categories, click the associated pencil icon.</p>';
	echo str_replace('href="','target="helper" href="',$desc['description']);
	$uri .= '/outcomes';
	$categories = $api->get_canvas($uri,false);
	
	foreach($categories as $cat){
		$points = $api->get_canvas("/api/v1/outcomes/" . $cat['outcome']['id'],false);
		$desc = $cat['outcome']['title'];
		$pt = $points['points_possible'];
		
		echo  '<p contenteditable="false">
    <a class="pencil"></a><input type="number" max="' . $pt . '" required>
    (' . $pt . '): ' . $desc . ' </p>';
	}
	echo '</div>';
}

?>
 
<script>

function loadCategory(cat){
	currentEditor =$("div[id*=custominstructions]:visible");
var cid = <?php echo $_GET['courseid']; ?>;	
currentEditor.text(url);
	var url = "getoutcomegroups.php?catid=" + cat + "&cid=" + cid;
	

	currentEditor.load("/git/lti/2015/getoutcomegroups.php", "catid=" + cat + "&cid=" + cid);
}

</script>


<?php include "/www/git/lti/2015/getasstrubrics.php" ?>