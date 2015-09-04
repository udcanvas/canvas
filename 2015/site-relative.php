 <!--Change log:
add strtolower to LTI data interpreter
sort roster alphabetically
fix title when returning from Create New to Project Options
------ summer 2014 -----------
hide first group from submitall
omit evaluatee<2000 from view student_evals_all ??
adjust out of in code for waiting members of group 1
replace non_responders query with direct query in email2_1.php (big performance boost)
remove product evals from non-responders
remove group1 from non-responders
remove demos from help page - open in new window instead

-------------winter 2015-----------------
separate analytics downloads from grade download, add new button to summary tab
add grade pushback for Canvas without export. Includes comments
add Banjo analytics download - compliance data compares number of submissions per student to group size

-->
<!-- 
dependencies:
php: /www/LTI/blti, logout, /www/peer/auth-cas, /www/canvas/auth-fork-token, footer2, help3 [faq_faculty_2013f], defaultInstructions,
pdefaultInstructions,peer/student-help, email3[timeout], getNames3[ldap],

showGrades2[includes/db_peer_sqli.php,includes/studentform5][released_active,grades2, glue],
studentData3 (local)
includes/studentData3[includes/dbconnect_peer] [peer_evals, product_evals, student2], 
studentResults7[includes/dbconnect_peer, submitEval2, studentform5] [grades2, student_evals_all, product_evals_all, assigned_evals, studentall, productall, glue_assigned2],
profData5[includes/dbconnect_peer, includes/mysessions] [instructor5, grades3],
email2_1[timeout, email2] [ glue_assigned2],
summaryTable9[includes/dbconnect_peer] [],
emailGroup[timeout,dbconnect_peer],
studentGroups[dbconnect_peer],
exportSakai6[dbconnect_peer] [pending, instructor4, grades],
submitEval[dbconnect_peer] ,
 peer/ping,
 getoutcomegroups.php,
 getasstrubrics.php,getgroups.php
 analytics.php
 accountability.php
 pending.html
 postGradesToCanvas.php[pending]
 
 

------------------------------
js: canvas_functions.js
views: 
 
-->
<?php 

//error_reporting(E_ALL);
//ini_set('display_errors', 1); 
require_once( "/home/bkinney/includes/mysessions.php");
mysession_start();
/*$blti=false;
if(isset($_POST['lis_person_sourcedid'])){
	// Load up the Basic LTI Support code
	require_once '/www/LTI/ims-blti/blti.php';
	
	// Initialize, all secrets are 'secret', do not set session, and do not redirect
	$context = new BLTI("myrealsecret", false, false);
	$blti = $context->valid;
}*/


if(array_key_exists("logout",$_GET)){

	include "/www/git/canvas/2015/logout.php";
	
 //sets $usertype to loggedout, destroys session and cookies if any
}else {	
//stuff cas needs
	$THIS_SERVICE = "index.php";
	$STATUS = "PRODUCTION";//"TEST"
	//$PROTOCOL = "https";
	
	mysession_start();
	if(empty($_SESSION['redirect'])) $_SESSION['redirect']=$_SERVER['PHP_SELF'];
	$secret="myrealsecret";
	
	if(!isset($loggedin)) include "/www/git/canvas/auth-fork-token.php";
	//$blti = $context->valid;
	


if($blti){

	if(isset($context->info['custom_canvas_course_id'])){
	//	echo "canvas";
		$canvas = 1;
			 $courseid = $context->info['custom_canvas_course_id'];	
	 //importRubric is in canvas_functions.js
		 $canvas_rubric = '<a href="#" onClick="importRubric()">Import Canvas Rubric</a>';
		 if($context->info['custom_canvas_api_domain'] == "udel.instructure.com"){
			 $udel = true;//not using this
		 }else{
			 // somebody else, need to redirect all traffic to the database to alt
			 $udel = false;
			//$uniqueid = getCourseKey();//12345:[context_id]	
		 }
	}else{
		$canvasgroups = $canvas_rubric = "";
		$canvas=0;
	}

	
	$emplid=$context->info['lis_person_sourcedid'];
	$firstName = $context->info['lis_person_name_given'];
	$lastName = $context->info['lis_person_name_family'];
	$udelnetid = strtolower($context->info['lis_person_contact_email_primary']);
	$udelnetid = str_replace("@udel.edu","",$udelnetid);
	$usertype = $context->info['roles'];
	$usertype = str_replace("Instructor","FACULTY",$usertype,$c_instructor);
	$usertype = str_replace("TeachingAssistant","FACULTY",$usertype,$c_ta);
	
	$usertype = str_replace("Learner","STUDENT",$usertype,$c_student);
	$domain=$context->info['custom_domain_url'];
		if(($c_instructor || $c_ta) && $canvas)$canvasgroups = "https://apps.ats.udel.edu/peer/2015/getgroups.php?courseid=" . $courseid . "&custom_domain_url=" .$domain;
	$canvasrubrics = "https://apps.ats.udel.edu/peer/2015/getoutcomegroups.php?courseid=" . $courseid . "&custom_domain_url=" . $domain;
	
	
	
	mysession_start();
	$_SESSION['cas_data']['EMPLID']=$emplid;
	$_SESSION['cas_data']['USER']=$udelnetid;

	//session_write_close();
/*	foreach($_POST as $key => $value ) {
   		 print "$key=$value<br>";
			exit();
		}*/
}else{//cas
	$canvas=0;

	
	
	$cas_data = $_SESSION['cas_data'];
	$emplid =  $cas_data['EMPLID'];//empty($cas_data['EMPLID']) ? $casdata['USER'] : $cas_data['EMPLID'];
	$firstName =  $cas_data['FIRSTNAME'];
	$lastName = $cas_data['LASTNAME'];
	$udelnetid = $cas_data['USER'];//empty($cas_data['UDELNETID']) ? $casdata['USER'] : $cas_data['UDELNETID'];
	$usertype = $cas_data['PERSONTYPE'];
	//remove this when you go live! just allows staffers to test
	//$usertype="FACULTY STUDENT";

		if(!empty($_SESSION['usertype'])){
			
			$usertype = $_SESSION['usertype'];
		}
	

	

 
 
	if(empty($cas_data)){//
		
		session_name("testing");
		mysession_start();//auth-cas does this, but  we have no CAS data
		$firstName =  "Becky";
		$lastName = "Kinney";
		$emplid = $_GET['udid']; 
		if(!isset($emplid))$emplid="10914";//701109365";//"701061351;
		
			$udelnetid = "bkinney";
			$_SESSION['cas_data']['EMPLID']=$emplid;
			$_SESSION['cas_data']['USER']=$udelnetid;
			//$usertype = $_GET['persontype'];
			if(!isset($usertype))$usertype="FACULTY STUDENT";
			//echo $usertype;
		}

}// end if CAS
/*	$ta = 0;	
	if($_SESSION['usertype']=="TA"){
		$usertype="FACULTY STUDENT";
		$ta = 1;
	}*/

}//end if not logged out
if( preg_match("/FACULTY/",$usertype) || preg_match("/(?<!MISC_)STAFF/",$usertype)){
	$usertype="FACULTY STUDENT";
	$testing=true;
}else if($usertype!="loggedout"){
	$usertype="STUDENT";
	$testing=false;
}

$lastName=str_replace("'","&apos;",$lastName);
$firstName=str_replace("'","&apos;",$firstName);
setcookie('emplid',$emplid,0,"/");



/*foreach($cas_data as $x){
	echo $x;
}*/
//if($usertype !=  "loggedout")$testing = true;
//if( preg_match("/FACULTY/",$usertype) || preg_match("/STAFF/",$usertype))$testing=true;
if($testing){
	$studentid = $emplid;
	$facid = $emplid . "9";
 	if($canvas)$facid="can" . $courseid;
	mysession_start();
	$_SESSION['facid'] = $facid;
	
	//if(strlen($facid) > 9)$facid="1". substr($emplid,1);
}else{
	$studentid=$facid=$emplid;
	
}


//exit();
?>
<!DOCTYPE HTML>
<html><head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>Peer Evaluation</title>
<link rel="Shortcut Icon" href="/peer/Styles/favicon.ico" type="image/ico" />

<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/jquery-ui.min.js"></script>

<!--<script type="text/javascript" src="../jqueryui/development-bundle/ui/jquery.ui.position.js"></script>
<script type="text/javascript" src="../jqueryui/development-bundle/ui/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="../jqueryui/jquery.ui.tabs.js"></script>-->
<link id="togglestyle" href="/peer/Styles/2014s.css" rel="stylesheet" type="text/css" />
<?php if ($canvas) : ?>
<script type="text/javascript" src="/peer/2015/canvas_functions.js"></script>
<?php endif ?>
<script type="text/javascript">

function toggleStyle(){
	
	var old = "/peer/Styles/2013s.css";
	var flex = "/peer/Styles/2014s.css";
	if($("#togglestyle").attr("href")!=flex){//some strange page speed stuff is screwing with my original href
		$("#togglestyle").attr("href",flex);
		$("#currstyle").text("Current: Flex ");
	}else{
		$("#togglestyle").attr("href",old);
		$("#currstyle").text("Current: Float ");
	}
}

</script>
<style>

#fachelp,#results{overflow:auto}
#stabs{overflow:auto}
#tabs div.btns a{padding-top:5px;}
.rounded h3.ui-corner-top.ui-state-active {
line-height:1.3;
}
#main div.rounded{margin-top:5px;}
#tabs p.instructions,#tabs div.button a{margin:0 0 0 0};
.rounded{height:auto;}
body{overflow-y: auto;
overflow-x: hidden;}
#footer{position:relative;}
#groups{height:100%;max-height:450px;}
#groups h3{margin-top:0;}
#tabs{min-height:0}
#tabs .ui-tabs-panel,#tabs div.buttons{padding:5px 0}

#main{}
#leftside:visible.hidden{
	flex-basis:0;
	flex-grow:0;
	display:block;
}
#groups.roster{
	float:right;
	text-align:left;
	width:45%;


	overflow:auto;
	margin-right:2%;
}
#groups.roster ul{
	width:85%;
	
}
.canvas{
	<?php if(!$canvas) echo 'display:none;' ?>
}
.step input[name=submit],.step input[name=cancel_create]{display:none;}
.temp ul{
	max-height:400px;
	margin-top:-40px;
	overflow-y:auto;
}
.temp ul.troster{
	margin-top:0;
	
}
.ui-tabs-panel{min-height:57px}
.hidden{display:none}
</style>
 
</head>
<body>


<div class="header" <?php if($canvas) echo 'style="display:none"' ?>>
	<div class="title"></div>
    <div class="loginDisplay"><span id="lblNickName">
    <?php if($loggedin):?>
    <a href="/peer/logins.php">[Test Log Out ]</a>
    <?php endif ?>
  <!--  <span id="currstyle">Current: Flex</span>  <a  href="#" onClick="toggleStyle()">[ Toggle Style ]</a> -->
<?php if($usertype == "loggedout"): ?>
<a class="login" href="<?php echo $_SERVER['PHP_SELF']?>">[ Log In ]</a>
</span></div></div>
<!-- login menu -->

<div class="vscroll login">  <ul class="formlist">
  <p ><a class="login" href="/peer/student.php">Student Login</a></p>
  <p><a class="login" href="/peer/index.php">Faculty Login</a></p>
  <p><a class="login" href="/peer/m.php">Mobile Login </a>(Student only)</p></ul></div><div class="clear"></div><div id="email2" class="rounded hidden"><h3 class="ui-widget-header ui-corner-top">Logged Out</h3><p>To log back into this tool, click Peer Evaluation in your Canvas menu, or refresh your browser.</p></div>
  </div>
  
  <!--end new div all for flex-->
  <?php include "/www/git/canvas/2015/footer2.php" ?>
<script>
 if(window!=window.top){
	 
	 $(".login,.header").hide();
	 $("#email2").css("float","none").css("margin-left","auto").css("margin-right","auto").css("margin-top","20px").show();
 }
</script>
</body></html>
<?php die()?>


<?php else: ?>



Welcome, <?php echo $firstName ?> <a href="/git/canvas/index.php?logout=true">[ Log Out ]</a>
<?php endif ?>

</span>
    </div><!--end login display-->
</div><!--end header -->

            

 <?php if($testing): ?>	
<div id="tabs">
	<ul>
    <li class="faculty"><a href="#optionsview">Project Options</a></li>
    <li class="faculty"><a href="#exportview">Summary</a></li>
    <li class="faculty"><a href="#wizardview">Create New Project</a></li>
	<li><a href="#studentview">Student View</a></li>
    <li><a href="#es"  class="student" onclick='$("#tabs").tabs("select",0);'>Exit Student View</a></li>
    <li class="student"><a href="#summaryview">My Grades</a></li>
	<li><a href="#fachelp" >Help</a></li>		
	<li><a href="#logout" >Log Out</a></li>
	
	</ul>
    <div id="logout"></div>
    <div id="fachelp"><iframe width="100%" height="1500" scrolling="auto" src="/git/canvas/2015/help3.php" style="border:none"></iframe> </div>
    <div id="summaryview" class="fullheight"> 
