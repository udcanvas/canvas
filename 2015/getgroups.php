<?php
//die($_REQUEST['custom_domain_url']);
/*$secret = array("table"=>"blti_keys","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");

include '/www/canvas/2015/canvas_dance_include.php';//now I have a CanvasApi object named $api*/
$secret = array("table"=>"key_token_view","key_column"=>"oauth_consumer_key","secret_column"=>"secret","context_column"=>"context_id");

include '/www/canvas/canvas_dance_include.php';

if($_GET['courseid']){
	$step=0;
	$uri="/api/v1/courses/" . $_GET['courseid'] . "/group_categories?";

	
	//$json = file_get_contents($host . $uri . $token);
	$roster = $api->get_canvas($uri,true);

//show groups as links
	if(count($roster)){
		echo "Please select a group category";
		foreach($roster as $cat){
			echo '<p><a style="cursor:pointer" onclick="loadCategory(' . $cat['id'] . ')">' . $cat['name'] . '</a></p>';
		}
	}else{
		echo "No group categories found";
	}
	//end if count
echo '<p>OR</p><p> <a style="cursor:pointer" onclick="loadCanvasRoster(' . $_GET['courseid'] . ')">Import Course Roster</a></p>';
}
if($_GET['catid']){
	$uri="/api/v1/group_categories/" . $_GET['catid'] . "/groups?";
		$roster = $api->get_canvas($uri);
		//$roster = json_decode($json,true);
			$groups = array();
			$groupnames = array();
		foreach($roster as $group){
			$groupnames[]=($group['name']);
			$thisgroup="";
			$uri = "/api/v1/groups/" . $group['id'] . "/users?";
			
			
			$glist = $api->get_canvas($uri,true);
			
			foreach($glist as $member){
				$thisgroup .= $member['sis_user_id'] . " ";
			}
			$groups[] = $thisgroup;
		}
		$obj = (object) array("groups" =>$groups, "groupnames" => $groupnames);
		echo json_encode($obj) ;
		
	
}
?>
<script>
function loadCategory(cat){
	$("#canvasgroups").load("/git/lti/2015/getgroups.php", "catid=" + cat,function(response){
		cloneGroups($(this).text());
	});
}

</script>	




