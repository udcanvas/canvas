<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>Peer Evaluation Help</title>
<style>
body{font-family:Arial, Helvetica, sans-serif}
.faq ul li{
	list-style:none;
	cursor:pointer;

}
.faq ul li ul li{
	list-style:circle;
	cursor:default;
	color:#000;
}
.faq ul li ol li{
	list-style:decimal;
	cursor:default;
	color:#000;
}
.faq ul li p{
	color:#000;
	margin-left:
}

</style>
<style>
#left{width:240px;float:left;padding:5px;height:auto%; border:thick solid #2e6e9e;}
.faq, ul,.ui-widget{padding:0;margin:0}
#main{margin-right:10px;margin-left:280px;}
.faq ul li ul li,.faq ul li ol li{list-style:none; 
font-weight:normal;font-size:90%;}
.faq ul li ol li{list-style:decimal;margin-left:10px}
.faq ul li ul li{margin-left:10px};
#acc div.faq ul{padding-left:6px};

a.button{margin:4px;}
h3.button{width:auto;display:inline-block;padding:8px 15px 8px 15px;margin-right:1em;}
button{padding:7px 15px 7px 15px;font-size:110%;cursor:pointer}
.faq ul.close-others li:first{padding-top:0;margin-top:0;}
.faq ul li{font-weight:bold;cursor:pointer;padding-top:5px;margin-left:8px;}
.ui-widget .faq ul li{margin-left:0}
p.instructions{font-size:12px;background-color:#dfeffc;padding:8px;margin:0;border-top:#2e6e9e solid thin;border-bottom:#2e6e9e solid thin;font-family:Arial, Helvetica, sans-serif;}
#left h3{margin-bottom:2;padding-bottom:0;}
.ui-widget ul{padding-left:16px;font-size:14px;font-family:Arial, Helvetica, sans-serif;margin-top:0;padding-top:0;}
.new ul{
	margin-left:16px;
	padding-left:40px;
}
</style>
<?php if(!isset($_GET['s'])): ?>

<script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
<script src='/peer/js/jquery-ui-1.8.15.custom.min.js'></script>
<link href='//ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/redmond/jquery-ui.css' rel='stylesheet' type='text/css'>
<?php endif ?>

</head>

<body>

<div id="left"><h3>Glossary</h3>
  <p class="instructions">Click on a term to reveal its definition</p>
<div class="faq">
<ul class="close-others">
  
  <li>
   <li>Activate/Deactivate
     <ul>
       <li>Only active projects are visible to students. Inactive projects are available to their faculty owners, and may be re-activated or copied.</li>
       <li>A deactivated project will appear gray in the project menu when viewed by the owner.</li>
      </ul>
   </li>
  
  
  <li>Copied Project
    <ul>
      <li>A copied project initially contains the same student groups, scoring rubric and instructions as the original. Each of these features can be edited before the new project is submitted. Changes made to a copied project have no impact on the original.</li>
      <li><b>Note:</b> an existing project can not be edited once it has been submitted. To change an existing project, copy it, edit the copy, and delete or deactivate the original.</li>
      </ul>
  </li>
  
    <li>Create New Project
    <ul>
      <li>Selecting the Create New Project tab will launch a step-by-step wizard, which will help you create a blank project. If you want to use an existing 
      project as a template, visit the Project Options tab, select the desired project, and click 'Copy' (see Copied Project).</li>
      </ul>
  </li>
  

  <li>Deleted Projects
    <ul>
      <li>A deleted project is gone completely and can not be recovered.</li>
      </ul>
  </li>
  <li>Grade Export
    <ul>
      <li>A text-format compatible with Excel and Sakai. This contains student identifying information along with student grades, however, since these are not final grades for a course, the Peer Evaluation csv files need not be encrypted. After downloading and saving the exported grades, they are ready to be uploaded to Sakai.</li>
      </ul>
  </li>
  <li>Group
    <ul>
      <li>A list of students who work together, and will evaluate one-another. Typically a project will contain more than one group.</li>
      </ul>
  </li>
<li>Instructions/Rubrics
    <ul>
      <li>Text provided to aid students in correctly completing the evaluation form. Instruction may include any number of numeric fields, representing different aspects of student performance.</li>
    </ul>
  </li>
  <li>Peer Evaluations
  <ul><li>Peer Evaluations, as opposed to Product Evaluations, allow students to rate themselves and others WITHIN a collaborative group.</li></ul></li>
   <li>Product Evaluations
  <ul><li>Product Evaluations, as opposed to Peer Evaluations, allow students to rate completed assignments or presentations submitted by OTHER students or groups.</li></ul></li>
  <li>Project
    <ul>
      <li>A peer evaluation project represents a single opportunity for students working together to evaluation one-another. Each project must have at least one group.</li>
    </ul>
  </li>
  
    <li>Project Options
    <ul>
      <li>Open the Project Options tab to select an existing project. Once a project has been selected, you will be able to perform any project-specific tasks.</li>
    </ul>
  </li>
       <li>Project Type
    <ul>
      <li>Beginning in Fall 2013, the ability to collect 'Product' as well as 'Peer' evaluations has been added. Projects can therefore be one of three types: Peer Only, Product Only, or Both. When creating a Project that collects both peer and product evaluations, you will have two rubrics, and students will have two categories of evaluation to complete.</li><li> It is not currently possible to release product evaluations on a different date than peer evaluations within a single Project. If you need separate delete dates, you can create two Projects, one for Peer Only and the other for Product Only. Reusing the same roster and collaborative groups can be accomplished by Copying projects.</li>
    </ul>
  </li>
  <li>Scoring rubric
    <ul>
      <li>A set of numeric fields  to be completed by the student. The total score for a project is the sum of the rubric scores.</li>
      <li>Each field should be labeled to indicate the contribution it represents.</li>
      </ul>
  </li>
  
   <li>Student View
    <ul>
      <li>Instructors can select this tab to view any project as a student with your UDID would see it. Project creators are automatically entered into a student group so that their 
      Student View will include their projects.</li><li>Disabling 
      self-evaluation or deleting your student group will cause a project to be removed from your
       Student View. To re-enable Student View of a project with no self-evaluation, you must add another 'student' to your personal group. A person you
        may add without asking permission is bkinney.</li>
     
      </ul>
  </li>
  
  <li>Summary Export<ul>
  <li>The summary export contains all data submitted for a given project, stored as a comma-delimited spreadsheet.</li></ul></li>
  <li>Summary grades
    <ul>
      <li>Faculty must submit an evaluation form for each student. The default value for each numeric field in the summary evaluation is the average of all student submissions, and the comment for the summary evaluation is a concatenation of all student comments. Prior to submission, the instructor is free to edit all fields.</li>
      <li>The contents of the summary (instructor) evaluation is the only feedback students will receive.</li>
    </ul>
  </li>
  <li>Summary Tab
  	<ul><li>Visit the summary tab to perform end-of-project tasks such as exporting or viewing project summary data or grades. The summary tab also provides an easy
     way to view group membership of very large projects. The summary tab is disabled when no current project is active.</li></ul>
  </li>
  </ul>
</div></div>
<div id="main"><h3>Overview</h3>
<p>The Peer Evaluation tool enables faculty to create an online interface students will use to evaluate themselves and their peers.  </p>
<h3>How to Use this Page</h3>
<p>Every effort has been made to make the Peer Evaluation Tool as easy to use as possible. If you encounter usability issues or bugs, please visit our <a href="https://sites.google.com/a/udel.edu/peer/features-for-testing/2014s-bugs">user feedback page</a> and report the problem.
We will address as many of the reported issues as we can in the next release. In the meantime, use the resources on this page. The glossary on the left should help you understand what the features of the tool are, and how these are
intended to be used. For text-based instructions on the use of an individual feature, consult the <b>How Do I</b> list below. </p>
<div class="new">
  <h3 > Summer 2014 Updates</h3>
  <ul>
    <li>Style changes to increase workspace when creating or editing projects </li>
    <li>Improved perfomance when compiling non-participants email list</li>
    <li>Remove instructor group from list of unsubmitted evaluations when releasing grades</li>
    <li>Fix all known bugs - if you know of an outstanding bug, PLEASE take the time to report it! </li>
  </ul>
</div>
<h3 class="ui-accordion-header ui-state-default ui-corner-all button" ><a href="https://sites.google.com/a/udel.edu/peer/features-for-testing/2014s-bugs" target="google">Report a Problem</a> </h3><h3 class="ui-accordion-header ui-state-default ui-corner-all button" ><a href="/git/canvas/2015/help3.php" target="help">Open Help in new Window</a> </h3><hr>

<div id="acc">

<h3><a>How do I...</a></h3>
<div class="faq"> <p class="instructions">Click on a question to reveal its answer</p>
<?php include "/www/peer/faq_faculty_2013f.php" ?>
</div>
<h3><a>More Help</a></h3>
<div>
  <p>Follow these links for more information on specific topics</p>
  <p><a href="https://docs.google.com/presentation/d/1F_gLATtLsYl3dSwGMohbteLcFdTV2-l4rtjCER4Ambo/pub?start=false&loop=false&delayms=60000" target="media">Canvas Integration</a></p>
  <p> <a href="http://www.youtube.com/embed/HizIyA7kaKc?rel=0" target="media">Create New Project</a>
    
    
  </p>
  <p><a href="/git/canvas/demos/Student Notification/index.html" target="media">Student Notification</a></p>
  <p><a href="/git/canvas/demos/Student View/" target="media">Student View</a></p>
  <p><a href="/git/canvas/demos/Summary Evaluations/" target="media">Summary Evaluations</a> (instructor approved)</p>
  <p><a href="/git/canvas/demos/Wrap-up/" target="media">Wrapping Up</a></p>
  <p><a href="https://docs.google.com/presentation/d/1z1ae-Lnndm3-VzQeUZQUPxUApqX7egGq8B74nc1OT64/pub?start=true&loop=false&delayms=5000" target="media">Promotional Screenshots</a></p>
  <p>&nbsp;</p>
</div>

</div> <!--END ACCORDION-->

</div><!--END MAIN-->

<!-- <script type="text/javascript">window.jQuery || document.write("<script src='//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'>\x3C/script><script src='js/jquery-ui-1.8.15.custom.min.js'>\x3C/script><link href='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/themes/redmond/jquery-ui.css' rel='stylesheet' type='text/css'>")</script>conditionally load js and css -->
<script type="text/javascript">
$(document).ready(function(e) {
	$(".faq ul:first").delegate("li","click",function(obj){
		
		if($(this).parent().hasClass("close-others") && $(this).children().is(':hidden') ){
			
			$(".faq ul:first li").children("ul,p").filter(':visible').toggle();
			
		}
		$(this).children("ul,p").toggle();
		
		
	});
    $(".faq ul:first li").children("ul,p").hide();
	 $( "#acc" ).accordion({
		active:0,
		collapsible: true,
		autoHeight:false
		});
			$("#acc div.faq ul:first").delegate("li","click",function(obj){
		
		if($(this).parent().hasClass("close-others") && $(this).children().is(':hidden') ){
			
			$("#acc div.faq ul li").children().filter(':visible').toggle();
			
		}
		$(this).children().toggle();
		
		
	});
    $("#acc div.faq ul:first li").children().hide();
	if(self==top)$("a[target=help]").parent("h3").hide();
});
</script>


</body>
</html>