<h1>Feedback for <?php echo $firstName . " " . $lastName ?></h1>
<div id="exportoptions" title="Export Options"  ></div>
<div id="summaryResults" style="width:80%"></div>

</div>

    <div id="optionsview"><h1 class="center" id="pagetitle">Faculty View</h1><p class="instructions">Welcome to the Peer Evaluation Tool. To get started, choose the <b>Create New Project</b> tab, or select one of your existing projects.</p>

    
    
    
    </div>
    
    
    
    <div id="exportview" class="btns" ></div>
 <!--   <div id="helpview"><a href="https://ats.udel.edu/projects/peer/help.php" target="peerhelp" class="rounded btn">Open Full Help (in new window or tab)</a> Context-sensitive help will appear here.</div>-->
    <div id="studentview" class="btns"><p class="instructions">Expand a project from the Peer or Product Evaluations menu to access your evaluations.</p></div>
        <!--<div id="fachelp"><iframe width="100%" height="1500" scrolling="auto" src="help3.php" style="border:none"></iframe> </div>-->
    <div id="wizardview" class="fullheight">    <div id="evalForm"  class="rounded"><h3 class="ui-widget-header ui-corner-top"></h3>
	<div id="instructions">
<p>Each project allows a single evaluation event, in which all students within a group evaluate each other and themselves. Future versions will allow for the creation of multiple evaluation events per project, to encourage improvement over the course of a single project.</p>
	</div><!--end instructions -->
 <form id="submitEval" >
  <p>
    <label for="projectName"> Project name: </label>
    <input name="projectName" id="projectName" type="text" autofocus accesskey="p" tabindex="1" placeholder="Choose a name (required)" size="50" alt="project name" required onChange="$('#projectName-w').val(this.value);"/>
    <input name="instructorID" id="instructorID" type="hidden" value="<?php echo ($testing) ? $facid : $emplid  ?>">
  </p>
  <div id="ptplaceholder">
  <div id="selectProjectType">
  <p>Feedback Type :
  <label>
  
    <input type="radio" name="project-type" value="0" id="project-type_0" checked onChange="setProjectType(0,true)">
    peer (intra-group)</label>
  
  <label>
    <input type="radio" name="project-type" value="1" id="project-type_1" onChange="setProjectType(1,true)">
    product (inter-group)</label>
 
  <label>
    <input type="radio" name="project-type" value="2" id="project-type_2" onChange="setProjectType(2,true)">
    both</label>
 
</p></div><!--end select project type -->
</div><!-- end ptplaceholder -->
  <p><label for="active">Active: </label><input name="active" type="checkbox" value="active" id="active" checked onChange="$('#active-w').attr('checked',this.checked);"> (check to release evaluation forms now)
  </p>
<p><label for="includeself">Include self-evaluation: </label><input name="includeself" type="checkbox" value="true" id="includeself" checked onChange="$('#includeself-w').attr('checked',this.checked);"> 
  </p>
  <div id="custom" >
    <h3 class="peer"><a href="#top">Customize Peer Evaluation Rubric</a> </h3>
    <div id="eplaceholder" class="peer">
    <div id="editor">
    <div class="toolset"><?php if(isset($canvas_rubric))echo $canvas_rubric ?>
    <a href="#" onClick='currentEditor=$("#eplaceholder");$( "#dialog-category" ).dialog( "open" );' >add new evaluation category</a><a  href="#" onClick='wysiwyg("bold",this);'>b</a>
<a href="#" onClick='wysiwyg("italic", this);'>ital</a>
<a href="#" onClick="createLink()">link</a>
</div><!--end .toolset -->
<div id="custominstructions" contenteditable ><?php include "/www/git/canvas/2015/defaultInstructions.php" ?></div><!-- end custominstructions-->
    <blockquote><p class="instructions">The remaining fields will appear beneath the instructions in every evaluation, and can not be customized. Total score will be summed automatically as component scores are entered.</p>
    <p>
      Total score: 
        <input name="grade" type="text" value=""  disabled >
    </p>
    <p>
     Comment : <br><textarea cols="50" rows="5" name="comments" disabled ></textarea>
    </p></blockquote>
</div><!-- end editor -->
</div><!-- end editor placeholder -->
<h3 class="presentation"><a href="#top">Customize Product Rubric</a></h3>
    <div id="pplaceholder" class="presentation">
    <div id="peditor"><p class="instructions">Edit these instructions to suit your project</p>
    <div class="toolset"><?php if(isset($canvas_rubric))echo $canvas_rubric ?>
    <a href="#" onClick='currentEditor=$("#pplaceholder");$( "#dialog-category" ).dialog( "open" );' >add new evaluation category</a><a  href="#" onClick='wysiwyg("bold",this);'>b</a>
<a href="#" onClick='wysiwyg("italic", this);'>ital</a>
<a href="#" onClick="createLink()">link</a>
</div><!--end .toolset -->
<div id="pcustominstructions" contenteditable ><?php include "/www/git/canvas/2015/pdefaultInstructions.php" ?></div><!-- end custominstructions-->
    <blockquote><p class="instructions">The remaining fields will appear beneath the instructions in every evaluation, and can not be customized. Total score will be summed automatically as component scores are entered.</p>
    <p>
      Total score: 
        <input name="grade" type="text" value=""  disabled >
    </p>
    <p>
     Comment : <br><textarea cols="50" rows="5" name="comments" disabled ></textarea>
    </p></blockquote>
</div><!-- end editor -->
</div><!-- end editor -p placeholder -->
</div><!-- end custom accordion -->
<div id="groupshome">
<div id="groupcreation">
 <div class="groupcreation" >
  <div id="rostertools"> Randomly generate groups of <input type="text" name="numGroups" size="5" id="groupSize" /> or fewer <input type="button" onClick="randomGroups()" value="go" /><br><input type="button" value="create empty group" onClick="getNames()"/><input type="button" class="roster group" value="Create Project"  onClick="compileAndSubmit()"><input type="button" name="cancel_create" id="cancel_create"  value="Cancel Project" accesskey="x" tabindex="5" onClick="showOptions()" class="roster group"></div><!--end rostertools-->
  

  
 
  <div class="temp"><p class="instructions"><b>Temporary Drag Location</b>.<br/>Drag groups or members here to facilitate re-grouping.</p><ul><input type="button" value="create as group" id="sag" onClick="temptogrp()" style="display:none"></ul></div>
  <p class="instructions group" style="text-align:left;padding-right:4px;margin-right:4%">
      Groups: 
      enter student ids (UDID OR ud email address, NO NAMES), separated by lines, spaces, or tabs. After each group is created, enter a new list to create another group. <i>An initial one-person group has been created for you so that you can see your project in student view.</i>
  </p>
 

<p><textarea class="idlist" name="idlist" id="idlist" rows="5"  cols="23" tabindex="4" ></textarea></p>
 
 
    
    <p>
    
<!--<input class="canvas roster" type="button" value="use canvas roster" onclick="loadCanvasRoster()"/>-->
<input class="canvas roster" type="button" value="import from Canvas" onclick="launchGroups()"/>
 

      <input class="group" type="button" value="create group" onClick="getNames()"/><input class="roster" type="button" value="use as roster" onClick="getNames(true,false)"/>
  </p>    <p>
      <input type="button" name="submit" id="submit"  value="Create Project" accesskey="s" tabindex="4" onClick="compileAndSubmit()"><input type="button" name="cancel_create" id="cancel_create"  value="Cancel Project" accesskey="x" tabindex="5" onClick="showOptions()">
      
    </p>
   
</div><!-- end .groupcreation --> 
 <div id="groups">F</div>
</div><!--end id group creation -->

</div><!-- end groupshome -->
</form><!--end form -->


      </div> 
      
      <div id="wizard">
        <div class="step" style="display:block" id="step1" title="Step 1: Project Name">
        <div id="instructions">
       <p><b>Note: </b>This wizard contains step-by-step instructions designed for first-time users. You can exit the wizard at any time and still edit all options. When editing an existing project, or after exiting the wizard, you will see a single scrollable interface containing all Project options.</p> <p>Each project allows a single evaluation event, in which all students within a group evaluate each other and themselves. </p></div>
        <p class="instructions">Enter a name for your project. It is a good idea to include information such as department codes and course numbers, but any name is acceptable. The name you enter here will appear in the Project listing (to the left) in both instructor and student view.</p>
        
          <p>
            <label for="projectName"> Project name: </label>
            <input name="projectName" id="projectName-w" type="text" autofocus  placeholder="Project name" size="50" form="evalForm" alt="project name" required onChange="$('#projectName').val(this.value);"/>
            <input name="instructorID" id="instructorID" type="hidden" value="<?php echo ($testing) ? $facid : $emplid  ?>">
          </p>
          
          </div><!-- end step1 -->
          <div id="step1a" title="Step 2: Select Project Type">
          <p class="instructions">This tool can be used to collect two types of feedback. Select 'peer' to enable each student within a collaborative group to evaluate members of his group. Select 'product' to enable students to evaluate a project, paper or presentation completed by groups other than his own. Select both if both types of feedback are desired, but the relevant groups are the same.</p>
          </div><!--end step 1a -->
      <div class="step" id="step2" title="Step 3: Project Settings">
      <p class="instructions">Only active projects can be seen by students. Uncheck the 'Active' checkbox if you wish to release this project at a later date.</p>
      <p><label for="active-w">Active: </label><input name="active-w" type="checkbox" value="active" id="active-w" checked  onChange="$('#active').attr('checked',this.checked);"> 
      </p>
      <p class="instructions">Uncheck the self-evaluation checkbox if you do not wish to have students evaluate themselves.</p>
    <p><label for="includeself">Include self-evaluation: </label><input name="includeself" type="checkbox"  form="evalForm" value="true" onChange="$('#includeself').attr('checked',this.checked);" id="includeself-w" checked> 
      </p>
      </div><!-- end step2 -->
  <div class="step" id="step3" title="Step 4 (optional): Customize Peer Evaluation Rubric">
  <p class="instructions">The default Peer evaluation form includes four categories shown below. Each category is worth 25 points. To accept the default instructions, click Next Step at the bottom of this window. To customize instructions, use the editing tools below.</p>
 
 
</div><!--end step3-->
  <div class="step" id="step3a" title="Step 4a (optional): Customize Product Evaluation Rubric">
  <p class="instructions">The default Product evaluation form includes the two categories shown below. Each category is worth 25 points. To accept the default instructions, click Next Step at the bottom of this window. To customize instructions, use the editing tools below.</p>
 
 
</div><!--end step3a-->

<div class="step" id="groupoptions" title="Group Creation Options">
<p class="instructions">The final step in the process is to generate student groups. There are three ways to accomplish this.</p>
<p><b>Group by Group: </b>Submit a short list of student identifiers (UDID or email) to create a group containing only those students. Continue until all groups are created.</p>
<p><b>Entire Roster: </b>Submit your entire roster. You can then have the system randomly generate groups of a given size, or create empty group containers and drag students from the roster into groups.</p>
<p><b>Exit Wizard: </b>Exit the wizard now for maximum flexibility in group creation. From outside the wizard, you will have access to both options above in a single interface.</p>
<?php if ($canvas) : ?>
<p><b>Canvas users: </b>To import groups (by category) or rosters from Canvas, choose the Entire Roster option.</p>
<?php endif ?>
<p class="instructions">Please select your preferred method</p>
</div>  <!-- end group options -->


<div class="step" id="step4a" title="Step 5: Student Groups">
<p>To create student groups, you must identify students using their UDID (a 9 digit number), UDELNETID (alphanumeric), or email address. Separate student identifiers with a comma, space, tab or line break. Copying and pasting a column from a roster spreadsheet works well. </p>

   <p class="instructions">
     Groups: enter a list of student ids or usernames, separated by lines, spaces, or tabs. After each group is created, enter a new list to create another group.
  </p>
  <div id="groupsa"><!--placeholder for groupcreation -->


   
</div><!-- end group creation -->

</div><!-- end step4 -->
<div id="step4b" class="step" title="Step 5: Student Roster">
<p class="instructions">
Copy a <b>single</b> column of data (UDID OR email) from your roster. 
Paste your roster below, and click 'use as roster'. Then drag members into empty groups, or generate random groups.</p>
<div id="groupsb">

  </div> <!-- end groupcreation -->
</div><!-- end step4b -->
 </div> <!-- end wizard -->
 </div><!-- end wizardview-->
    
</div><!-- tabs-->

<?php else: ?>
<div id="stabs">
	<ul>
        <li><a href="#studentview">My Projects</a></li>
        <li class="student"><a href="#summaryview">My Grades</a></li>
        <li><a href="/git/canvas/student-help.php" >Help</a></li>		
	<li><a href="#logout" >Log Out</a></li>
	
	</ul>
    <div id="logout"></div>	
	<div id="summaryview" class="fullheight"> 
