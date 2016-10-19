<?php
	require_once("config.php");
?>
<html>
	<head>
		<title>Student Schedule Creator</title>
		<meta name="viewport" content="width=device-width, initial-scale=1"/>
		<link href="css/bootstrap-tour-standalone.min.css" rel="stylesheet"/>
		<link rel="stylesheet" href="css/bootstrap.min.css"/>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script src="js/jquery-ui.min.js" type="text/javascript"></script>
		<script src="js/jquery.ui.touch.min.js" type="text/javascript"></script>
		<link href="css/bootstrap-toggle.min.css" rel="stylesheet"/>
		<link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css"/>
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/css/jasny-bootstrap.min.css"/>
		<script src="//cdnjs.cloudflare.com/ajax/libs/jasny-bootstrap/3.1.3/js/jasny-bootstrap.min.js"></script>
		<script src="js/bootstrap-toggle.min.js"></script>
		<script src="js/bootstrap-tour-standalone.min.js"></script>
		<script>
		if(location.hostname != "localhost" && location.hostname != "127.0.0.1"){
			(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
			(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
			m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
			})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

			ga('create', 'UA-4436865-11', 'auto');
			ga('send', 'pageview');
			(function(){
					var t,i,e,n=window,o=document,a=arguments,s="script",r=["config","track","identify","visit","push","call","trackForm","trackClick"],c=function(){var t,i=this;for(i._e=[],t=0;r.length>t;t++)(function(t){i[t]=function(){return i._e.push([t].concat(Array.prototype.slice.call(arguments,0))),i}})(r[t])};for(n._w=n._w||{},t=0;a.length>t;t++)n._w[a[t]]=n[a[t]]=n[a[t]]||new c;i=o.createElement(s),i.async=1,i.src="//static.woopra.com/js/w.js",e=o.getElementsByTagName(s)[0],e.parentNode.insertBefore(i,e)
			})("woopra");

			woopra.config({
				domain: 'mikedombrowski.com'
			});
			woopra.track();
		}
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
			.row{
				margin-left:0px;
				margin-right:0px;
			}
			label{
				font-weight:bold;
			}
		</style>
	</head>
	<body>
		<nav class="navbar navbar-default navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="#">
						<div class="navbar-brand-name">
							<span style="color:#ffffff">Unofficial Richmond Scheduler</span>
						</div>
					</a>
				</div>
				<ul class="nav navbar-nav navbar-right">
					<li><a><button class="btn btn-default glyphicon glyphicon-list" type="button" data-toggle="offcanvas" data-target="#history">&nbsp;Schedule History</button></a></li>
				</ul>
			</div>
		</nav>
		<div class="container-fluid" id="content">
			<nav id="history" class="navmenu navmenu-default navmenu-fixed-left offcanvas">
			</nav>
			<div class="col-md-12">
				<div class="alert alert-info" role="alert">
					<h4 style="font-size:16px;">Courses Updated 10/19/2016</h4>
				</div>
				<div class="jumbotron hide">
					<h1>Welcome to the Unofficial Richmond Scheduler!</h1>
					<h2>Schedules for Spring 2017 Now Available</h2>
					<p>Use the search below to find courses and then click the&nbsp;<button class="glyphicon glyphicon-plus btn btn-success" style="line-height:1em!important; vertical-align:text-top;"></button>&nbsp;to add the course to your basket.</p>
					<p>Then click "Create Schedule" to generate every possible schedule</p>
					<p>Disclaimer: This product has been developed by Michael Dombrowski it is not owned or operated by the University of Richmond. Accuracy cannot be guaranteed, please contact me if you find any inaccuracies.</p>
					<p><a class="btn btn-primary btn-success btn-lg btn-jumbo-close" role="button">Okay!</a></p>
				</div>
				<div class="page-header" style="margin-top:0px;">
					<h2>Make a Schedule</h2>
				</div>
			</div>
			<div class="col-md-8">
				<div class="row">
					<div class="panel panel-default" id="searchDiv">
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
				
				<div class="row hidden-sm hidden-xs">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h1 class="panel-title">Browse Courses</h1>
						</div>
						<div class="panel-body">
							<div id="subj-list">
								<div class="panel panel-default hide" id="subj-list-template">
									<div class="panel-heading collapse-btn">
										<h1 class="panel-title">Accounting (ACCT)</h1>
									</div>
									<div class="panel-collapse panel-body collapse main-body">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="col-md-4">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h1 class="panel-title">Selected Courses</h1>
					</div>
					<div class="panel-body">
						<style>
							#course-basket, #course-basket-required {
								border: 1px solid #eee;
								min-height: 20px;
								list-style-type: none;
							  }
						</style>
						<p>Required Courses</p>
						<ul class="list-group row connectedSortable" id="course-basket-required">
						<?php
							$get = false;
							if(isset($_GET["i"])){
								$get = $_GET["i"];
								$get = json_decode($get, true)["allCourses"];
							}
							
							if($get != false){
								foreach($get as $k=>$v){
									if(!isset($v["requiredCourse"]) || !$v["requiredCourse"]){
										continue;
									}
									$display = $v["Title"];
									if(isset($v["displayTitle"])){
										$display = $v["displayTitle"];
									}
									echo '<li class="list-group-item" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursenum="'.htmlspecialchars($v["CourseNum"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'" data-displaytitle="'.htmlspecialchars($display).'">';
									echo htmlspecialchars($v["FOS"])." ".htmlspecialchars($v["CourseNum"])." | ".htmlspecialchars($display).
									'<button class="btn btn-danger glyphicon glyphicon-minus btn-remove-course pull-right" type="button" style="line-height: 1!important;" data-coursenum="'.
									htmlspecialchars($v["CourseNum"]).'" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'"></button></li>';
								}
							}
						?>
						</ul>
						<p>Optional Courses</p>
						<ul class="list-group row connectedSortable" id="course-basket">
						<?php
							$get = false;
							if(isset($_GET["i"])){
								$get = $_GET["i"];
								$get = json_decode($get, true)["allCourses"];
							}
							
							if($get != false){
								foreach($get as $k=>$v){
									if(isset($v["requiredCourse"]) && $v["requiredCourse"]){
										continue;
									}
									$display = $v["Title"];
									if(isset($v["displayTitle"])){
										$display = $v["displayTitle"];
									}
									echo '<li class="list-group-item" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursenum="'.htmlspecialchars($v["CourseNum"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'" data-displaytitle="'.htmlspecialchars($display).'">';
									echo htmlspecialchars($v["FOS"])." ".htmlspecialchars($v["CourseNum"])." | ".htmlspecialchars($display).
									'<button class="btn btn-danger glyphicon glyphicon-minus btn-remove-course pull-right" type="button" style="line-height: 1!important;" data-coursenum="'.
									htmlspecialchars($v["CourseNum"]).'" data-fos="'.htmlspecialchars($v["FOS"]).'" data-coursename="'.htmlspecialchars($v["Title"]).'"></button></li>';
								}
							}
						?>
						</ul>
						<script>$("#course-basket, #course-basket-required").sortable({connectWith: ".connectedSortable"}).disableSelection();</script>
						<div class="form-inline">
							<div class="form-group">
							<label for="full-classes" class="control-label">Show Sections at Capacity&nbsp;</label><input class="form-control" type="checkbox" id="full-classes" <?php if(isset($_GET["i"])){if(json_decode($_GET["i"], true)["fullClasses"]){echo "checked";}}else{echo "checked";}?>></input>
							</div>
							<div class="form-group">
							<label for="time-pref" class="control-label">Class Time Preference&nbsp;</label><input class="form-control" type="checkbox" id="time-pref" <?php if(isset($_GET["i"])){if(json_decode($_GET["i"], true)["timePref"]){echo "checked";}}?>></input>
							</div>
						</div>
						<div class="form-group">
							<label for="crns" class="control-label">Preregistered Courses</label>
							<input class="form-control" type="text" id="crns" name="crns" placeholder="CRNs of Courses Already Registered" value="<?php if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["preregistered"])){$print = ""; foreach(json_decode($_GET["i"], true)["preregistered"] as $v){$print = $print.$v.", ";} echo substr($print, 0, -2);}?>"></input>
						</div>
						<div class="form-group"><span id="arrow" class="glyphicon glyphicon-chevron-right"></span><a id="adv" onclick="advOptions()">Show Advanced Options</a></div>
						<div id="advanced">
							<div class="form-group">
								<label for="slider-range" class="control-label">Only Allow Courses Between</label>
								<div id="slider-range"></div>
								<span id="restrict-slider"></span>
							</div>
							<div class="form-group">
							</div>
							<div id="block">
								<div class="form-group">
									<div class="form-inline">
										<div class="form-group">
											<label for="block-time-template" class="control-label">Times You Don't Want</label>
										</div>
										<div class="form-group pull-right">
											<button class='form-control btn btn-success glyphicon glyphicon-plus' id="add-block-time"></button>
										</div>
									</div>
								</div>
							</div>
							
							<div class="hide blocked-time" id="block-time-template">
								<hr/>
								<div class="form-group">
									<div class="time-slider"></div>
									<span class="time-display"></span>
								</div>
								<div class="form-inline">
									<div class="form-group">
										<div class="btn-group" data-toggle="buttons">
										  <label class="btn btn-primary">
											<input type="checkbox" name="Su">Su
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="M">Mo
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="T">Tu
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="W">We
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="R">Th
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="F">Fr
										  </label>
										  <label class="btn btn-primary">
											<input type="checkbox" name="S">Sa
										  </label>
										</div>
									</div>
									<div class="form-group">
										<button class='form-control btn btn-danger glyphicon glyphicon-minus btn-remove-block-time'></button>
									</div>
								</div>
							</div>
							
							<div class="panel panel-default hide" id="history-template">
								<div class="panel-heading">
									<h1 class="panel-title"></h1>
									<button class="btn btn-success glyphicon glyphicon-repeat pull-right btn-load-history" type="button" style="line-height: 1!important; margin-top:-22px;"></button>
								</div>
								<div class="panel-body">
								</div>
							</div>
						</div>
						<script>
							$('.btn').button();
							$("#advanced").hide();
							
							if(getCookie("history").length > 0){
								var c = getCookie("history");
								c = JSON.parse(decodeURIComponent(c));
								console.log(c);
								$.each(c, function(i,v){
									var $cloned = $("#history-template").clone().removeClass("hide").removeAttr('id');
									$cloned.find(".panel-title").text(v["schedules"]+" Schedules");
									var courses = "<ul class='list-group'>";
									$.each(v, function(i2, v2){
										if(v2["Title"] != undefined){
											courses = courses+"<li class='list-group-item'>"+v2["Title"].replace(/\+/g, " ")+"</li>";
										}
									});
									courses = courses+"</ul>";
									$cloned.find(".panel-body").html(courses);
									$cloned.find(".btn-load-history").attr('data-history-id', i);
									$("#history").prepend($cloned);
								});
							}
							
							if(getCookie("jumbotron") != "hidden"){
								$(".jumbotron").removeClass("hide");
							}
						
							var tour = new Tour({
								backdrop: true,
								debug: true,
								onEnd: function(tour){
									$("#course-basket .btn-remove-course").trigger("click");
								},
								steps: [
								{
									element: "#searchDiv",
									title: "Search for Courses Here",
									content: "Use the search field to find courses by title or by subject and course number",
									
									onNext: function(tour){
										$(".collapse-btn:eq(1)").trigger("click");
									}
								},
								{
									element: "#subj-list",
									title: "Browse for Courses",
									content: "Click on a subject to see what courses are offered",
									placement: "top",
									onNext: function(tour){
										$(".collapse-btn-subj:eq(0)").trigger("click");
									}
								},
								{
									element: ".collapse-btn-subj:eq(0)",
									title: "View Course Descriptions",
									content: "Click the title to view the course description",
									
								},
								{
									element: ".btn-add-course:eq(0)",
									title: "Add Course to Selected Courses",
									content: "Once you like a course, click the + button to add it to your selected courses",
									onNext: function(tour){
										$(".btn-add-course:eq(0)").trigger("click");
									}
								},
								{
									element: "#course-basket",
									title: "View Selected Courses",
									content: "Click the - button to remove any courses",
									placement: "left"
								},
								{
									element: ".btn-generate",
									title: "Now Generate Schedules!",
									content: "Click 'Create Schedule' to make every possible schedule",
									placement: "left"
								},
								{
									element: "body",
									title: "Tour Complete",
									content: "Go forth and make your perfect schedule!",
									placement: "top"
								}
								]});

								if(!isMobile()){
									tour.init();
									tour.start();
								}
							
							$(document).on("click", ".btn-load-history", function(e){
								var c = getCookie("history");
								c = JSON.parse(decodeURIComponent(c));
								c = c[$(e.target).data('history-id')];
								$.each(c, function(i, v){
									if(v["Title"] != undefined){
										if(v["displayTitle"] != undefined){
											v["displayTitle"] = v["displayTitle"].replace(/\+/g, " ");
										}
										addCourse(v["FOS"], v["CourseNum"], v["Title"].replace(/\+/g, " "), v["displayTitle"]);
									}
								});
							});
							
							function isMobile() {
								var check = false;
								(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
								return check;
							};
							
							function getCookie(cname) {
								var name = cname + "=";
								var ca = document.cookie.split(';');
								for(var i=0; i<ca.length; i++) {
									var c = ca[i];
									while (c.charAt(0)==' ') c = c.substring(1);
									if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
								}
								return "";
							}
							
							function getParameterByName(name, url) {
								if (!url) url = window.location.href;
								name = name.replace(/[\[\]]/g, "\\$&");
								var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
									results = regex.exec(url);
								if (!results) return null;
								if (!results[2]) return '';
								return decodeURIComponent(results[2].replace(/\+/g, " "));
							}
							
							if(getParameterByName('i') != undefined){
								var importFromURL = JSON.parse(getParameterByName('i'))['unwantedTimes'];
								
								$.each(importFromURL, function(){
									var $cloned = $("#block-time-template").clone().removeClass("hide").removeAttr('id');
									var d = new Date();
									var now = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0,0,0);
									
									var time = this["startTime"].split(" ")[0].split(":");
									var ampm = this["startTime"].split(" ")[1];
									d.setHours(parseInt(time[0]) + (ampm=="PM" ? 12 : 0));
									d.setMinutes(parseInt(time[1]) || 0 );
									this["startTime"] = parseInt((d-now)/60000);
									
									time = this["endTime"].split(" ")[0].split(":");
									ampm = this["endTime"].split(" ")[1];
									d.setHours(parseInt(time[0]) + (ampm=="PM" ? 12 : 0));
									d.setMinutes(parseInt(time[1]) || 0 );
									this["endTime"] = parseInt((d-now)/60000);
									
									$cloned.find(".time-slider").slider({
										range: true,
										min: 480,
										max: 1320,
										step:15,
										values:[this["startTime"], this["endTime"]],
										slide: slideTime
									});
									setSpanTime($cloned.find(".time-slider"));
									
									if(this["Su"] != undefined){
										$cloned.find("input[name='Su']").parent().addClass("active");
										$cloned.find("input[name='Su']").prop('checked', true);
									}
									if(this["M"] != undefined){
										$cloned.find("input[name='M']").parent().addClass("active");
										$cloned.find("input[name='M']").prop('checked', true);
									}
									if(this["T"] != undefined){
										$cloned.find("input[name='T']").parent().addClass("active");
										$cloned.find("input[name='T']").prop('checked', true);
									}
									if(this["W"] != undefined){
										$cloned.find("input[name='W']").parent().addClass("active");
										$cloned.find("input[name='W']").prop('checked', true);
									}
									if(this["R"] != undefined){
										$cloned.find("input[name='R']").parent().addClass("active");
										$cloned.find("input[name='R']").prop('checked', true);
									}
									if(this["F"] != undefined){
										$cloned.find("input[name='F']").parent().addClass("active");
										$cloned.find("input[name='F']").prop('checked', true);
									}
									if(this["S"] != undefined){
										$cloned.find("input[name='S']").parent().addClass("active");
										$cloned.find("input[name='S']").prop('checked', true);
									}
									
									$("#block").append($cloned);
								});
							}
							
							$(document).on("click", "#add-block-time", function(){
								var $cloned = $("#block-time-template").clone().removeClass("hide").removeAttr('id');
								$cloned.find(".time-slider").slider({
									range: true,
									min: 480,
									max: 1320,
									step:15,
									values:[480, 600],
									slide: slideTime
								});
								setSpanTime($cloned.find(".time-slider"));
								$("#block").append($cloned);
							});
							
							$(document).on("click", ".btn-remove-block-time", function(){
								$(this).parent().parent().parent().remove();
							});
							
							function advOptions(){
								if(!$('#advanced').is(':visible')){
									$('#advanced').show(500); $('#adv').text('Hide Advanced Options');
									$('#arrow').removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
								}
								else{
									$('#advanced').hide(500); $('#adv').text('Show Advanced Options');
									$('#arrow').removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
								}
							}
							
							$("#slider-range").slider({
								range: true,
								min: 480,
								max: 1320,
								step:15,
								values: [<?php if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["startTime"])){echo (strtotime(json_decode($_GET["i"], true)["startTime"])-strtotime("today"))/60;}else{echo "480";}?>, 
								<?php if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["endTime"])){echo (strtotime(json_decode($_GET["i"], true)["endTime"])-strtotime("today"))/60;}else{echo "1320";}?>],
								slide: slideTime
							});
							setSpanTime($("#slider-range"));
							
							function slideTime(event, ui){
								var val0 = ui.values[0],
								val1 = ui.values[1],
								minutes0 = parseInt(val0 % 60, 10),
								hours0 = parseInt(val0 / 60 % 24, 10),
								minutes1 = parseInt(val1 % 60, 10),
								hours1 = parseInt(val1 / 60 % 24, 10);
									
								startTime = getTime(hours0, minutes0);
								endTime = getTime(hours1, minutes1);
								$(event.target).parent().children().last().text(startTime + ' - ' + endTime);
							}
							
							function setSpanTime($id){
								var val0 = $id.slider("values", 0),
								val1 = $id.slider("values", 1),
								minutes0 = parseInt(val0 % 60, 10),
								hours0 = parseInt(val0 / 60 % 24, 10),
								minutes1 = parseInt(val1 % 60, 10),
								hours1 = parseInt(val1 / 60 % 24, 10);
								
								startTime = getTime(hours0, minutes0);
								endTime = getTime(hours1, minutes1);
								$id.parent().children().last().text(startTime + ' - ' + endTime);
							}
							
							function getTime(hours, minutes) {
								var time = null;
								minutes = minutes + "";
								if (hours < 12) {
									time = "AM";
								}
								else {
									time = "PM";
								}
								if (hours == 0) {
									hours = 12;
								}
								if (hours > 12) {
									hours = hours - 12;
								}
								if (minutes.length == 1) {
									minutes = "0" + minutes;
								}
								return hours + ":" + minutes + " " + time;
							}
						</script>
						<div class="form-group"></div>
						<div class="form-group">
							<button type="submit" class="btn btn-success btn-generate">Create Schedule</button>
						</div>
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
					<p>Disclaimer: This product has been developed by Michael Dombrowski it is not owned or operated by the University of Richmond.  I will always try to have the data be kept up to date and accuate, but I cannot guarantee effectiveness. Please contact me
					if you find any issues.</p>
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
	
	<div class="hide panel panel-default" id="subj-list-template2">
		<div class="panel-heading">
			<h1 class="panel-title pull-left collapse-btn-subj" id="title"></h1>
			<button class="btn btn-success glyphicon glyphicon-plus pull-right btn-add-course" type="button" style="line-height: 1!important;" id="button" data-search="true"></button>
			<div class="clearfix collapse-btn-subj"></div>
		</div>
		<div class="panel-collapse collapse panel-body subj-list-collapse">
		</div>
	</div>
	
	<li class="hide list-group-item" id="addedTemplate"></li>
	<button class="hide btn btn-danger glyphicon glyphicon-minus btn-remove-course pull-right" type="button" style="line-height: 1!important;" id="basket-remove"></button>
	
	<script>
		var crns = "";
		if($("#crns").val() != undefined && $("#crns").val().length > 0){
			crns = $("#crns").val().replace(/[,]+/g, '').replace(/ +(?= )/g,'').split(" ");
		}
		
		var subjects = {"ACCT":"Accounting", "AMST":"American Studies","ANTH":"Anthropology","ARAB":"Arabic",
			"ARTH":"Art History","BIOL":"Biology","BMB":"Biochemistry","BUAD":"Business Administration",
			"CHEM":"Chemistry","CHIN":"Chinese Program","CJ":"Criminal Justice","CLAC":"Cultures and Languages Across the Curriculum",
			"CLCV":"Classical Studies","CLSC":"Classical Studies","CMSC":"Computer Science","DANC":"Dance","ECON":"Economics",
			"EDUC":"Education","ENGL":"English","ENVR":"Environmental Studies","FIN":"Finance","FMST":"Film Studies",
			"FREN":"French Program","FYS":"First Year Seminar","GEOG":"Geography","GERM":"German Studies Program",
			"GREK":"Greek","HCS":"Healthcare Studies","HIST":"History","IBUS":"International Business","IDST":"Interdisciplinary Studies",
			"IS":"International Studies","ITAL":"Italian Studies Program","JAPN":"Japanese Program","JOUR":"Journalism",
			"JWST":"Jewish Studies","LAIS":"Latin American, Latino and Iberian Studies","LATN":"Latin","LDST":"Leadership Studies", "LLC":"Languages, Literatures and Cultures",
			"MATH":"Mathematics","MGMT":"Management","MKT":"Marketing", "MSAP":"Music-Applied","MSCL":"Military Science and Leadership",
			"MSEN":"Music-Ensemble","MUS":"Music",
			"PHIL":"Philosophy", "PHYS":"Physics","PLSC":"Political Science","PPEL":"Philosophy Politics Economics and Law",
			"PSYC":"Psychology","RELG":"Religious Studies","RHCS":"Rhetoric and Communication Studies", 
			"RUSN":"Russian Studies Program","SDLC":"Languages, Literatures and Cultures","SOC":"Sociology",
			"SPCS":"School of Professional and Continuing Studies", "SWAH":"Languages, Literatures and Cultures",
			"THTR":"Theatre","UNIV":"University Seminar", "VMAP":"Visual and Media Arts Practice","WELL":"Wellness Program","WGSS":"Women, Gender and Sexuality Studies"};
			
		
		function browse(){
			$.each(subjects, function(k,subjFullName){
				var $subjPanel = $("#subj-list-template").clone().removeClass("hide").removeAttr('id').addClass("subj-"+k);									
				$subjPanel.find(".panel-title").text(subjFullName+" ("+k+")");
				$("#subj-list").append($subjPanel);
			});
		}
		
		function fetchBySubj(k){
			$.ajax({
				url: "<?php echo SUBDIR;?>/richmondAPI.php",
				jsonp: "callback",
				dataType: "jsonp",
				data: {
					subj: k
				},
				success: function( courseData ) {
					courseData = eval(courseData.response);
					
					$.getJSON('http://assets.richmond.edu/catalogs/courses.php?orderby=subjnum&archiveYear=2016&term=&catalogtype=ug&paginate=false&subj='+k+'&level=&keyword=&callback=?', function(data){
						data = data.courses;
						$.each(courseData, function(i,v){
							var $newPanel = $("#subj-list-template2").clone().removeAttr('id');
							var num = v["Course Number"];
							if(v["Course Number"] <= 99){
								num = "0"+v["Course Number"];
							}
							$newPanel = loadCourses($newPanel, data, v, num).removeClass("hide");
							$("#subj-list .subj-"+k).find('.main-body').append($newPanel);
						});
					});
				}
			});
		}
		
		browse();
		var alreadyFetched = [];
		
		$(document).on("click", ".collapse-btn", function(){
			var cls = $(this).parent().attr("class").split(" ");
			cls = cls[cls.length-1].split("-")[1];
			if(alreadyFetched.indexOf(cls) == -1){
				fetchBySubj(cls);
				alreadyFetched.push(cls);
			}
			$(this).parent().find('.panel-collapse:not(.subj-list-collapse)').collapse('toggle');
		});
		
		$(document).on("click", ".collapse-btn-subj", function(){
			$(this).parent().parent().find('.panel-collapse').collapse('toggle');
		});
		
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
		
		$(document).on("keyup", "#searchField", function(){
			var loc = $("#searchField").val();
			if(loc==""){
				$("#search-results").empty();
			}
			if(loc.length < 3){
				return;
			}
			$.ajax({
				url: "<?php echo SUBDIR;?>/richmondAPI.php",
				jsonp: "callback",
				dataType: "jsonp",
				data: {
					search: loc
				},
				success: function( courseData ) {
					courseData = eval(courseData.response);
					var crns = $("#crns").val().replace(/[,]+/g, '').replace(/ +(?= )/g,'').split(" ");
					$.each(courseData, function(i,v){
						var $newPanel = $defaultSearchResult.clone();
						var cn;
						var num = v["Course Number"];
						if(v["Course Number"] <= 99){
							num = "0"+v["Course Number"];
						}
						if(v["Course Number"] > 99){
							cn = v["Course Number"].substr(0,1)+"00";
						}
						else{
							cn = v["Course Number"];
						}
						$.getJSON('http://assets.richmond.edu/catalogs/courses.php?orderby=subjnum&archiveYear=2016&term=&catalogtype=ug&paginate=false&subj='+v["FOS"]+'&level='+cn+'&keyword=&callback=?', function(data){
							data = data.courses;
							$newPanel = loadCourses($newPanel, data, v, num).removeAttr('id').removeClass("hide");
							if(i == 0){
								$("#search-results").empty();
							}
							$("#search-results").append($newPanel);
						});
					});
				}
			});
		});
		
		function loadCourses($newPanel, data, v, num){
			var initial = data.substring(data.indexOf("</span>"+v["FOS"]+" "+num));
			var end = initial.substring(0, initial.indexOf('<!--close inner-content-wrap'));
			var title = end.substring(v["FOS"].length+9+v["Course Number"].length, end.indexOf("</a>"));
			var descr = end.substring(end.indexOf("Description</div>")+17);
			descr = descr.substring(0, descr.indexOf("</div>"));
			var units = end.substring(end.indexOf("Units: ")+7, end.indexOf("</div>"));
			
			var hasDescr = true;
			if(title.indexOf('Print Courses')>-1){
				hasDescr = false;
				title = v["Title"];
				descr = "Course has no description";
				units = "units are unknown";
			}
			
			if(end.indexOf("Prerequisites</div>")>-1){
				var prereq = end.substring(end.indexOf("Prerequisites</div>")+19);
				prereq = prereq.substring(0, prereq.indexOf("</div>"));
			}

			if(!(v["Title"].indexOf("ST:") > -1) && !(v["Title"].indexOf("SP:") > -1) && v["FOS"] != "FYS" && v["FOS"] != "WELL" && !(v["FOS"] == "HIST" && v["Course Number"] == "199") && !(v["FOS"] == "HIST" && v["Course Number"] == "299") && !(v["FOS"] == "ENGL" && v["Course Number"] == "299") && !(v["FOS"] == "BIOL" && v["Course Number"] == "199")){
				if(hasDescr){
					v["Title"] = title;
				}
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

			var inCRN = false;
			$.each(v["crns"], function(i, v2){
				if(crns.indexOf(v2) > -1){
					inCRN = true;
				}
			});

			if(!v["Available"]){
				$newPanel.find("#button").removeClass("btn-success");
				$newPanel.find("#button").removeClass("btn-add-course");
				$newPanel.find("#button").removeClass("glyphicon-plus");
				$newPanel.find("#button").addClass("btn-disable");
				$newPanel.find("#button").text("Course Not Available");
			}
			else if(inCRN){
				$newPanel.find("#button").removeClass("btn-success");
				$newPanel.find("#button").removeClass("btn-add-course");
				$newPanel.find("#button").removeClass("glyphicon-plus");
				$newPanel.find("#button").addClass("btn-disable");
				$newPanel.find("#button").text("Preregistered");
			}

			var $list = $("#course-basket, #course-basket-required").find("li");
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
			return $newPanel;
		}
		
		
		$(document).on("click", ".btn-generate", function (e) {
			var $courses = $("#course-basket li");
			var getCourses = [];
			var count = 0;
			$courses.each(function(){
				var temp = {CourseNum:$(this).data("coursenum"), FOS:$(this).data("fos"), Title:$(this).data("coursename"), displayTitle:$(this).data("displaytitle")};
				getCourses.push(temp);
				count++;
			});
			var $courses = $("#course-basket-required li");
			$courses.each(function(){
				var temp = {CourseNum:$(this).data("coursenum"), FOS:$(this).data("fos"), Title:$(this).data("coursename"), displayTitle:$(this).data("displaytitle"), requiredCourse:true};
				getCourses.push(temp);
				count++;
			});
			
			var unwantedTimes = [];
			$("#block").find(".blocked-time").each(function(){
				var tempTime = {};
				$(this).find("input, .time-display").each(function(){
					if($(this).text() != "" && $(this).text() != undefined){
						var times = $(this).text().split(" - ");
						tempTime["startTime"]=times[0];
						tempTime["endTime"]=times[1];
					}
					if($(this).is(':checked')){
						tempTime[$(this).attr('name')]=$(this).attr('name');
					}
				});
				unwantedTimes.push(tempTime);
			});
			
			var crns = $("#crns").val().replace(/\D+/g, ',').split(",");
			getCourses = {allCourses: getCourses, timePref:$("#time-pref").prop('checked'), fullClasses:$("#full-classes").prop('checked'), preregistered: crns, startTime:$("#restrict-slider").text().split(" - ")[0], endTime:$("#restrict-slider").text().split(" - ")[1], unwantedTimes:unwantedTimes};
			var json = JSON.stringify(getCourses);
			if(count>5){
				window.alert("Trying to generate schedules with this many courses may take a long time, but I will try.\n\nThe calculation is allowed take up to 5 minutes, if it takes longer, it will fail.");
			}
			console.log(json);
			window.location.assign("<?php echo SUBDIR;?>/makeSchedule.php?i="+encodeURIComponent(json));
		});
		
		$(document).on("click", ".btn-jumbo-close", function(){
			$(this).parent().parent().hide();
			document.cookie="jumbotron=hidden";
		});
		
		$(document).on("click", ".btn-remove-course", function (e) {
			var $course = $(e.target);
			if($course.data("fos") == undefined){
				$course = $course.parent();
			}
			
			var fos = $course.data("fos");
			var num = $course.data("coursenum");
			var name = $course.data("coursename");
			
			$("#search-results button").each(function(){
				if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
					$(this).addClass("glyphicon-plus");
					$(this).addClass("btn-success");
					$(this).addClass("btn-add-course");
					$(this).removeClass("btn-danger");
					$(this).removeClass("glyphicon-minus");
					$(this).removeClass("btn-remove-course");
				}
			});
			$(".panel .panel-default button").each(function(){
				if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
					$(this).addClass("glyphicon-plus");
					$(this).addClass("btn-success");
					$(this).addClass("btn-add-course");
					$(this).removeClass("btn-danger");
					$(this).removeClass("glyphicon-minus");
					$(this).removeClass("btn-remove-course");
				}
			});
				
			var $list = $("#course-basket li").add("#course-basket-required li");
			$list.each(function(){
				if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
					$(this).remove();
				}
			});
		});
		
		function addCourse(fos, num, name, displaytitle) {
			var continuing = true;
			var $list = $("#course-basket, #course-basket-required").find("li");
			$list.each(function(){
				if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
					continuing = false;
				}
			});
			
			if(continuing){
				$("#search-results button").each(function(){
					if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
						$(this).removeClass("glyphicon-plus");
						$(this).removeClass("btn-success");
						$(this).removeClass("btn-add-course");
						$(this).addClass("btn-danger");
						$(this).addClass("glyphicon-minus");
						$(this).addClass("btn-remove-course");
					}
				});
				
				$(".panel .panel-default button").each(function(){
					if($(this).data("fos") == fos && $(this).data("coursenum") == num && $(this).data("coursename") == name){
						$(this).removeClass("glyphicon-plus");
						$(this).removeClass("btn-success");
						$(this).removeClass("btn-add-course");
						$(this).addClass("btn-danger");
						$(this).addClass("glyphicon-minus");
						$(this).addClass("btn-remove-course");
					}
				});
			
				var $add = $addedTemplate.clone();
				var $button = $buttonRemoveTemplate.clone().removeClass("hide");
				
				if(displaytitle != null){
					name = displaytitle;
				}
			
				$add.removeClass("hide");
				$add.attr("id", "");
				$add.text(fos+" "+num+" | "+name);
				$add.append("&nbsp; &nbsp; &nbsp; &nbsp;", $button);
				$add.attr("data-fos", fos);
				$add.attr("data-coursenum", num);
				$add.attr("data-coursename", name);
				$add.attr("data-displayTitle", displaytitle);
				
				$("#course-basket").append($add);
			}
		}
		
		$(document).on("click", ".btn-add-course", function(e){
			var $course = $(e.target);			
			var fos = $course.data("fos");
			var num = $course.data("coursenum");
			var name = $course.data("coursename");
			var displaytitle = $course.data("displaytitle");
			addCourse(fos, num, name, displaytitle);
		});
	</script>
</html>