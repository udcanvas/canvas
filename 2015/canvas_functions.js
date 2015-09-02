function importRubric(){
	if($("#canvasrubrics").text() != "loading..."){
		$("#canvasrubrics").dialog("open");
		return;
	}
	var endpoint = $("#canvasrubrics").data("endpoint");
	if(endpoint==""){
		alert_dialog("No rubric endpoint specified. Please report this bug using the link at the bottom of the page.");
	}else{
	$("#canvasrubrics").load(endpoint).dialog("open");
	}
}
function maximizeViewport(){
	
	
	window.parent.postMessage('{"subject":"lti.frameResize","height":900}',"https://udel.instructure.com");
}
function launchGroups(){
	if($("#canvasgroups").text() != "loading..."){
		$("#canvasgroups").dialog("open");
		return;
	}
	var endpoint = $("#canvasgroups").data("endpoint");
	if(endpoint==""){
		alert_dialog("No roster endpoint specified. Please report this bug using the link at the bottom of the page.");
	}else{
	$("#canvasgroups").load(endpoint).dialog("open");
	}
	
}
function cloneGroups(json){
	$("#canvasgroups").dialog("close");
		var obj = jQuery.parseJSON(json);
		var groups = obj["groups"];
		
		var names = obj["groupnames"];
		var msg = "";
		for (var i=0;i<groups.length;i++){
			var orig = groups[i].split(' ');
			for(var j=0;j<orig.length;j++){
				if(isNaN(orig[j])){
					msg += orig.splice(j,1) + ", ";
				}
			}
			$("#idlist").val(orig.concat(" "));
			getNames(false,names[i]);
		}
		if(msg != "")alert_dialog("The following invalid members have been removed: " + msg);
	
		$("input.roster").hide();
		
	
}
function loadCanvasRoster(cid){
	if(cid){
		$("#canvasgroups").dialog("close");
		$("#ajax").load("/git/lti/2015/getroster.php","courseid=" + cid,function(response,success,xhr){
			$('.temp ul:first').addClass("troster").prepend(response);
			var oldmembers = $("#groups").html();
			var msg=false;
			$('.temp ul.troster li').each(function(index, element) {
				var member = $(this).text().split(" | ")[2];
				
				if(oldmembers.toLowerCase().indexOf(member.toLowerCase())>1){
					$(this).remove();
					msg=true;
				}
				
			});
			//if(msg)alert_dialog("Duplicate members have been removed");
			$('.temp').show().children('p').hide();
			//$("#groups").prepend(response);
			$("input[value='use as roster'], input[value='use canvas roster']").hide();
			$("input[value='create group'],#groupsb p.group").show();
			//$("#groupcreation").hide();
			$("#rostertools").show();
			setProjectType();
			enableDrag();
			
		
		});
	}
	

}