<h1>Feedback for <?php echo $firstName . " " . $lastName ?></h1>
		<div id="summaryResults"><?php include "/www/git/canvas/2015/showGrades2.php" ?></div>
    </div>
	<div id="studentview" class="btns" ><h1 class="center" id="pagetitle">
	<?php 

		echo "Peer Evaluation: " . $firstName . " " . $lastName; 
	?>

</h1><p class="instructions">Expand a project from the Peer or Product Evaluations menu to access your evaluations.</p></div>
</div><!-- end stabs -->

<?php endif ?>  
  <div id="all">               
<div id="leftside">
    <div class="vscroll">
<h3 class="faculty">Projects</h3><h3 class="student">Peer Evaluations</h3>
<?php 

if($testing){
	echo '<div class="faculty">';
	
		//include "profDataLTI.php";
	echo '<div id="facdata"></div></div>';
	
}

	echo '<div class="student">';
	 
	
	echo '<div id="stuData">';
	include "/www/git/canvas/2015/studentData3.php";//new local file. compare with studentData3.php
	echo '</div></div>';


?>

</div>


 
 </div><!-- end leftside -->
 <!-- moved from leftside div -->
 <div id="main">
 <div id="email" class="rounded" style="display:none;margin-top:10px"></div>
    <div >

		<div class="fill"><span id="MainContent_lblSuccess"></span>
        </div>
        <div class="fill errmsg">

            <span id="MainContent_lblError"></span>

        </div>

        

      <div class="fill" ><!-- min-height 10px? -->

                   
<h2 class="center" id="subtitle"></h2>
        

      </div>
    </div>
    <!--end moved -->
 <div id="email2" class="rounded" style="display:none"><a  href="#" class="btn rounded exit" onclick='$("#email2").hide()'>X</a><form name="personalContact"   >
   
<div id="confirmation"></div>
  <p>To: <input name="to" type="text" id="personal-to" size="90" >
   
 
  </p>
  <p>From: <?php echo $udelnetid ?>@udel.edu
    <input name="from" id="personal-from" type="hidden" value="<?php echo $udelnetid ?>@udel.edu">
  </p>
  <p>cc: <?php echo $udelnetid ?>@udel.edu</p>
   <p>Subject:
    <input name="subject" id="subject" type="text" value="Peer Evaluation" size="40">
  </p> 
<p>  <textarea name="body" id="body" cols="70" rows="5"></textarea>
  </p>

  <p>
    <input type="button" onClick="email2()" value="Send">
    <input type="button" onClick='$("#email2").hide()' value="Cancel">
  </p>
  <input type="hidden" name="fullname" id="fullname" value="<?php echo $firstName . " " . $lastName ?>">
</form></div><!--end email2 -->

 <div id="results" class="rounded" ><h3 class="ui-widget-header ui-corner-top">What&apos;s New</h3>
 
  <h4>Winter 2015 Updates</h4>
 <ul>
 
    <li>A new Analytics button has been added to Summary tab. Click to see available data exports.</li>
    <li>New data export options
    <ol><li>Peer Category Scores by Group - Individual rubric scores appear in separate columns, sorted by group.</li>
    <li>Compliance data (peer only) compare the number of evaluations each student has submitted against the size of his group. </li></ol></li>
    <li>Summary scores and comments can now be pushed directly to the Canvas Gradebook.</li>
    
  </ul>
  <h4>Canvas Integration</h4>
 
 
 <p>Canvas rosters, groups (by category) and outcomes (by group) can be imported directly into the Peer Evaluation tool. To get started, you must request a Canvas course, and then drag the Peer Evaluation link into your course navigation. Visit the Help tab for more information.</p><p>For those who do not use Canvas, Peer Evaluation is still fully functional as a stand-alone system. Sakai integration is not available at this time.</p></div>
<div id="evalview" ></div>
    
<div id="summary" class="rounded faculty" style="display:none"></div>
<div id="dialogs" >
<div id="confirm1" class="dialog">Editing an active project can be problematic. Continue anyway?</div>
<div id="confirm2"></div>
<div id="confirmDelete" >Delete this project?</div>

<div id="prompt1" class="dialog">
<form>
<h3>Peer Evaluation</h3>
<p>Enter total possible points: <input type="number"></p>
</form>
</div>
<div id="prompt3" class="dialog">
<form>
<h3>Product Evaluation</h3>
<p>Enter total possible points: <input type="number"></p>
</form>
</div>
<div id="prompt2" class="dialog">
<form>
<p>Enter URL: <input type="text" size="20"></p>
</form>
</div>
<div id="alert" ></div>
<!--<div id="tooltip"></div>-->

</div> <!--end dialogs --> 


<div id="dialog-category" title="Create Category Score">
<form>
	<fieldset>
		<p><label for="maxpoints">Maximum points:</label>
		<input type="text" name="maxpoints" id="maxpoints" class="text ui-widget-content ui-corner-all" size="5"/></p>
		<div class="formfield">Description:
		<textarea name="catdescription" id="catdescription" value="" class="text ui-widget-content ui-corner-all" cols="57" rows="2" ></textarea>
</div><!-- end formfield -->
	</fieldset>
    </form>
</div><!-- end dialog-category -->

 <div id="edit-category" title="Edit Category Score">
<form>
	<fieldset>
		<p><label for="maxpoints-e">Maximum points:</label>
		<input type="text" name="maxpoints-e" id="maxpoints-e" class="text ui-widget-content ui-corner-all" size="5"/></p>
		<div class="formfield">Description:
		<textarea name="catdescription-e" id="catdescription-e" value="" class="text ui-widget-content ui-corner-all" cols="57" rows="2" ></textarea>
</div><!-- end formfield -->
	</fieldset>
    </form>
</div><!-- end edit-category -->
 
      
<div id="ajax" ></div>
<?php if ($canvas) : ?>
<div id="canvasgroups" data-endpoint="<?php echo $canvasgroups; ?>" >loading...</div>
<div id="canvasrubrics" data-endpoint="<?php echo $canvasrubrics; ?>">loading...</div>
<?php endif ?>


</div>

        </div>

        <div class="clear">

        </div>

    </div>
<?php include "/www/git/canvas/2015/footer2.php" ?>

</body>
<script>
var activetab=0;
/**
 * defaultGroup is a boolean. Always true when called from showOptions. Need to know when it is false! * Called from showOptions(), which passes true
 * Resets default instructions and project type. Puts studentid into the groups field to be submitted next time a new project is initialized.
 */
function clearInstructorForm(defaultGroup){
			$("#submit").val("Create Project");
		$("#projectName").val("");
		$("#projectName-w").val("");
		$("#custominstructions").load("/git/canvas/2015/defaultInstructions.php");
		$("#pcustominstructions").load("/git/canvas/2015/pdefaultInstructions.php");
		//$("#evalForm").children().val("");
		//$("#idlist").text("");
		setProjectType(0);
		$("#includeself").attr("checked",true);
		if(defaultGroup){
			
			$("#pagetitle").text("Project Options");
			$("#idlist").val('<?php echo $studentid ?>');
			$("#groups").find("li").remove();
			getNames(false);
		}
}
function showOptions(){//this has been repurposed - called on cancel of edit or copy
	$(".groupcreation").show();
	$("#evalForm").dialog("destroy");
	//if($("#tabs").hasClass("hidden"))toggleChrome();//if editor is expanded, we need to show the tabs and scroll
	if(activetab==2){//new
		$("#tabs").tabs("select",0);
		var title = $("ul.formlist li.ui-state-active").text();
		$("#pagetitle").text(title);
		setHeight();
		
	}else{
		
		$("#evalForm").detach().appendTo("#wizardview");
	}
	clearInstructorForm(true);
	//showGroup(activegroup,"eval",true);
}
/**
 * loads email3.php into #ajax for sending email from instructor to a single student
*/
function email2(){
	var obj = new Object();
	obj.to = $("#personal-to").val();
	obj.from = $("#personal-from").val();
	obj.fullname = $("#fullname").val();
	obj.body= $("#body").val();
	obj.subject = $("#subject").val();
	$("#ajax").load("/git/canvas/2015/email3.php",obj,function(response,status,xhr){
		    if (response == "error") {
    var msg = "Sorry but there was an error: ";
	
    $("#confirmation").html(msg);
	
  }else{
		$("#confirmation").html(response);
		$("#body").val("");
	}
		});
}//email2

var groupdata = "";
var projectRoster;
/**
 * function alert_dialog
 * replaces built-in alert
 * presents a 'hide this message' option if cookie==true
*/
alert_dialog=function(t,cookie){
	if(cookie && !localStorage[cookie]){
		$dismiss = $("<a>Don't warn me again</a>");
		$dismiss.css("cursor","pointer");
		$dismiss.click(function(){
			localStorage[cookie] = "1";
			$("#alert").dialog("close");
		});
		
		
		$("#alert").html("<p>" + t + "</p>").append($dismiss).dialog("open");
		return false;
	}else if(localStorage[cookie]){
		return true;
	}else{
	
		var d = $("#alert");
		if(d.dialog("isOpen")){
			d.append("<hr>" + t);
		}else{
		d.html(t).dialog("open");
		}
	}
}
/**
 * used in the context of the mini-editor for customizing instructions. 
 * creates an html anchor
*/
function createLink()
{
	var selectedtext="";
	if(!window.getSelection){//older IE
		var sText = document.selection.createRange();
		selectedtext = sText.text;
	}else{
		selectedtext = window.getSelection();
	}
	if(selectedtext == ""){
		 alert("please select some text to be used as a hyperlink");
	}else{
		range = saveSelection();
		$("#prompt2").dialog("open");
/*		if (url){
			document.execCommand("createLink", false, url);
			$("div.custom a").removeAttr("style");
			$("div.custom a").attr("target","_new");
		}*/
	}
}//createlink
function storeCustom(op){
	if(op=="put"){
		var arr_str = "";
		$("#results div.custom input").each(function(){
			arr_str += $(this).val() + ",";
		});
		return arr_str;
	}else{
		var arr = op.split(",");
		$("#results div.custom input").each(function(n){
			$(this).val(arr[n]);
		});
	}
}//storecustom

var trashme = null;
/**
 * toggles the two column view when we need full screen for help.
 * Called from tabs.select
 */
function hideMain(bool){
	
	if(bool){
		$(".vscroll, #subtitle,#results").hide();
		
		//$(".formlist li.ui-state-active a").click();
		
		//showGroup(activegroup,"eval",false);hopefully this closes the active group
	}else{
		$(".vscroll, #subtitle,#results").show();
	}
	
	
}//hideMain
/**
 * Loads an existing project into the project creation UI if n is set. Otherwise launches the Wizard. When called from a project's 'Edit' option, deleteOrig is false and we overwrite. When called from duplicate a new project record is created.
 */
