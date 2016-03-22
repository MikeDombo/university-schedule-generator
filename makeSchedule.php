<?php
if(isset($_GET["i"])){//check if we received the correct GET request, and redirect back to the input page if not
	$inputData = json_decode(urldecode($_GET["i"]), true);
	if(count($inputData["allCourses"])<1){
		echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('/ur');</script>";
	}
}
else{
	echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('/ur');</script>";
}
?>
<html>
	<head>
		<title>Student Schedule Creator</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css"></link>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="js/loadingoverlay.min.js"></script>
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
			td{
				color: #000000;
			}
			
			.navbar-brand-name > img {
				max-height:70px;
				width:auto;
				padding: 0 15px 0 0;
			}
			
			.navbar {
				min-height: 90px;
				background-color: #4788c6;
			}
			
			.navbar-collapse.in{
				margin-top:20px;
			}
		</style>
	</head>
	<body>
		<nav class="navbar navbar-default navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<a class="navbar-brand" href="/ur">
						<div class="navbar-brand-name">
							<span style="color:#ffffff">Unofficial University of Richmond Scheduler</span>
						</div>
					</a>
					<button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<div class="navbar-collapse collapse" id="navbar-main">
					<ul class="nav navbar-nav navbar-right">
						<li><a><button class="btn btn-default" type="button" onclick="window.location.href='/ur/?i=<?php echo urlencode(json_encode($inputData));?>'">Edit Courses</button></a></li>
						<li><a><button class="btn btn-success btn-expand glyphicon glyphicon-collapse-down" type="button">&nbsp;Expand All Schedules</button></a></li>
						<li><a><button class="btn-listview btn glyphicon glyphicon-list btn-default" type="button">&nbsp;List View</button></a></li>
					</ul>
				</div>
			</div>
		</nav>
	<script>
		$(document).ready(function(){
			$('[data-toggle="popover"]').popover();   
		});
		
		$(document).on("click", ".btn-expand", function (e) {
			$('.collapse:not(.in)').each(function (index) {
				$(this).addClass("in");
			});
			$(this).text(' Collapse All Schedules');
			$(this).removeClass("glyphicon-collapse-down btn-expand");
			$(this).addClass("glyphicon-collapse-up btn-collapse");
		});
		
		$(document).on("click", ".btn-collapse", function (e) {
			$('.collapse:not(.in)').each(function (index) {
				$(this).collapse("toggle");
			});
			$('.collapse.in').each(function (index) {
				$(this).removeClass("in");
			});
			$(this).text(' Expand All Schedules');
			$(this).addClass("glyphicon-collapse-down btn-expand");
			$(this).removeClass("glyphicon-collapse-up btn-collapse");
		});
		
		$(document).on("click", ".btn-calview", function (e) {
			$(this).text(' List View');
			$(this).addClass("glyphicon-list btn-listview");
			$(this).removeClass("glyphicon-calendar btn-calview");
			$('#list-view').addClass("hide");
			$('#calendar-view').removeClass("hide");
		});
		
		$(document).on("click", ".btn-listview", function (e) {
			$(this).text(' Calendar View');
			$(this).removeClass("glyphicon-list btn-listview");
			$(this).addClass("glyphicon-calendar btn-calview");
			$('#list-view').removeClass("hide");
			$('#calendar-view').addClass("hide");
		});
		
		function getParameterByName(name, url) {
			if (!url) url = window.location.href;
			name = name.replace(/[\[\]]/g, "\\$&");
			var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
				results = regex.exec(url);
			if (!results) return null;
			if (!results[2]) return '';
			return results[2].replace(/\+/g, " ");
		}
	</script>
	<div class="container-fluid" id="results" style="min-height:200px;"></div>
	<script>
		var count = <?php echo ini_get('max_execution_time');?>;
		var ptr = 0;
		var writer = '';
		var sensible = ["Sending Data to Server", "Processing", "Making Schedules", "Wrangling Bits", "A Few Bits Tried to Escape, but We Caught Them", "It's Still Faster Than You Could Do It", "Counting Down from Inifinity", "Reticulating Splines", 
		"Searching for the Answer to Life, the Universe, and Everything","Checking Gravitational Constant in Your Locale"];
		var random = [["Recalibrating", "Excavating", "Acquiring", "Extracting", "Computing", "Deflummoxing", "Binding", "Serving","Routing","Distributing","Sampling","Servicing","Repairing","Discombobulating", "Processing", "Preprocessing"],
		["Flux", "Data", "Spline", "Storage", "Plasma", "Cache", "Laser","Extra Large","Ethernet","WiFi","Wireless","Sample", "Computational", "Local", "Integral"],
		["Capacitor", "Conductor", "Assembler", "Detector", "Post-processor", "Integrator", "Computer", "Disk", "Server","Router","Calculator"]];
		var customElement = $("<img src='/ur/loading-spinner.gif'></img><h3>", {
			id : "countdown",
			text : ""
		});
		function makeFunnyWord(){
			var verb = random[0][Math.floor(Math.random()*random[0].length)];
			var adjective = random[1][Math.floor(Math.random()*random[1].length)];
			var noun = random[2][Math.floor(Math.random()*random[2].length)];
			return verb+" "+adjective+" "+noun;
		}
		var interval = setInterval(function(){
			count--;
			if(count > 10 && count%5 == 4 && ptr < sensible.length){
				writer = sensible[ptr++];
				customElement.text(writer);
			}
			else if(count > 10 && count%5 == 4 && ptr >= sensible.length){
				writer = makeFunnyWord();
				customElement.text(writer);
			}
			else if(count <= 10 && count > 0){
				writer = "Maximum Execution Time Almost Complete "+count+" Seconds Remain";
				customElement.text(writer);
			}
			else if (count <= 0) {
				clearInterval(interval);
				return;
			}
		}, 1000);
		customElement.text(sensible[0]);
		$("#results").LoadingOverlay("show", {
			minSize:"200px",
			size:"100%",
			custom:customElement,
			image:""
		});
		$.ajax({
			url: 'newSched.php',
			type: 'GET',
			data: {"i":getParameterByName("i")},
			dataType: 'html',
			timeout: (count*1000)+5000,
			cache: false,
			success: function(data) {
				$('#results').html(data);
				$("#results").LoadingOverlay("hide");
				clearInterval(interval);
			},
			error: function(e) {
				console.log(e);
				clearInterval(interval);
				if(e.status == 404 || e.status == 500 || e.statusText == "timeout"){
					$("#results").LoadingOverlay("hide");
					$('#results').html("<center><h2>Execution Time Exceeded</h2><h3>Try Again with Fewer Courses</h3></center>");
				}
				else{
					alert("Something went wrong!");
				}
			}
		});
	</script>
	</body>
</html>