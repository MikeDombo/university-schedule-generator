<?php
ob_start();
?>
<html>
	<head>
		<title>Student Schedule Creator</title>
		<link href="http://kp4assets.richmond.edu/images/kp4/static/favicon.ico" rel="shortcut icon"/>
		<link href="http://d2r44v0ubjhg6i.cloudfront.net/images/kp4/apple-touch-icon.png" rel="apple-touch-icon" />
        <link href="http://d2r44v0ubjhg6i.cloudfront.net/images/kp4/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
        <link href="http://d2r44v0ubjhg6i.cloudfront.net/images/kp4/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
        <link href="http://d2r44v0ubjhg6i.cloudfront.net/images/kp4/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css"></link>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.2/jquery.scrollTo.min.js"></script>
		<link href="css/bootstrap-toggle.min.css" rel="stylesheet">
		<script src="js/bootstrap-toggle.min.js"></script>
		<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		  ga('create', 'UA-69105822-1', 'mikedombrowski.com/sched');
		  ga('require', 'linkid');
		  ga('send', 'pageview');
		</script>
		<style>
			.navbar-brand-name > img {
				max-height:70px;
				width:auto;
				padding: 0 15px 0 0;
			}
			
			.navbar {
				min-height: 90px;
				background-color: #4788c6;
			}
			
			.navbar-brand{
				min-height: 90px;
				height:auto;
				max-height: 120px;
			}
			
			.bootstrap-switch .bootstrap-switch-handle-off, .bootstrap-switch .bootstrap-switch-handle-on, .bootstrap-switch .bootstrap-switch-label {
				height:auto;
			}
		</style>
		<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
	</head>
	<body>
		<nav class="navbar navbar-default navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" style="padding: 10 15px;" href="#">
						<div class="navbar-brand-name">
							<img src="http://www.richmond.edu/_KP4_assets/images/kp4/shield.png"/>
							<span style="color:#ffffff">University of Richmond Scheduler</span>
						</div>
					</a>
				</div>
			</div>
		</nav>
		<div class="container-fluid">
			<div class="col-md-12">
				<div class="jumbotron">
					<h1>Welcome to the University of Richmond Scheduler!</h1>
					<p>Use the search below to find courses and then click the&nbsp;<button class="glyphicon glyphicon-plus btn btn-success" style="line-height:1em!important; vertical-align:text-top;"></button>&nbsp;to add the course to your basket.</p>
					<p>Then click "Create Schedule" to generate every possible schedule</p>
					<p><a class="btn btn-primary btn-success btn-lg btn-jumbo-close" role="button">Okay!</a></p>
				</div>
				<div class="page-header" style="margin-top:0px;">
					<h2>Make a Schedule</h2>
				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h1 class="panel-title">Search for Courses</h1>
					</div>
					<div class="panel-body">
						<h5>If searching by course title fails, try field of study and course number, ex CMSC 315</h5>
						<input id="searchField" class="form-control search" name="fields[]" type="text" placeholder="Search by name or subject area and course number"></input>
						<hr width="100%"/>
						<div id="search-results">
						</div>
					</div>
				</div>
			</div>
			<div class= "col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h1 class="panel-title">Selected Courses</h1>
					</div>
					<div class="panel-body">
						<ul class="list-group" id="course-basket">
						<?php
							ob_flush();
							flush();
							$get = false;
							if(isset($_GET["i"])){
								$get = $_GET["i"];
								$get = json_decode($get, true)["allCourses"];
							}
							
							if($get != false){
								foreach($get as $k=>$v){
									$display = $v["Title"];
									if(isset($v["displayTitle"])){
										$display = $v["displayTitle"];
									}
									echo '<li class="list-group-item" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursenum="'.htmlspecialchars($v["CourseNum"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'" data-displaytitle="'.htmlspecialchars($display).'">';
									echo htmlspecialchars($v["FOS"])." ".htmlspecialchars($v["CourseNum"])." | ".htmlspecialchars($display).'<button class="btn btn-danger glyphicon glyphicon-minus btn-remove-course pull-right" type="button" style="line-height: 1!important;" id="basket-remove" data-coursenum="'.htmlspecialchars($v["CourseNum"]).'" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'"></button></li>';
									ob_flush();
									flush();
								}
							}
						?>
						</ul>
						<label for="full-classes" class="control-label">Show Full Sections&nbsp;</label><input type="checkbox" id="full-classes" <?php if(isset($_GET["i"])){if(json_decode($_GET["i"], true)["fullClasses"]){echo "checked";}}else{echo "checked";}?>></input>
						<label for="time-pref" class="control-label">Class Time Preference&nbsp;</label><input type="checkbox" id="time-pref" <?php if(isset($_GET["i"])){if(json_decode($_GET["i"], true)["timePref"]){echo "checked";}}?>></input>
						<br/><br/><button type="submit" class="btn btn-success btn-generate">Create Schedule</button>
					</div>
				</div>
			</div>
		</div>
		
		<div class="container-fluid" style="margin-top:30px;">
			<div class="col-md-12">
				<div class="well well-lg" style="text-align:center;">
					<h4>Made by <a href="http://mikedombrowski.com" style="color:#444444;">Michael Dombrowski</a></h4>
					<h5>Code Available on <a href="https://github.com/md100play/university-schedule-generator" style="color:#444444;">GitHub</a></h5>
					<h5>Feel Free to Contact Me With Issues or Feature Requests at <a href="mailto:michael@mikedombrowski.com" style="color:#444444;">Michael@MikeDombrowski.com&nbsp;<span class="glyphicon glyphicon-envelope" style="vertical-align:top;"></span></a></h5>
				</div>
			</div>
		</div>
	</body>
	
	<div class="hide panel panel-default" id="searchResultTemplate">
		<div class="panel-heading">
			<h1 class="panel-title pull-left" id="title"></h1>
			<button class="btn btn-success glyphicon glyphicon-plus pull-right btn-add-course" type="button" style="line-height: 1!important;" id="button" data-search="true"></button>
			<div class="clearfix"></div>
		</div>
		<div class="panel-body">
		</div>
	</div>
	
	<li class="hide list-group-item" id="addedTemplate"></li>
	<button class="hide btn btn-danger glyphicon glyphicon-minus btn-remove-course pull-right" type="button" style="line-height: 1!important;" id="basket-remove"></button>
	
	<script>
		$(function() {
			$('#time-pref').bootstrapToggle({
				on: 'Morning',
				off: 'Afternoon',
				offstyle: 'warning'
			});
			$('#full-classes').bootstrapToggle({
				on: 'Yes',
				off: 'No',
				offstyle: 'danger',
				onstyle:'success'
			});
		});
		var $defaultSearchResult = $("#searchResultTemplate");
		var $addedTemplate = $("#addedTemplate");
		var $buttonRemoveTemplate = $("#basket-remove");
		
		$("#searchField").autocomplete({source:function(request, response){var loc = request.term; $.getJSON('/sched/richmond/richmondAPI.php?search='+loc+'&callback=?', function(courseData){
		courseData = eval(courseData.response);
		$("#search-results").empty();
		$.each(courseData, function(i,v){
			var $newPanel = $defaultSearchResult.clone();
			var cn;
			if(v["Course Number"] > 99){
				cn = v["Course Number"].substr(0,1)+"00";
			}
			else{
				cn = v["Course Number"];
			}
			 $.getJSON('http://assets.richmond.edu/catalogs/courses.php?orderby=subjnum&archiveYear=2015&term=&catalogtype=ug&paginate=false&subj='+v["FOS"]+'&level='+cn+'&keyword=&callback=?', function(data){
					 data = data.courses;
					 
					 var initial = data.substring(data.indexOf("</span>"+v["FOS"]+" "+v["Course Number"]));
					 var end = initial.substring(0, initial.indexOf('<!--close inner-content-wrap'));
					 var title = end.substring(v["FOS"].length+9+v["Course Number"].length, end.indexOf("</a>"));
					 var descr = end.substring(end.indexOf("Description</div>")+17);
					 descr = descr.substring(0, descr.indexOf("</div>"));
					 var units = end.substring(end.indexOf("Units: ")+7, end.indexOf("</div>"));
					 
					 if(end.indexOf("Prerequisites</div>")>-1){
						 var prereq = end.substring(end.indexOf("Prerequisites</div>")+19);
						 prereq = prereq.substring(0, prereq.indexOf("</div>"));
					 }
					 
					if(!(v["Title"].indexOf("ST:") > -1) && !(v["Title"].indexOf("SP:") > -1) && v["FOS"] != "FYS" && !(v["FOS"] == "HIST" && v["Course Number"] == "199")){
						v["Title"] = title;
					 }
				 
				 if(v["FOS"] == "FYS" && v["description"] != null){
					 title = v["displayTitle"];
					 descr = v["description"];
				 }
				 else if(v["description"] == null){
					 title = v["Title"];
				 }
				 
				 var html = "<h4>"+title+"</h4><p>"+descr+"</p><p>Units: "+units+"</p>";
				 if(prereq != undefined){
					 html = html+"<p>Prerequisites: "+prereq+"</p>";
				 }
				 
				$newPanel.find("#title").text(v["FOS"]+" "+v["Course Number"]+" | "+title);
				$newPanel.find(".panel-body").html(html);
				$newPanel.find("#button").attr("data-fos", v["FOS"]);
				$newPanel.find("#button").attr("data-coursenum", v["Course Number"]);
				$newPanel.find("#button").attr("data-coursename", v["Title"]);
				$newPanel.find("#button").attr("data-displayTitle", v["displayTitle"]);
				
				if(v["Available"] == "false"){
					$newPanel.find("#button").removeClass("btn-success");
					$newPanel.find("#button").removeClass("btn-add-course");
					$newPanel.find("#button").removeClass("glyphicon-plus");
					$newPanel.find("#button").addClass("btn-disable");
					$newPanel.find("#button").text("Course Not Available");
				}
				
				$newPanel.removeClass("hide");
				$newPanel.attr("id", "");
				
				var $list = $("#course-basket").find("li");
				$list.each(function(){
					if($(this).data("fos") == v["FOS"] && $(this).data("coursenum") == v["Course Number"] && $(this).data("coursename") == v["Title"]){
						$newPanel.find("#button").removeClass("glyphicon-plus");
						$newPanel.find("#button").removeClass("btn-success");
						$newPanel.find("#button").removeClass("btn-add-course");
						$newPanel.find("#button").addClass("btn-danger");
						$newPanel.find("#button").addClass("glyphicon-minus");
						$newPanel.find("#button").addClass("btn-remove-course");
					}
				});
				
				$("#search-results").append($newPanel);
			 });
			 
		});
		});},
		select: function(event, ui){
			var index = event.target.id;
			}
		});
		
		$(document).on("click", ".btn-generate", function (e) {
			var $courses = $("#course-basket li");
			var getCourses = new Array();
			var count = 0;
			$courses.each(function(){
				var temp = {CourseNum:$(this).data("coursenum"), FOS:$(this).data("fos"), Title:$(this).data("coursename"), displayTitle:$(this).data("displaytitle")};
				getCourses.push(temp);
				count++;
			});
			getCourses = {allCourses: getCourses, timePref:$("#time-pref").prop('checked'), fullClasses:$("#full-classes").prop('checked')}
			var json = JSON.stringify(getCourses);
			if(count>5){
				window.alert("Trying to generate schedules with this many courses may take a long time, but I will try.  \n\nThe page will appear to be loading until it is finished, so do not refresh the page.  \n\nThe calculation is allowed take up to 5 minutes, if it takes longer, it will fail.");
			}
			console.log(getCourses);
			window.location.assign("/sched/richmond/makeSchedule.php?i="+encodeURIComponent(json));
		});
		
		$(document).on("click", ".btn-jumbo-close", function(){
			$(this).parent().parent().hide();
		});
		
		$(document).on("click", ".btn-remove-course", function (e) {
			var $course = $(e.target);
			if($course.data("fos") == undefined){
				$course = $course.parent();
			}
			
			var fos = $course.data("fos");
			var num = $course.data("coursenum");
			var name = $course.data("coursename");
			
			if($course.data("search")){
				$course.addClass("glyphicon-plus");
				$course.addClass("btn-success");
				$course.addClass("btn-add-course");
				$course.removeClass("btn-danger");
				$course.removeClass("glyphicon-minus");
				$course.removeClass("btn-remove-course");
			}
			else{
				var $list = $("#search-results button");
				$list.each(function(){
					if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
						$(this).addClass("glyphicon-plus");
						$(this).addClass("btn-success");
						$(this).addClass("btn-add-course");
						$(this).removeClass("btn-danger");
						$(this).removeClass("glyphicon-minus");
						$(this).removeClass("btn-remove-course");
					}
				});
			}
			var $list = $("#course-basket").find("li");
			$list.each(function(){
				if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
					$(this).remove();
				}
			});
		});
		
		$(document).on("click", ".btn-add-course", function (e) {
			var $course = $(e.target);
			$course.removeClass("glyphicon-plus");
			$course.removeClass("btn-success");
			$course.removeClass("btn-add-course");
			$course.addClass("btn-danger");
			$course.addClass("glyphicon-minus");
			$course.addClass("btn-remove-course");
			
			var fos = $course.data("fos");
			var num = $course.data("coursenum");
			var name = $course.data("coursename");
			
			if($course.data("displaytitle") != null){
				name = $course.data("displaytitle");
			}
			
			var $add = $addedTemplate.clone();
			var $button = $buttonRemoveTemplate.clone().removeClass("hide")
			
			$add.removeClass("hide");
			$add.attr("id", "");
			$add.text(fos+" "+num+" | "+name);
			$add.append("&nbsp; &nbsp; &nbsp; &nbsp;", $button);
			$add.attr("data-fos", fos);
			$add.attr("data-coursenum", num);
			$add.attr("data-coursename", $course.data("coursename"));
			$add.attr("data-displayTitle", $course.data("displaytitle"));
			
			$("#course-basket").append($add);
		});
	</script>
</html>
<?php
	ob_flush();
	flush();
	ob_end_clean();
?>