function showInstructorForm(n,deleteOrig,confirmed){
	
	if(n)var ref = "#eval" + n;
	if(deleteOrig && !confirmed){
		trashme=n;
		$("#confirm1").dialog("open");
		return;
	
	}else if(!deleteOrig){
		if(n)$("#projectName").val($(ref).text() + " Copy");
		trashme = null;
		
	}
	$("#email").hide();
	//$("#optionsview, #exportview").addClass("ui-tabs-hide");
	$("#rostertools").hide();
	$("input[value='use as roster']").show();
	$('.temp').html('<p class="instructions"><b>Temporary Drag Location</b>.<br/>Drag groups or members here to facilitate re-grouping.</p><ul><input type="button" value="create as group" id="sag" onclick="temptogrp()" style="display:none"></ul>').hide();
	$("#groups").html("").removeClass("roster");
	
	if(n){//this is an edit or copy. no wizard
		//$(".wizard").hide();
		
		//var radioToCheck = "#project-type_" + $(ref).attr("data-type");
		
		if($(ref).attr("data-self")=="0"){
			$("#includeself").removeAttr("checked");
		}else{
			$("#includeself").attr("checked",true);
		}
		if($(ref).hasClass("archived")){
			$("#active").removeAttr("checked");
		}else{
			$("#active").attr("checked",true);
		}
		var postdata = new Object();
		postdata.survey=n;
		postdata.copy=true;
		postdata.id=8000;
		postdata.emplid = '<?php echo $_SESSION['cas_data']['EMPLID'] ?>';
		
		$("#custominstructions").load("/git/canvas/2015/studentResults7.php",postdata,function(){//show person as evaluatee to get peer instructions

		
				$("#evalForm").detach().appendTo("#evalview").show();
/*				$("#leftside").addClass("hidden");
				fillWindow("#evalForm",.95);
				fillWindow("#groups",.9);*/
				toggleChrome();
				$("#wizard").hide();
			if(deleteOrig){
				$("#pagetitle").text("Edit Project: " + $(ref).text());
				$("input[name=cancel_create]").attr("value","Cancel Edit");
			}else{
				$("input[name=cancel_create]").attr("value","Cancel Project");
				$("#pagetitle").text("Copy Project: " + $(ref).text());
			}
			//alert($(ref).nextUntil("li[id^='eval']").length);
			var allgroups="";
			$(ref).nextUntil("li[id^='eval']").each(function(i,e){
				//var collection = new Array();
				var newgroup = "<ul>";
				$(this).children("li:not('.label')").each(function(m){
					newgroup+="<li>";
					newgroup += $(this).text().split(" (")[0] + " | ";
					newgroup += $(this).attr("id") + " | ";
					newgroup += $(this).attr("data-email");
					newgroup += "</li>";
				})
				newgroup += "</ul>";
				newgroup += '<input type="button" onclick="removeGroup(this)"  value="delete group" ><br>'
				allgroups+=newgroup;	
				
					/*collection.push($(this).attr('id'));
					});
					$("#idlist").val(collection.join(" "));
					
					if(collection.length)getNames() ;//setTimeout(getNames,10,false,true);//;*/
			});
			$("#groups").html(allgroups);
			$("#idlist").val("");//for some reason there was stray stuff in here
			setProjectType($(ref).attr("data-type"));
			var postdata=new Object();
			postdata.survey=n;
			postdata.copy="true";
			postdata.id=1;
			postdata.emplid = '<?php echo $_SESSION['cas_data']['EMPLID'] ?>';
			if(($(ref).attr("data-type"))!=0)$("#pcustominstructions").load("/git/canvas/2015/studentResults7.php",postdata);//show product as evaluatee to get product instructions
			enableDrag();
		});
	

	//}else if (<?php echo $studentid ?>=="10914"){
		//its me, testing the student roster
		
	}else{//blank
	$("input[name=cancel_create]").attr("value","Cancel Project");
		$("#evalForm").detach().appendTo("#wizardview").show();
		
/*		$("#submit").val("Create Project");
		$("#projectName").val("");
		$("#projectName-w").val("");
		$("#custominstructions").load("defaultInstructions.php");
		$("#pcustominstructions").load("pdefaultInstructions.php");
		//$("#evalForm").children().val("");
		//$("#idlist").text("");
		setProjectType(0);
		$("#includeself").attr("checked",true);
		$("#pagetitle").text("Create New Project");*/
		if($("#groups ul").length==0){
			$("#idlist").val('<?php echo $studentid ?>');
			getNames(false);
		}
		
		launchWizard();
		
	}
	//projectRoster = new Array();
			//$("#pagetitle").html("");
		$("#subtitle").text("");
	$("#evalForm").show();
	$("ul.formlist ul li.ui-state-highlight").removeClass("ui-state-highlight");
	$("#results").html("").hide();
	$("#email2").hide();
	
	//

	
}//show instructor form
/**
 * does exactly what the name says 
 */
function getProjectType(){
	return $("input[name=project-type]:checked").val();
}
/**
 * roster is a boolean. when true, list in #groups represents the entire roster
 * groupname is a string, used only when the project accepts product evals. 
 * access ldap to obtain missing identifiers, then create the individual group lists with draggable members       * depends on getNames3.php
 * calls 
    * setProjectType
    * enableDrag
	*/
function getNames(roster,groupname){
	

	var ta = $("#idlist");
	var lower = ta.val().toLowerCase();
	var regEx = new RegExp("(@udel.edu)|(,)|(;)",'g');
	var cleanlist = lower.replace(regEx," ");
	var ta_arr = cleanlist.split(/\s+/);
	if(ta_arr[ta_arr.length-1]=="")ta_arr.pop();
	var initlength= ta_arr.length;
	groupdata = $("#groups").val();
	
	if(ta.val().length==0 && !roster){
		
		$("#groups").append('<ul  ></ul><input type="button" align="right" onclick="removeGroup(this)" value="delete group" ><br>');
		
		setProjectType();
		//enableDrag();
		return;
	}
	
	
	//regEx = new RegExp(",",'g');
	//cleanlist = cleanlist.replace(regEx,"");
	var data = new Object();
	data.idlist=cleanlist;
	
	$("#ajax").load("/git/canvas/2015/getNames3.php",data,function(response, status, xhr) {
		 if (response.indexOf("LDAP error")>-1) {
			 //alert(response);
			  
			var msg = "Sorry, but there was an error. Please check your roster IDs and try again. Do not submit both UDIDs and UDelNetIDs in a single submission. You may mix UDelNetIDs and email addresses.";
			alert_dialog(msg);
			return;
		 }
		var content = $("#ajax div").html();
		content= content.replace("<BR>","<br>");
		
		var group_array = content.split("<br>");
		group_array.sort();
/*		if(group_array.length==1){
			alert($("#ajax div").html())
			group_array = $("#ajax div").html().split("<BR>");
		}*/
			
		var dups = new Array();
		var errormsg = "";
		if(group_array.length < initlength && group_array.length>0){
			var invalids = "The following student ids are invalid:";
			for(var t = 0;t<initlength;t++){
				var mem = ta_arr[t];
				
				if(content.indexOf(mem)==-1 && mem.length>1)invalids+= "<br>" + ta_arr[t];
			}
			errormsg += invalids + "<br>";
		}
		for(var i=0;i<group_array.length;i++){
			var member = group_array[i];
			if($("#groups").html().indexOf(member)>1){
				dups.push(i);
				
				
				errormsg += "Duplicate student, " + member + " has been removed from this group.<br>";
			}//close if member
		}	// close member loop
		if(dups.length || group_array.length < initlength){
			alert_dialog(errormsg);
			for(i=dups.length-1;i>-1;i--){
				var dup = dups[i];
				group_array.splice(dup,1);
			}//close for
		}//close if dups
		
		
		
		  if (response.indexOf("LDAP error")>-1) {
			  
			//var msg = "Sorry but there was an error. Please check your roster IDs and try again.";
			//alert_dialog(msg);
			//$("#MainContent_lblError").text(msg);
		  }else{//close if response
		  
			if(group_array.length>0){
				
				if(roster){
					//$("#groups").addClass('roster');
					//response = '<ul class="roster"><li>' + group_array.join("</li><li>") + "</li></ul>";
					$('.temp ul:first').addClass("troster").prepend('<li>' + group_array.join("</li><li>") + "</li>");
					$('.temp').show().children('p').hide();
					//$("#groups").prepend(response);
					$("input[value='use as roster']").hide();
					$("input[value='create group'],#groupsb p.group").show();
					//$("#groupcreation").hide();
					$("#rostertools").show();
					//var reopen="<a href=\"#\" onclick='$(\".groupcreation\").show();$(this).remove();' class=\"rounded btn\">Create New Group</a>";
					//$(".groupcreation").hide();//.before(reopen);
				}else{
				var lbl = "<li>";
				if(groupname && getProjectType)lbl = '<li class="label"><input type="text" value="' + groupname +'" ></li><li>';	
				var	response = '<ul>' + lbl + group_array.join("</li><li>") + "</li></ul>";

						$("#groups").append(response + '<input type="button" onclick="removeGroup(this)"  value="delete group" ><br>');
					
				}
				
			}//close if group
			  ta.val("");
			  ta.text("");
			 // if(final)compileAndSubmit();//alert("Final group created. Please click submit again.");
		  }//close else
		  //$('ul.roster li').sort(sortAlpha).appendTo('ul.roster');
		 
		 setProjectType();
		 
		 enableDrag();
		 // do it here
	});//close load and success
	
		  
}//getnames
var activegroup=null;
function randomGroups(){
	var groupSize=$("#groupSize").val();
	if(isNaN(groupSize) || groupSize==""){
		alert_dialog("Please enter a group size");
		return;
	}
 	var rg = "<ul>";
	if(groupSize==1 && $("input[name=project-type]:checked").val()!="0"){//use student name as group name
		$('ul.troster li').each(function(n) {
            if(n>0 && n % groupSize==0 ) rg += '</ul><input type="button" onclick="removeGroup(this)"  value="delete group" ><br><ul>';
			var name = $(this).text().split(" | ")[0];
			rg += '<li class="label"><input type="text" value="' + name + '"/>';
			rg += '<li>' + $(this).text() + '</li>';
        });
	}else{
		$('ul.troster li').sort(sortRandom).appendTo('ul.troster');

//var numGroups =Math.floor( $('ul.roster li').length/groupSize);

	var gnum=$('ul.troster').length;
/*	if($("input[name=project-type]:checked").val()!="0"){
					
					rg += '<li class="label"><input type="text" value="Product 1"/></li>';
				}*/
		$('ul.troster li').each(function(n){
			if (n==0 && $("input[name=project-type]:checked").val()!="0"){//add product name for first random group
				var name = "Product " + gnum++;
				rg += '<li class="label"><input type="text" value="' + name + '"/></li>';
			}else if(n>0 && n % groupSize==0 ) {//close previous group, and start new one
				rg += '</ul><input type="button" onclick="removeGroup(this)"  value="delete group" ><br><ul>';
				if($("input[name=project-type]:checked").val()!="0"){
					var name = "Product " + gnum++;
					rg += '<li class="label"><input type="text" value="' + name + '"/></li>';
				}
				//rg += '</ul><input type="button" onclick="removeGroup(this)"  value="delete group" ><br><ul>';
			
			}
				
			
			rg += '<li>' + $(this).text() + '</li>';
			});
			rg += '</ul><input type="button" onclick="removeGroup(this)"  value="delete group" ><br>';
		
}
	$('#groups').append(rg);
	$('ul.troster li').remove();
	
	 enableDrag();								
			
}//randomgroups

/**
as named
 * loads profData5.php
 * calls testAs("faculty")
*/
function deleteProject(n){
	showGroup(activegroup,"eval",false);
	//$("#eval"+n).nextUntil("li[id^='eval']").remove();
	//$("#eval"+n).remove();
	//$("#evalForm").hide();
	
	var obj = new Object();
	obj.id=n;
	obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';
	$("#results").load("/git/canvas/2015/profData5.php",obj,function(){
		activegroup=null;														   
		//window.location.reload();
		testAs("faculty");
		});
	//$("#pagetitle").html("");
}//deleteproject
function toggleProject(n,b){

	var obj = new Object();
	obj.active=b;
	obj.id=n;
	obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';
	$("#results").load("/git/canvas/2015/profData5.php",obj,function(){
		showGroup(activegroup,"eval",false);
		testAs("faculty");
		});
}//toggleproject

function removeGroup(b){
	$(b).prev("ul").remove();
	$(b).next("br").remove();
	$(b).remove();
	enableDrag();
}//removegroup

var tallycomplete=false;
function showGroup(n,prefix,reopen){
	
	$("#results, #email").hide();
	var msg = saveUnsubmitted();
	var opengroup = "#" + $(".formlist").find("li.ui-state-active").attr("id");
	
	$("ul.formlist ul li.ui-state-highlight").removeClass("ui-state-highlight");
	
	$(opengroup).removeClass("ui-state-active");
	//$("#MainContent_lblError").text(msg);
	var ref = "#" + prefix + n;
	
	//$(ref).next("ul").children("li").attr("data-proj",n);
	//$("#reopen").html("");
	if(opengroup == ref  ){//closing
		//$("ul.formlist li").removeClass("ui-state-active");
		//$("ul.formlist ul li").removeClass("ui-state-highlight");
		//alert(opengroup + "," +ref);
		
		var pt = prefix == "eval" ? "Faculty View" : "Peer Evaluation: <?php echo  $firstName . " " . $lastName ?>";
		var title = $("#pagetitle").html(pt);
		if(prefix!="eval"){
			$("#results").html("<p>Expand a project from the Peer or Product Evaluations menu to access your evaluations.</p>").prepend(title);
			$(opengroup).next("ul").hide();//hide group names
			$("#email2").hide();
			
		}else{
			$("#email").hide();
			$("#optionsview").html('<p class="instructions">No project selected. Choose the <b>Create New Project</b> tab, or select one of your existing projects.</p>').removeClass("btns");
			$("#email").hide();
			$(opengroup).nextUntil("li[id^=eval]").hide();

			$("#tabs").tabs("disable",1);
		}
		activegroup=null;
		//return;
	}else{//opening
		
		$("#" + prefix + n).addClass("ui-state-active");
		var title = $("#pagetitle").html($(ref).text());
		
		if(prefix!="eval"){
			$(ref).next("ul").show();
			$(opengroup).next("ul").hide();//hide group names
			if(prefix=="stu"){
				$("#studentview").addClass("btns").html('<a id="mygroup" class="email ui-corner-all" href="#" onclick="emailGroup('+n+')">Email Group Members</a>').prepend(title);
			}else{
				$("#mygroup").remove();
			}
		}else{
			$(opengroup).nextUntil("li[id^=eval]").hide();
			$(ref).nextUntil("li[id^=eval]").show();
			//faculty member is expanding a project
			if(tallycomplete){
				
				//do nothing, this is done
			}else{
				
				$("li.waiting").each(function(index, element) {
						var count = $(this).parent().children("li").length-1;
						
						if($(this).parent().prevAll("li['data-self']").attr("data-self")=="0")count--;
						if($(this).parent().prevAll("li['data-type']").attr("data-type")>"0")count--;
					if($(this).attr("data-email")=="presentation"){
						var proj = $(this).parent().prevAll("li['data-self']:first");
						
						var countall = proj.nextUntil("li").find("li:not([data-email=presentation]").length;
						count += proj.nextUntil("li").find("li.label").length;
						count = countall - count;
						
					}
					if(count<=0)count++;//first group has no product
					var n = Number($(this).attr("data-complete"));
					var p = 10*(n/count);
					var w = Math.round(p) + "px";
					$(this).append(" (" +n +"/"+count+")");
					$(this).before('<span class="waiting" style="width: ' + w + '"></span>');
				});
				tallycomplete=true;
			}

/*			$("#email").html('<div id="email_acc"><h3><a href="#top">Email Project Members</a></h3><div><iframe  src="email2.php?projectID='+n+'&from=<?php echo $udelnetid ?>@udel.edu&fullname=<?php echo $firstName . ' ' . $lastName ?>&projName='+$(ref).text() +'" height="350" width="90%" frameborder="0" marginheight="0" marginwidth="0"></iframe></div><h3><a href="#top">Project Summary</a></h3><div><iframe onload="emailAccordion()" src="summaryTable9.php?proj='+n+'&facid=<?php echo $facid ?>" height="640" width="100%" frameborder="0" marginheight="0" marginwidth="0"></iframe></div></div>');

$("#email").html('<h3 class="ui-widget-header ui-corner-top">Email Project Members</h3><iframe  src="/peer/2014/email2_1.php?projectID='+n+'&from=<?php echo $udelnetid ?>@udel.edu&fullname=<?php echo $firstName . ' ' . $lastName ?>&projName='+$(ref).text() +'&redirect=/peer/2014/student.php" height="350" width="96%" frameborder="0" marginheight="0" marginwidth="0"></iframe>').hide();*/
$("#email").html('<h3 class="ui-widget-header ui-corner-top">Email Project Members</h3><div id="delay" data-get="projectID='+n+'&from=<?php echo $udelnetid ?>@udel.edu&fullname=<?php echo $firstName . ' ' . $lastName ?>&projName='+$(ref).text() +'&redirect=/peer/student.php" class="errmsg">Loading your email groups. This may take a second or two.</div>').hide();

$("#summary").html('<h3 class="ui-widget-header ui-corner-top">Project Summary</h3><iframe  src="/git/canvas/2015/summaryTable9.php?proj='+n+'&facid=<?php echo $facid ?>" height="640" width="96%" frameborder="0" marginheight="0" marginwidth="0"></iframe>');

			//$("#email").show();
		}
		
		
		activegroup=n;
	}
	//$(ref).nextAll("ul").hide();
	
	
	
	if(prefix == "eval"){
		
		$("#email2").hide();
		if(activegroup != null){
		$("#results").html('<h3 class="ui-widget-header ui-corner-top">Project Options</h3><p>Select a student or product to view evaluations.</p>').show();	
		$("#optionsview").html('<a class="email" href="#" onclick="populateEmail()">Notify</a><a href="#" class="pencil2" onclick="showInstructorForm('+n+',true)">Edit</a><a href="#" class="copy" onclick="showInstructorForm('+n+')">Copy</a><a href="#" class="delete" onclick="activeGroup='+n+';$(\'#confirmDelete\').dialog(\'open\')">Delete</a>').addClass("btns");
		$("#exportview").html('<a href="#" onclick="showGroups('+n+')" class="groups" >Show Groups</a><a class="grades" href="#" onclick="exportOptions('+n+',\'<?php echo $facid ?>\')">Export Grades</a><a class="summary" href="#" onclick="exportAnalytics('+n+',\'<?php echo $facid ?>\')">Analytics</a><a href="/git/canvas/2015/summaryTable9.php?proj=' + n + '&disposition=download&facid=<?php echo $facid ?>" class="summary" >Export Summary</a>');
		$("#tabs").tabs("enable",1);
		
			if($(ref).hasClass("archived")){
				$('<span id="ptoggle"><a class="activate" href="#" onclick="toggleProject('+n+',1)">Activate</a></span>').prependTo("#optionsview");
			}else{
				$('<span id="ptoggle"><a  class="deactivate" href="#" onclick="toggleProject('+n+',0)">Deactivate</a></span>').prependTo("#optionsview");
			}
			if($(ref).attr("data-released")=="1"){
				$('<a id="release_btn" class="waiting" href="#" onclick="releaseGrades(0)">Retract Grades</a>').prependTo("#optionsview");
				
			}else{
				$('<a id="release_btn" class="complete" href="#" onclick="submitAllSummaries()">Release Grades</a>').prependTo("#optionsview");
			}
		}else{
			$("#tabs").tabs("select",0);
			$("#tabs").tabs("disable",1);
			$("#results").html('<h3 class="ui-widget-header ui-corner-top">Project Options</h3><p>Select a project to view student groups.</p>').show();
		
		}
		$("#optionsview").prepend(title);
		$("#optionsview a").addClass("ui-corner-all");
		
	}else{
		
		
	}
	$("#evalForm").hide();
	$("#subtitle").text("");
	//if(reopen)showGroup(n,"eval");
}//showgroup
function setProjectType(type,radio){
	if(type==undefined)type = Number($("input[name=project-type]:checked").val());
	if(radio){//radio button has been changed
		var named = type;
	if(type>0 && <?php echo $canvas ?>){
		//we don't really recommend the product evaluation within the context of Canvas
		alert_dialog("Canvas offers product evaluation that is in many ways superior to what the UD tool can offer. For that reason, peer-only is the recommended choice for Canvas users.","cpe2");
		}
		
	}else{
		
		named=type;
		var radioToCheck = "#project-type_" + type;
		$(radioToCheck).attr("checked",true);
	}
	
		
	
	$("#groups ul li:contains('| presentation')").each(function(n){
		//convert from reloaded project
			var gname = $(this).text().split(" | ")[0];
			$(this).addClass("label").html('<input type="text" value="'+gname+'"/>');
			});
	
	if(named ==0){//no presentations
		$(".presentation").hide();
		$("h3.peer").show();
		$("#groups ul li.label").remove();
	}else if(named==1){//no peer evaluations
		$(".peer").hide();
		$("h3.presentation").show();
	
	
	}else{//both
	//only do this next part if the project is new
	//code moved to setProjectType
		
		$("h3.presentation, h3.peer").show();
		
	}
	if(named>0){
		$("#groups ul:not('.troster')").each(function(n) {
            if($(this).find("li.label").length==0){//do we want a label in an empty project?
				//$(this).children("li:first-child").not(".label").before('<li class="label"><input type="text" value="Product ' + (n) + '"/></li>');
				$(this).prepend('<li class="label"><input type="text" value="Product ' + (n) + '"/></li>');
			}
        });
	}
	$("#groups ul li.label").eq(0).remove();
	enableDrag();
}
function personalEmail(addr){
	$("#personal-to").val(addr);
	$("#confirmation").html("");
	$("#email2").toggle();
				
			
}//personalemail

/**
reformat #groups ul lists to send off to the database.
         * returns a string representing groups for storage, delimited by "" and replacing the product inputs with a dummy student name in the | delimited data.
         * called by compileAndSubmit
		 */
function formatGroups(){
		var groups="";
	$("#groups ul:contains(|)").each(function(g){
		
		if($(this).find("input").length){
			groups += $(this).find("input").val() + " | " + g + " | presentation<br>";
		}
		
		$(this).children("li:contains(|)").each(function(m){
			var grouplen = $(this).parent().children("li:contains(|)").length -1;								 
			groups += $(this).text();
			if(m< grouplen) groups += "<br>";								 
											 });
		
		if( g< $("#groups ul:contains(|)").length -1) groups += "<nextgroup>";
	});

	return groups;
}//formatgroups
var maxgrade;//total score is calculated by validate instructions
var maxgrade2;//

/**
validate and submit instructor form to profData5.php
         * on success calls        
         * clearInstructorForm
         * testAs("faculty")
*/
function compileAndSubmit(){
	if($("#projectName").val()=="")alert_dialog("Your project must have a name");
	
	var projectType = $("input[name=project-type]:checked").val();
	if(projectType>0){
		var tempbool=false;
		$("#groups ul").each(function(index, element) {
            if($(this).children("li:not('.label')").length==0){
				alert_dialog("Groups containing only products must be deleted.");
				tempbool=true;
				return;
			}
        });
	}
	if(tempbool)return;
	if(projectType!=1 && !validateInstructions("peer"))return;
	if(projectType!=0 && !validateInstructions("product"))return;
	
	if($("#idlist").val().length){
		alert_dialog("Please create or delete your final group.");
		//getNames(true);
		
		return;
	}
	temptogrp();

	var obj = new Object();
	obj.maxscore=maxgrade;
	obj.roster = formatGroups();
	obj.custom = $("#custominstructions").html();
	obj.instructorID = $("#instructorID").val();
	obj.projectName = $("#projectName").val();
	obj.surveyid = trashme;
	obj.startdate = 0;//$("#startdate").val();
	obj.enddate = 0; //$("#enddate").val();
	obj.active = $("#active:checked").length;
	obj.includeself = $("#includeself:checked").length;
		obj.projecttype = $("input[name=project-type]:checked").val();
	obj.maxscore2=maxgrade2;
	obj.custom2=$("#pcustominstructions").html();
	var all ="";
	for(var a in obj){
		all += a + ":" + obj[a];
	}
	//alert(all);
	if(obj.projectName && obj.roster){
		obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';
	$("#results").load("/git/canvas/2015/profData5.php",obj,function(){
		//$("#projectName, #idlist").removeClass("ui-state-highlight");
		//$("#evalForm").hide();
		//if(trashme) deleteProject(trashme);
		//window.location.reload();
		$("#tabs").tabs("select",0);
		$("#evalForm").dialog("destroy");
		clearInstructorForm(false);//don't create default group, since this will happen in testAs
		testAs("faculty");
		});
	}else{
	
		if(!obj.projectName)$("#projectName").addClass("ui-state-highlight").focus();
		if(!obj.roster){
			$("#idlist").addClass("ui-state-highlight");
			alert_dialog("Please create at least one group before submitting");
		}
	}
}//compileandsubmit
var currentRole;
var ta = false;
function testAs(s){
	
	currentRole=s;
	
	if(s=="student" || ta){//student is entering as a ta,
	if(activegroup)showGroup(activegroup,"eval",false);
	if(ta) currentRole="faculty";
		ta = false;
		$("#email").hide();
		//$("#summaryResults").append("<p>Click on a project name in the left side-panel to evaluate your group's members</p><p>Click on a project name above to view feedback you have received</p>");
		activegroup=null;
		$(".student").show();
		var obj = new Object();
		obj.emplid="<?php echo $_SESSION['cas_data']['EMPLID'] ?>";//check against itself in showGrades2.php
		$("#summaryResults").load("/git/canvas/2015/showGrades2.php",obj);
		$("#stuData").load("/git/canvas/2015/studentData3.php",obj,function(){
			$("#pagetitle").html("Peer Evaluation: <?php echo  $firstName . " " . $lastName ?>").prependTo($("#studentview"));
			initList();
		});
		$(".faculty").hide();
		$("#evalForm").hide();
		$("#email").hide();
		//$("#summaryResults").show();
		
		$("#results").hide();
	}else{
		if(activegroup)showGroup(activegroup,"stu",false);
		$(".student").hide();
		$(".faculty").show();
		$("#results").show();
		//$("#summaryResults").hide();
		$("#pagetitle").html("Faculty View");
		$("#evalForm").hide();
		tallycomplete=false;
		var obj = new Object();
		obj.facid='<?php echo $facid ?>';
		obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';
		$("#facdata").load("/git/canvas/2015/profData5.php",obj,function(){
			initList();
		});
	}
	
		
		if($("#results").text()=="") $("#results").text("Welcome");
	$("#email2").hide();
	$("#email").hide();
	$("#summary").hide();
}//testas
var testing = '<?php echo $testing?>';
/**
clears content from "#MainContent_lblError, #MainContent_lblSuccess"
*/
function clearMessages(){
	$("#MainContent_lblError, #MainContent_lblSuccess").html("");
}//clearmessages
function submitMe(btn){
	$("#alert").dialog("close");
	var totscore = 0;
	var maxscore=0;
	var validates=true;
		var obj = new Object();
		obj['custom']="";
	$(btn).parent().find("input").each(function(){
		if($(this).attr("type")!= "button"){
			var name = $(this).attr("name");
			var value = $(this).val();
			
			if(!name || name=="undefined" || name=="nosubscores"){//this is an unnamed category field
				obj['custom'] += $(this).val() + ",";
			
				if((isNaN(value) || Number(value)<0 || value=="" || value > Number($(this).attr("max")))&& $(this).css("display")!= "none" ){//
									$(this).addClass("ui-state-highlight");
					validates = false;
				}else{
					$(this).removeClass("ui-state-highlight");
				}
				totscore+=Number(value);
				maxscore += Number($(this).attr("max"));
			}else if(name=="grade"){
					
				if($(btn).parent().find("input[name='nosubscores']").length==0){
				//there are category scores, so correct the math
					$(this).val(totscore);
				}
				
				
				
				obj[name]=Number($(this).val());
			
			}else{//not a grade
				obj[name]=value;
			}
			
		}
	
		
		obj['comments'] = $(btn).parent().find("textarea").val();
		});
			if(!validates){
			alert_dialog("Please enter a score for each highlighted field. Scores may not exceed the value indicated by parentheses.");
			$("input.ui-state-highlight").get(0).focus();
			return;
		}
		
		var testnum =  Number($("input[name=grade]").val());
				var testbool = $.isNumeric(testnum);
				obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';

	if(testbool  && obj.grade>=0 && obj.grade<=maxscore){$("#ajax").load("/git/canvas/2015/submitEval.php",obj,function(a,status,c){
		$("input[name=grade]").removeClass("ui-state-highlight");
		if(a == "Thank you"){//I don't know how to set status to failure
			//alert($("#ajax").html());
			$(btn).val("update");
			var prefix =  currentRole == "faculty" ? "#eval" : obj.evaluatee < 2000 ? "#prod" :"#stu";

			var survey = prefix + obj['surveyid'];
		
			var sdone = obj['evaluatee'];
			$(survey).nextUntil("li").find("li#"+obj['evaluatee']).removeClass("pending").removeClass("waiting").addClass("complete").prev("span.waiting").hide();
			if($("#results p.ui-state-highlight").length){
				$("#results p.ui-state-highlight").hide().text("Your submission has been updated").fadeIn(500);
			}else{
				$("#results").prepend('<p align="center" class="ui-state-highlight">'+ a +'. Your submission has been recorded. You may modify this evaluation, select another evaluation, or log out.</p>');
			}
			
			/*$("#results").prepend('<p align="center" class="ui-state-highlight">Thank-you</p>');
			$(survey).next("ul").find("li").each(function(){
				
				if($(this).attr("id")==obj['evaluatee']){
					$(this).toggleClass("pending").addClass("complete");
				}
				});*/
		}else{
			alert_dialog('There has been a problem with your submission. Please <a href="https://sites.google.com/a/udel.edu/peer/features-for-testing/2014s-bugs" target="_blank"> report this bug.</a>');
		}
	});
	}else{
		
		alert_dialog("Please enter a score between 0 and "+maxscore);
		$("input[name=grade]").addClass("ui-state-highlight");
	}
    
	
}// submitme
/**
 * function
 * sets up drag/drop for group members
 * establishes temporary drag/drop location
 * calls
  * setProjectType() to check whether inputs need to be added
  */
function enableDrag(){
	
	if($("ul.troster li").length==0){//move this block into enableDrag? 
						$("ul.troster").removeClass("troster").parent("div.temp").children().show();
						$("#rostertools").hide();
					}
	var roster = $(".temp ul:first").hasClass("troster");
	$("#groups ul:first li:contains('<?php echo $emplid ?>')").addClass("instructor").parent().next("input[type=button]").hide();
	//$("#groups ul:first li:first").addClass("instructor").parent().next("input[type=button]").hide();//this should always be the professor
		
				$( "#groups ul li, .temp ul li" ).not(".label").not(".instructor").draggable({
			//connectToSortable: "#groups ul",
			helper: "clone",
			revert:false,
			revertDuration:0,
			stack:"#groups ul",
			scope:'me',
			scroll:false
			
		});
		$("#groups ul,.temp ul").droppable({
			scope:'me',
			drop: function( event, ui ) {
				
			
				$( this ).append(ui.draggable);
				
					if($("div.temp ul:first").children("li").length>0 && !roster){
						$("#sag").show();
					}else{
						
						$("#sag").hide();
					}
				setProjectType();//need to add product?
			}
		});
		

		//$( "#groups ul, #groups li" ).disableSelection();
		if($("#groups h3").length == 0) $("#groups").prepend("<h3>Groups</h3>");
		
		if($("#groups").get(0).scrollHeight > $("#groups").height() + 5){
			
			
			$(".temp").show();
			$("p.instructions.group").height(20).css("overflow","hidden");
			if(!roster){		//-----------drag groups only if roster is hidden
				$( "#groups ul" ).draggable({
					//connectToSortable: "#groups ul",
					helper: "clone",
					revert:"invalid",
					revertDuration:0,
					stack:"#groups",
					scope:'metoo',
					scroll:true,
					start:function(){
						
						$("#tempgroup").click();
					}
					
				});
				
				$(".temp").droppable({
					scope:'metoo',
					drop: function( event, ui ) {
						ui.draggable.next("input[type=button]").addClass("hidden").hide();
						
					
						$( this ).append(ui.draggable);
						var self=$(ui.draggable);
						ui.draggable.append('<input id="tempgroup" type="button" value="Done" style="float:right">');
						
						$("#tempgroup").click(function(e) {
							self.insertBefore("#groups input.hidden");
							$("#groups input.hidden").removeClass("hidden").show();
							e.target.remove();
						});
					
					}
				});
			}
/*			$("#groups ul").click(function(e) {
                $(this.addClass("pinned");
            });*/
			
		}else if($(".temp ul li").length==0){
			$(".temp").hide();
			$("p.instructions.group").height("auto");
		
		}
		//$("#groups input:first").hide();//don't let users delete the first group
}//fn enabledrag
var cookieNotification = "";
function saveUnsubmitted(){//called when any list item is clicked
	clearMessages();
	var activeitem = $("ul.formlist ul li.ui-state-highlight");
	//if(activeitem.hasData()){
	var oldData = activeitem.data("grade") + activeitem.data("comment") + activeitem.data("custom");
						 // }else{
							//  var oldData="";
						 // }
	var cc = storeCustom("put");
			var currentData = $("input[name=grade]:first").val() + $("textarea[name=comments]:first").val() + cc;
			//alert(oldData+  "?" + currentData);
		if(currentData != oldData && activeitem.length>0){
		
			var msg = "saving unsubmitted form data for " +$(activeitem).text() +". this data will be lost if you log out or refresh your browser";
			
			activeitem.data("grade",$("input[name=grade]:first").val());
			activeitem.data("comment",$("textarea[name=comments]:first").val());
			activeitem.data("custom",storeCustom("put"));
			//alert(activeitem.data("comment"));
		}else{
			//activeitem.removeData();
			var msg="";
		}
		
			
			//$("#MainContent_lblError").text(msg);
		
		return msg;
}//saveUnsubmitted

 function wysiwyg (style,btn) {
	if(!document.getSelection){
		var state = document.selection.createRange().text;
	}else{
		var state = document.getSelection();
	}
	
	if(state==""){
	$(btn).toggleClass("ui-state-active");
	$("#custominstructions").focus();
		
	}

	document.execCommand (style, false, null);

	
}//wysiwyg
var range;
function saveSelection() {
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            return sel.getRangeAt(0);
        }
    } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
    }
    return null;
}//saveselection

function restoreSelection() {
    if (range) {
        if (window.getSelection) {
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (document.selection && range.select) {
            range.select();
        }
    }
}//restoreselection

/**
* function
 * calls jQueryUI.accordion on #email_acc
 * I honestly don't know why there is an email accordion!
 */
function emailAccordion(){
	$( "#email_acc" ).accordion({
	
		collapsible: false,
		autoHeight:false
		});
}//fn emailaccordion

/**
 * function
 * simply sums the values of the rubric elements and populates the total score
 * total score updates every time a rubric input is edited
 */
function compileFromCustom(obj){
	
	var ts = 0;
	var subform = $(obj.target).parents(".custom").find("input");
	var ctotal = $(obj.target).parents("form").find("input[name=grade]");
	
	$(subform).each(function(index, element){
	//$(".custom input").not(".custominstructions .custom input").each(function(index, element) {
        ts += Number($(this).val());
    });
	$(ctotal).val(ts);
}//fn complileFromCustom
/*function showStatus(e,t){
	
	$("#tooltip").css({"top":(e.clientY-10)+ "px","left":e.clientX + "px"}).text(t).show().hide(2000);
}//fn showstatus*/
	function sortRandom(a,b){  
		return Math.random()>.5 ? 1 : -1;  
	};
	function sortAlpha(a,b){  
    return a.innerHTML.toLowerCase() > b.innerHTML.toLowerCase() ? 1 : -1;  
};
$(document).ready(function(e) {
	 $("#catdescription,#catdescription-e,.custom p").bind('paste', function(e) {
		  
            if(!alert_dialog('Warning: do not paste MS Word copy or html into your rubric. Disable this warning, and paste again if you are sure you are pasting plain text only.','paste')){
			e.preventDefault();
			}//bypass warning if cookie is set
			
        });
	$(".step").find("p:eq(0)").append(' <button> <a href="/git/canvas/2015/help3.php" title="open help in new window" target="help"> ? </a></button>');
	jQuery.fn.sort = function() {  
  	 return this.pushStack( [].sort.apply( this, arguments ), []);  
 	};  
  


	var lastUpdate = 0;
		$( "#tabs" ).tabs({
			select: function(event, ui) {
				console.log("active tab:" + ui.index);
			if(ui.index==7){
					window.location='/peer/index.php?logout=true';
					return;
				}
				activetab=ui.index;
				var hidemenu = (ui.index==2 || ui.index>=5) ? true : false;
				hideMain(hidemenu);
				if(ui.index==2){//launch wizard
			
					$("#summary").hide();
					
					showInstructorForm();
				
				
				}else if(ui.index==5){//student grades
				testAs("student");
					if($("#accordion3").length){
						$("#summaryintro").text("Click on a panel title below to view your feedback/grades.");
						$("#pagetitle").html("Peer Evaluation Results for <?php echo  $firstName . " " . $lastName ?>").detach().prependTo(ui.panel);
						$("#summaryResults").show();

					}
				}else if(ui.index==3){//student view
					
						$(".faculty").hide();
						//if(activegroup)showGroup(activegroup,"eval",false);
						testAs("student");
						
					
				}else if(ui.index==4){//exit student view
				$("#results").html("");
					//$('#pagetitle').text("Faculty View");
					//$(".student").hide();
					testAs("faculty");
				}else if(ui.index==1){//summary
					$(".faculty").show();
					$("#pagetitle").detach().prependTo(ui.panel);
					$("#email").hide();
					$("#summary").show();
					$("#results,#subtitle").hide();
					$("h3.ui-state-active").click();
				}else if(ui.index==0){//options 
					
					$(".faculty,#results,#subtitle").show();
					$("#summary, #email").hide();
					$("#pagetitle").detach().prependTo(ui.panel);
					if($("#results").text()=="") $("#results").hide();
				}else if(ui.index==6){//fac help
					$("#summary, #email").hide();
				}
				
				},
		ajaxOptions: {
			error: function( xhr, status, index, anchor ) {
				$( anchor.hash ).html(
					"Couldn't load this tab. We'll try to fix this as soon as possible. " +
					"If this wouldn't be a demo." );
			}
		}
	});//faculty tabes
		$( "#tabs" ).tabs("disable",1);
	$( "#stabs" ).tabs({
		select: function(event, ui) {
				if(ui.index==3){
					window.location='/peer/index.php?logout=true';
					return;
				}
			if(ui.index>=1 ){
				hideMain(true);
/*						$("#results").hide();
				if($("#accordion3").length){
					$("#summaryview").html("<p class='instructions' align='center'>Click on a panel title below to view your feedback/grades.</p>");
					$("#pagetitle").html("Peer Evaluation Results for <?php echo  $firstName . " " . $lastName ?>").detach().prependTo(ui.panel);
					$("#summaryResults").show();

				}else{
					//$("#summaryview").html("<p class='instructions' align='center'>No instructor evaluations have been submitted</p>");
					//$("#summaryResults").hide();
					
				}*/
			}else{
				hideMain(false);
				$("#pagetitle").html("Peer Evaluation: <?php echo  $firstName . " " . $lastName ?>").detach().prependTo(ui.panel);
			}
			
			},
		ajaxOptions: {
			error: function( xhr, status, index, anchor ) {
				$( anchor.hash ).html(
					"Couldn't load this tab. Please report this problem to bkinney@udel.edu" );
			}
		}
	});//stabs
    var checkInterval = setInterval(function(){
       if(new Date().getTime() - lastUpdate > 840000){
           clearInterval(checkInterval);
		   
       }else{   
            $.get('/peer/ping.php');
       }
    }, 840000); // 14 mins * 60 * 1000

   $(document).keydown(function(){
         lastUpdate = new Date().getTime();
    });
	 /*$(document).mousemove(function(){
         lastUpdate = new Date().getTime();
		 
    });
	$(".pencil").live("click",function(e){
		currentSubscore=$(e.target).parent();
		$("#maxpoints-e").val($(e.target).next("input").attr("max"));
		$("#catdescription-e").val($(e.target).parent().text().split("): ")[1]);
		$("#edit-category").dialog("open");
	});*/
	$("#custominstructions,#pcustominstructions").delegate("p[contenteditable=false]","click",function(e){
		if($(e.target).hasClass("instructions")){
			//alert_dialog("To edit general instructions, simply place your cursor within the existing text and enter changes.<br><br>To edit a subscore, click on it's pencil icon. <br><br>The formatting tools in the gray toolbar may be used to enhance general instructions. The formatting tools can not be used on subscore description text.");
			return;
		}else if($(e.target).hasClass("pencil") || $(e.target).attr("type")=="number"){
			currentSubscore=$(e.target).parent();
		}else{
			currentSubscore=$(e.target);
		}
		
		$("#maxpoints-e").val(currentSubscore.children("input").attr("max"));
		$("#catdescription-e").val(currentSubscore.text().split("): ")[1]);
		$("#edit-category").dialog("open");
	});
	$.extend($.ui.dialog.prototype.options, {
   position:["center",60]
});

	$("#exportoptions").dialog({
		position:["center",60],
		autoOpen:false,
		modal:true,
		width:450
		
		
	});
	$( "#confirmDelete" ).dialog({
		position:["center",60],
		resizable: false,
		height:140,
		modal: true,
		autoOpen:false,
		buttons: {
			"Delete": function() {
				
				deleteProject(activegroup);
				
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				
				
				$( this ).dialog( "close" );
			}
		}
		
	});

	$( "#confirm1" ).dialog({
		position:["center",60],
		resizable: false,
		height:140,
		modal: true,
		autoOpen:false,
		buttons: {
			"Continue": function() {
				
					var ref = "#eval" + trashme;
				
				//trashme = n;
				$("#submit").val("Update Project");
				$("#projectName").val($(ref).text());
				showInstructorForm(trashme,true,true);
				$( this ).dialog( "close" );
			},
			Cancel: function() {
				trashme=null;
				
				$( this ).dialog( "close" );
			}
		}
		
	});
	$("#alert").dialog({			   
		position:["center",60],
		autoOpen:false
	});

		$("#prompt2").dialog({
		position:["center",60],
		autoOpen:false,
		buttons:{
			"OK":function(){
				var url=$(this).find("input").val();
				restoreSelection()
				document.execCommand("createLink", false, url);
			$("div.custom a").removeAttr("style");
			$("div.custom a").attr("target","_new");
				$(this).dialog("close");
			},
		
			Cancel:function(){
				$(this).dialog("close");
			}
		}
	});
	
	$("#dialog-category").dialog({
		position:["center",60],
		autoOpen: false,
		height: 210,
		width: 550,
		modal: false,
		buttons: {
			"Create": function() {
				var pts = Number($("#maxpoints").val());
				if(pts < 1 || isNaN(pts)){
					$("#maxpoints").addClass("ui-state-highlight");
					return;
				}
				insertInput();
				
				$(this).dialog("close");
			
				
		},
			Cancel: function() {
					clearDialog();
					$( this ).dialog( "close" );
				
			}
		}

	});
	$("#edit-category").dialog({
		position:["center",60],
		autoOpen: false,
		height: 210,
		width: 550,
		modal: true,
		buttons: {
			"Update": function() {
				var pts = Number($("#maxpoints-e").val());
				
				if(pts < 1 || isNaN(pts)){
					$("#maxpoints-e").addClass("ui-state-highlight");
					return;
				}
				updateCurrentSubscore();
				
				$(this).dialog("close");
			
				
		},
			"Delete":function(){
				
				
				updateCurrentSubscore(true);
				$(this).dialog("close");
			},
			Cancel: function() {
					
					$( this ).dialog( "close" );
				
			}
		}

	});

/*	$("#groups").scroll(function(e){
		$("ul.troster").css("margin-top",e.target.scrollTop);
	});*/

	$(".faq ul:first").delegate("li","click",function(){
		$(this).children("ul,ol").toggle();
	});
	//know when data has changed
/*	$("#accordion4 div form").delegate("input","blur",function(){
						msgActive=true;alert(msgActive);
											 
																							});*/
//$('body').bind('click',null,clearMessages);
	//enable accordion for multiple results
	$( "#custom" ).accordion({
	
		collapsible: true,
		autoHeight:false,
		active:false});

//
	//enable datepicker on date fields
	if(testing){
		testAs('faculty' ); 
		$("#tabs").tabs("select",0);
	}else if(<?php echo preg_match("/FACULTY/",$usertype) ?> ){
		currentRole="faculty";
		//toggleHelp();
		$(".student").hide();
		
	}else if(<?php echo preg_match("/STUDENT/",$usertype) ?> ){
		$(".faculty").hide();
		//toggleHelp();
	}
	
	
		
   // $("#start").datepicker({altField:"#startdate",altFormat:"yy-mm-dd",defaultDate:new Date(),selectDefaultDate:true});
	//$("#end").datepicker({altField:"#enddate",altFormat:"yy-mm-dd"});
	$("ul li ul").hide();

	if(!testing)initList();
	$('body').removeClass("hidden");
	setHeight();
	$(window).resize(function(){
		setHeight();
	});
	
});//end ready
function fillWindow(selector,p,minh){
	console.log(selector + " top= " + $(selector).offset().top);
	console.log(selector + " ptop= " + $(selector).position().top);

	var correction =$(selector).offset().top +  $("#footer:visible").outerHeight() + $(selector).outerHeight()-$(selector).height();
	var h = ($(window).height() - correction)*p;//-35
	h=Math.max(h,minh);
	console.log(correction);
		$(selector).css("max-height",h  + "px");
		$(selector).css("height",h  + "px");
	return h;
}
/*this is a workaround since I have not yet been able to get a style that uses all available space, and then drops in a scrollbar. at this point, all this does is call fillWindow on appropriate page elements */
function setHeight(wizard){
	
/*	var correction = 0;
	$(".header:visible,#footer:visible").each(function(){
		correction += $(this).height();
	});
	
	//correction +=25;
	//set max size of a tab
	//$("#tabs,#stabs").css("max-height",($(window).height() - correction)+"px");
	//$("#fachelp,#results").css("overflow","auto");
	//$(".fullheight").css("height",($(window).height() - correction)+"px")
	//$("#fachelp iframe.fullheight").css("height",($(window).height() - correction -5)+"px");
	
	if(full){
		correction=0;
	}else{
		correction += $("#tabs").height();
	}*/
	//$(".vscroll,#main").css("height",($(window).height() - correction)+"px");
	//if(full){
	
		//fillWindow("#tabs",1);
		if(wizard){
			console.log("wizard");
		//if($("#tabs").tabs("option","selected")==6){//choose something to force the interface to fill the screen
			fillWindow("#evalForm",.99,0);
			
		}else{
			fillWindow("#evalForm",.99,0);
			fillWindow(".vscroll",1,0);
		}
		fillWindow("#groups",.97,450);
	//}
	
	/*correction = $("#groups").offset().top;
	$("#groups").css("max-height",($(window).height() - correction)+"px");
	$("#groups").css("height",($(window).height() - correction)+"px");*/
}
/* launches #evalForm in a dialog, called fillWindow so that we take best advantage of available window height called whenever a project edit is initiated, and on exit wizard
*/
function toggleChrome(){
	//fillWindow("#evalForm",.98);
	
	$("#evalForm").dialog({
		width:"98%",
		modal:true,
		position:"top",
		height:$(window).height()-20,
		close: function() {
				showOptions();
				
			}
	});
	
	fillWindow("#groups",.95,450);
}

 function nextStep(){
	 $("div.step:visible").hide().next("div.step").show();
	 
 }
 function prevStep(){
	 $("div.step:visible").hide().prev("div.step").show();
 }
 var currentEditor;
 function insertInput(){// called on line 265v
 
 
 
 
 /*note to self- use currentEditor.find(input[name=nosubscores] instead of #nosubscores throughout */
 
currentEditor =$("div.toolset:visible").parent("div"); 
 
 
 currentEditor.find("input[name=nosubscores]").remove();//now there is a subscore!
 var pts = $("#maxpoints").val();
 var desc = $("#catdescription").val();
currentEditor.find("div.custom").append('<p contenteditable="false"><a class="pencil"></a><input type="number" required max="' + pts + '"> (' + pts + '): ' + desc + '</p>');
clearDialog();
	//insertTextAtCursor
	//$("#custom").get(0).insertAdjacentHTML('beforeEnd','<input type="text" size="3">');
}
var currentSubscore;
var represssubmit = false;
function updateCurrentSubscore(remove){
	var form = currentSubscore.parent("div.custom");
	if(remove){
	currentSubscore.remove();	
	var p = form.parent("div").attr("id");
	represssubmit=true;

		if(p=="custominstructions"){
			validateInstructions("peer"); 
		}else if(p=="pcustominstructions"){
			validateInstructions("product")
		}
				
		
		
	}else{
		form.find("[name=nosubscores]").remove();
	 var pts = $("#maxpoints-e").val();
 var desc = $("#catdescription-e").val();
 
currentSubscore.html('<a class="pencil"></a><input type="number" required max="' + pts + '"> (' + pts + '): ' + desc);
	}
}//fn prevstep/nextstep


function clearDialog(){
	$("#dialog-category").find("input,textarea").val("");
}//fn cleardialog
/**
*Called from showInstructorForm() when 'Create New' tab is selected. Launches the UI Wizard which only appears for new projects. * The function initializes a bunch of UI dialogs, with names beginning with 'step'. * initializes form elements to default values
*/
function launchWizard(){
	
	$("#step1").dialog({
		autoOpen:true,
		position:["center",60],
		height:400,
		width:800,
		modal:true,
		buttons:{
			"Next Step":function(){
				$("#step1a").dialog("open");
				
				$(this).dialog("close");
			},
					"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close");
				
				
			},
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},

	

	});
	$("#step1a").dialog({
		autoOpen:false,
		position:["center",60],
		height:400,
		width:800,
		modal:true,
		buttons:{
			"Next Step":function(){
				$("#step2").dialog("open");
				
				$(this).dialog("close");
			},
					"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close");
				
				
			},
			
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},
			open: function() {
				
					$("#selectProjectType").detach().appendTo("#step1a");
				
			},
			close: function(){
				
				switch($("input[name=project-type]:checked").val()){
					case "peer":
					$(".presentation").hide();
					break;
					case "presentation":
					$(".peer").hide();
					break;
				}
				
				$("#selectProjectType").detach().appendTo("#ptplaceholder");
			}
		
	});
	
		$("#step2").dialog({
		autoOpen:false,
		position:["center",60],
		height:400,
		width:800,
		modal:true,
		buttons:{
			"Back":function(){
				$("#step1").dialog("open");
				$(this).dialog("close");
				
			},
			
			"Next Step":function(){
				var type = $("input[name=project-type]:checked").val();
				if(type != '1'){
					$("#step3").dialog("open");
				}else{
					$("#step3a").dialog("open");
				}
				$(this).dialog("close");
				
			},
			"Exit Wizard": function() {
				toggleChrome();
				
				$(this).dialog("close");
			},
			
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},


	});
		$("#step3").dialog({
		autoOpen:false,
		position:["center","top"],
		dialogClass:"step",
		width:800,
		height:500,
		position:['center','top'], 
		modal:true,
		buttons:{
			"Back":function(){
				
				$("#step2").dialog("open");
				$(this).dialog("close");
				
			},
			"Next Step":function(){
				var type = $("input[name=project-type]:checked").val();
				if(type=='2'){
					$("#step3a").dialog("open");
				}else{
				$("#groupoptions").dialog("open");
				}
				$(this).dialog("close");
				
				
			},
				"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close" );
				
			},
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},
		
			open: function(){
				$("#editor").detach().appendTo("#step3");
			}

	});
	$("#step3a").dialog({
		autoOpen:false,
		position:["center","top"],
		dialogClass:"step",
		width:800,
		height:500,
		position:['center','top'], 
		modal:true,
		buttons:{
			"Back":function(){
				var type = $("input[name=project-type]:checked").val();
				if(type=='2'){
					$("#step3").dialog("open");
				}else{
					$("#step2").dialog("open");
				}
				$(this).dialog("close");
				
			},
			"Next Step":function(){
				
				
				$("#groupoptions").dialog("open");
				
				$(this).dialog("close");
				
				
			},
				"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close" );
				
			},
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},
			
			open: function(){
				$("#peditor").detach().appendTo("#step3a");
			}

	});
		$("#groupoptions").dialog({
		autoOpen:false,
		position:["center",60],
		width:500,
		modal:true,
		buttons:{
			"Group by Group":function(){
				$("#groupcreation").detach().prependTo("#groupsa");
				$("#step4a").dialog("open");
				$(this).dialog("close");
				
			},
			"Entire Roster":function(){
				$("#groupcreation").detach().prependTo("#groupsb");
				$("#step4b").dialog("open");
				$(this).dialog("close");
				
			},
		
				"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close" );
				
			},
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
			
				
				
			
		}

	});
	$("#step4a, #step4b").dialog({
		autoOpen:false,
		position:["center","top"],
		width:850,
		height:500,
		modal:true,
		buttons:{

			"Create Project":function(){
				compileAndSubmit();
				$(this).dialog("close");
			},
				"Exit Wizard": function() {
				toggleChrome();
				$( this ).dialog( "close" );
				
			},
			"Cancel Project":function(){
				showOptions();
				$( this ).dialog( "close" );
			}
		},
		close: function() {
				$(".groupcreation").show();
				$("#groupcreation").detach().prependTo("#groupshome");
			}
		

	});	
	//setHeight();
//toggleChrome(true);
}// end launch wizard	
$("#prompt1").dialog({
	position:["center",60],
	autoOpen:false,
	modal:true,
	buttons:{
		"OK":function(){
			var pts=$(this).find("input").val();
			$("#custominstructions div.custom").append('<input type="number" name="nosubscores" style="display:none" max="'+pts+'">');
			$(this).dialog("close");
			maxgrade=pts;
			if(represssubmit){// launched from editor
				represssubmit=false;
			}else{
				compileAndSubmit();
			}
		},
	
		Cancel:function(){
			$(this).dialog("close");
			
		}
	}
});//prompt1 dialog
$("#canvasgroups, #canvasrubrics").dialog({
	autoOpen:false,
	modal:false,
	position:["center",60]
});
$("#prompt3").dialog({
	position:["center",60],
	autoOpen:false,
	modal:true,
	buttons:{
		"OK":function(){
			var pts=$(this).find("input").val();
			$("#pcustominstructions div.custom").append('<input type="number" name="nosubscores" style="display:none" max="'+pts+'">');
			$(this).dialog("close");
			maxgrade2=pts;
			if(represssubmit){// launched from editor
				represssubmit=false;
			}else{
				compileAndSubmit();
			}
		},
	
		Cancel:function(){
			$(this).dialog("close");
			
		}
	}
});//prompt1 dialog	
	
function validateInstructions(type){
	//$("input[name=nosubscores]").show();
	//new - change nosubscores inputs to number
	if(type=="peer"){//need peer validated
		if($("#custominstructions").find("input[type=number]").length==0){
			
			$("#prompt1").dialog("open");
			return false;
		}else{//validated, so go ahead with whatever
		maxgrade=0; 
			$("#custominstructions").find("input[type=number]").each(function(index, element) {
				
				maxgrade+=Number($(this).attr("max"));
			});
			return true;
		}	
	}else if(type=="product"){//need product validated
		if($("#pcustominstructions").find("input[type=number]").length==0){
			
			$("#prompt3").dialog("open");
			/*  dialog will add a dummy input to the form if no others exist */
			return false;
		}else{//validated, so go ahead with whatever
		maxgrade2=0;
			$("#pcustominstructions").find("input[type=number]").each(function(index, element) {
				maxgrade2+=Number($(this).attr("max"));
			});
			return true;
		}	
	}
}	
	
function showGroups(n){ 
params  = 'width='+screen.width*.9;
 params += ', height='+screen.height*.9;
 params += ', top=0, left=0';

 params += ', scrollbars=1';
 params += ', resizable=1';
	window.open("/git/canvas/2015/studentGroups.php?proj="+n,"popup",params,false);
}//fun validateinstructions
////////////	
function submitAllSummaries(){
	//var list = $("#facdata ul.formlist ul:visible li.waiting:visible").text().split(")");
	//list.pop();
	var list = new Array();
	$("#facdata ul.formlist ul:visible").each(function(index){
		if(index>0){
			
			$(this).find("li.waiting").each(function(index){
				list.push($(this).text());
			});
		}
	});
	//alert(list);
	if(list.length==0){
		releaseGrades(1);
		
	}else{//make sure they want to accept the automatically generated summaries
		$("#confirm2").html("<p>This action will submit unapproved summaries for each of the following " + (list.length) + " students:</p><ul><li>" + list.join(")</li><li>") + ")</li></ul><p>To review, edit and approve summaries prior to release, click on a student name in the project menu list on the left.</p><p>Choose Approve and Release to accept all default summaries.</p>").dialog({
			width:500,
			position:["center",60],
			buttons:{
				"Approve and Release":function(){
				releaseGrades(1);	
		
		
		$(this).dialog("close");
					},
				
				"Cancel":function(){$(this).dialog("close");}
			}
		});
	}
	
}//fn submitallsummaries
function releaseGrades(tiny){
	a=activegroup;
	var pd = new Object();
	pd.submitAll="true";
	pd.type="eva";
	pd.evaluator = "<?php echo $facid ?>";
	pd.survey=activegroup;
	pd.status=tiny;
	pd.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';
	//var datastr = "submitAll=true&type=eva&evaluator=<?php echo $facid ?>&emplid=<?php echo $facid ?>&survey="+activegroup + "&status=" + tiny;
	$("#results").load("/git/canvas/2015/studentResults7.php",pd,function(){
		var obj = new Object();
		obj.emplid='<?php echo $_SESSION['cas_data']['EMPLID']; ?>';//refresh the projects menu
					$("#facdata").load("/git/canvas/2015/profData5.php",obj,function(){//profData4s
			//initList();
			testAs("faculty");
			if(tiny){
				alert_dialog("Your grades have been released. Please be sure to notify your students that their feedback is ready for review.");
			}else{
				alert_dialog("Student grades for this project are no longer accessible within this system, however, any grades you may have posted in other locations, such as Sakai, must be removed separately. Your students are now free to submit or update their individual peer evaluations.");
			}
			//activegroup=null;
			//showGroup(a,"eval");
			
			if(tiny){populateEmail();$("#email").show();}
			
			
		});
			
			
		});
		
}// fn releasegrades
function initList(){//moved this out of ready because for some reason, it was broken there
//move product evaluations to top of list
$("#facdata ul.formlist ul li[data-email=presentation]").each(function(index, element) {
    
	$(this).parent("ul").find("li.label:first").after(element);
});;

	 
if(activegroup){
	var a = activegroup;
	activegroup=null;//treat as unopened
	showGroup(a,'eval');
	
}
$("ul.formlist ul").delegate("li:not('.label,.archived')","click",function(e){
		if($(this).hasClass('ui-state-highlight')) return;//this is already the active record
		//if($(this).parent().prev("li").hasClass("archived")) return;
		var msg =	saveUnsubmitted();														
		//if(currentRole =="faculty") $("#reopen").html('<a href="#" class="rounded btn" onclick="showOptions()">Project Options</a></div>');
		var id = $(e.target).attr("id");
			$("#email, #evalForm").hide();//management tools also
		$("ul.formlist ul li").removeClass("ui-state-highlight");
		$(this).addClass("ui-state-highlight");															
				var type =  currentRole == "faculty" ? "eva" : "stu";
				
				//var type = $(this).parent().prev("li").attr("id").substr(0,3);
				
		if(testing){
			var roleid = type != "stu" ? "<?php echo $facid ?>" : "<?php echo $studentid ?>";
		}else{
			var roleid = <?php echo $emplid ?>;
		}	
		var stuname = $(e.target).text().split(" (")[0];
		var pd = new Object();//get evaluation submitted for/by evaluatee/evaluator
		pd.id=id;
		pd.survey=activegroup;
		pd.evaluator=roleid;
		pd.name = stuname;
		pd.type = type;
		//var datastr = "id=" + id + "&survey=" + activegroup + "&emplid=" + roleid + "&name=" + stuname + "&type=" + type;
		var persontype = "<?php echo $usertype ?>";
		
		if (type=="eva"){
			if($(e.target).attr('data-email')=="presentation"){
				
				var emaillink=' <span class="btns"><a href="#" class="email ui-corner-all" onclick="emailGroup('+activegroup+')">&nbsp</a></span>';
			}else{
				var emaillink=' <span class="btns"><a href="#" class="email ui-corner-all" onclick="personalEmail(\''+$(e.target).attr("data-email")+'\')">&nbsp;</a></span>';
			}
			
			//var emaillink=' <a c href="#" >E-mail ' + $(e.target).text().split(" ")[0] + '</a>';
			$("#subtitle").text("Compiling results for " + stuname + ". Please wait"); 
			$("body,ul.formlist ul li:not(.label)").css("cursor","wait");
			$("#email2").hide();
			//datastr += "&showall=true&submitAll=false&facid=<?php echo $facid ?>";
		
		}else{//student
		emaillink="";
		//$("#rtitle").text("Your evaluation of " + $(e.target).text());
		}
		pd.emplid='<?php echo $_SESSION['cas_data']['EMPLID'] ?>';
		$("#results").load("/git/canvas/2015/studentResults7.php",pd,function(){$("#results").show();
		$("#subtitle").text("Results for " + stuname);
		 $("body").css("cursor","default");
		 $("ul.formlist ul li:not(.label)").removeAttr("style");
			$("#subtitle").hide();
			if(type=="eva"){
				$("#results").prepend('<h3 id="rtitle" class="ui-widget-header ui-corner-top">Results for ' + stuname +'</h3>');
			}else{
				$("#results").prepend('<h3 id="rtitle" class="ui-widget-header ui-corner-top">Your Evaluation of ' + stuname +'</h3>');
			}
			
			$("#rtitle").append(emaillink);
					var li = $(e.target);
	//$("#MainContent_lblError").text(msg);
		if(jQuery.hasData(e.target)){
			//$("#MainContent_lblError").text("restoring unsubmitted data");
			
			$("input[name=grade]:first").val(li.data("grade"));
			$("textarea[name=comments]:first").val(li.data("comment"));
			storeCustom(li.data("custom"));
			//return;
		}else{
		li.data("grade",$("input[name=grade]:first").val());
			li.data("comment",$("textarea[name=comments]:first").val());
			//alert(storeCustom("put"));
			li.data("custom",storeCustom("put"));
		}
		if(li.hasClass("pending")){
				$("input[value=update]").attr("value","submit");	   
					   }
			$( "#accordion" ).accordion({
	
		collapsible: true,
		autoHeight:false,
		active:false});
				$( "#accordion2" ).accordion({
	
		collapsible: true,
		autoHeight:false,
		active:false}
	
			   );	
			   	
		});
		
});

}//end initList
/**
 * function emailGroup(n)
 * called from student interface, icon next to project name?
 * loads emailgroup.php into an iframe inside #email
*/
function emailGroup(n){
	$("#email").html('<a  href="#" class="btn rounded exit" onclick=\'$("#email").hide()\'>X</a><iframe src="/git/canvas/2015/emailgroup.php?projectID='+n+'" height="300" width="600" frameborder="0" ></iframe>').show();
}
/**
 * function exportOptions(n,f)
 * loads exportSakai6.php into #export options and launches it in a dialog
  * n is surveyid, f is facid. Both are sent in GET
  */
function exportOptions(n,f){

	
	$("#exportoptions").load('/peer/2015/exportSakai6.php?facid=' + f + '&proj=' +n+ '&type=unknown').dialog("open");
}
/**
 * function exportAnalytics(n,f)
 * loads analytics.php into #export options and launches it in a dialog
  * n is surveyid, f is facid. Both are sent in GET. Analytics downloads are now launched separately from grade downloads
  */
function exportAnalytics(n,f){

	
	$("#exportoptions").load('/peer/2015/analytics.php?facid=' + f + '&proj=' +n+ '&type=unknown').dialog("open");
}
function temptogrp(){
	var ng = $("<ul></ul>");
	$(".temp ul:first li").appendTo(ng);
	
	ng.appendTo("#groups");
	$("#groups").append('<input type="button" onclick="removeGroup(this)"  value="delete group" ><br>');
	setProjectType();
	$("#sag").hide();
}
function get2obj(str){
	var arr = str.split("&");
	var obj = new Object();
	for(var prop in arr){
		var g = arr[prop].split("=");
		obj[g[0]]=g[1];
		
	}
	//console.log(obj.length);
	return obj;
}
function populateEmail(){
	$("#email").toggle();
	if($("#delay").hasClass("errmsg")){
		var src=$("#delay").data("get");
		data = src.replace("&amp;","&");
		//data=encodeURIComponent(data);
		console.log(data);
		var obj = get2obj(data);
		obj.emplid = '<?php echo $_SESSION['cas_data']['EMPLID'] ?>';
		console.log(obj);
		$("#delay").load("/git/canvas/2015/email2_1.php",obj,function(){
			$(this).removeClass("errmsg");
			});
	}
}
	
//end fn emailgroup
/////////////////		
/*function addPresentations(){
	var ta = $("#idlist");
	
	var ta_arr = ta.val().split(/\n+/);
	var initlength= ta_arr.length;
	var plist = "<ul>";
	for(var i=0;i<initlength;i++){
		plist += '<li class="label">'+ta_arr[i] + '</li>';
	}
	plist += '</ul><input type="button" align="right" onclick="removeGroup(this)" value="delete group" ><br> ';
	ta.val("");
	$("#groups").append(plist);
	
	enableDrag();
}*/
	
</script>
<?php include_once("/www/analyticstracking.php") ?>
</html>