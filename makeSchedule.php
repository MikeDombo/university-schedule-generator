<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

This is the most important file. It accepts a GET request that contains all the requested courses and will generate all the non-conflicting schedules using the "run" function.
**/

spl_autoload_register(function ($class) { //load all external classes to run the algorithm
    include "Course.php";
	include "Schedule.php";
	include "Section.php";
	include "MinHeap.php";
});

ob_start(); //start output_buffer

if(isset($_GET["i"])){//check if we received the correct GET request, and redirect back to the input page if not
	$inputData = json_decode(urldecode($_GET["i"]), true);
	if(count($inputData["allCourses"])<1){
		echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('/sched/richmond/');</script>";
	}
}
else{
	echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('/sched/richmond/');</script>";
}
?>
<html>
	<head>
		<title>Student Schedule Creator</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="css/bootstrap.min.css"></link>
        <script type="text/javascript" src="js/jquery.min.js"></script>
        <script type="text/javascript" src="js/bootstrap.min.js"></script>
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
					<a class="navbar-brand" style="padding: 10 15px;" href="/sched/richmond/">
						<div class="navbar-brand-name">
							<img src="http://www.richmond.edu/_KP4_assets/images/kp4/shield.png"/>
							<span style="color:#ffffff">University of Richmond Scheduler</span>
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
						<li><a><button class="btn btn-default" type="button" onclick="window.location.href='/sched/richmond/?i=<?php echo urlencode(json_encode($inputData));?>'">Edit Courses</button></a></li>
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
	</script>
<?php
ob_flush();
flush();
$starttime = microtime(true);
$database = "***********";
$user = "***********";
$pass = "***********";
$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));


function generateColor($c){
	$red = rand(0, 255);
	$green = rand(0, 255);
	$blue = rand(0, 255);
	$red = ($red + $c[0]) / 2;
	$green = ($green + $c[1]) / 2;
	$blue = ($blue + $c[2]) / 2;
		
	return array(intval($red), intval($green), intval($blue));
}

function plural($word, $num){
	if($num == 1){
		return $word;
	}
	else{
		if(substr($word, -1) == "y"){
			return substr($word, 0, strlen($word)-1)."ies";
		}
		else if(substr($word, -1) == "s"){
			return $word."es";
		}
		else{
			return $word."s";
		}
	}
}

function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
	if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
	   $jsonp = substr($jsonp, strpos($jsonp, '('));
	}
	return json_decode(trim($jsonp,'();'), $assoc);
}

$allSections = array();
if(isset($_GET["i"])){
	$inputData = json_decode(urldecode($_GET["i"]), true)["allCourses"];
	$GLOBALS["morning"] = json_decode(urldecode($_GET["i"]), true)["timePref"];
	$allowFull = json_decode(urldecode($_GET["i"]), true)["fullClasses"];
	
	$classCount = count($inputData);
	foreach($inputData as $key=>$section){
		$subj = mysqli_real_escape_string($link, $section["FOS"]);
		$num = mysqli_real_escape_string($link, $section["CourseNum"]);
		$title = $section["Title"];
		$courseColor = generateColor(array(255, 255, 255));
		
		$result = mysqli_query($link, "SELECT * FROM `schedule` WHERE (`CRSE#` = '".$num."' AND `SUBJ` = '".$subj."')");

		$tempSection = array();
		$manyOptions = array();
		$multipleOptions = false;
		while($rows = mysqli_fetch_assoc($result)){
			if(!$allowFull){
				if($rows["MAX"] <= $rows["ENROLLMENT"]){
					continue;
				}
			}
			
			if(($rows["SUBJ"] == "FYS" || strpos($title, "ST:") > -1 || strpos($title, "SP:") > -1 || ($rows["SUBJ"] == "HIST" && $rows["CRSE#"] == "199")) && $rows["TITLE"] != $title){
				continue;
			}
			if(isset($section["displayTitle"])){
				$title = $section["displayTitle"];
			}
			$sectionNum = $rows["SECTION"];
			if(substr($sectionNum, 0, 1) == "L" || substr($sectionNum, 0, 1) == "P" || substr($sectionNum, 0, 1) == "D"){
				$sectionNum = substr($sectionNum, 1);
				if(substr($sectionNum, 1) == "A" || substr($sectionNum, 1) == "B" || substr($sectionNum, 1) == "C" || substr($sectionNum, 1) == "D"){
					$sectionNum = "0".substr($sectionNum, 0, -2);
					$multipleOptions = true;
				}
			}
			
			if(!isset($tempSection[$sectionNum]) && !$multipleOptions){
				$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE#"], floatval($rows["CREDIT"]), [$rows["CRN"]]);
				
				foreach($rows as $k=>$v){
					if($k == $v){
						$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
					}
				}
				$tempSec->setProf($rows["INSTR FN"]." ".$rows["INSTR LN"]);
				$tempSection[$sectionNum] = $tempSec;
			}
			else if(!$multipleOptions){
				$tempSec = $tempSection[$sectionNum];
				foreach($rows as $k=>$v){
					if($k == $v){
						$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
					}
				}
				if(!(array_search($rows["CRN"], $tempSec->getCRN()) > -1)){
					$tempSec->addCRN($rows["CRN"]);
				}
				$tempSection[$sectionNum] = $tempSec;
			}
			else if($multipleOptions){
				$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE#"], floatval($rows["CREDIT"]), [$rows["CRN"]]);
				foreach($rows as $k=>$v){
					if($k == $v){
						$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
					}
				}
				$tempSec->setProf($rows["INSTR FN"]." ".$rows["INSTR LN"]);
				if(!(array_search($rows["CRN"], $tempSec->getCRN()) > -1)){
					$tempSec->addCRN($rows["CRN"]);
				}
				array_push($manyOptions, $tempSec);
			}
		}
		
		foreach($tempSection as $k=>$v){
			if($multipleOptions){
				foreach($manyOptions as $optionalSection){
					if($optionalSection->getCourseNumber() == $v->getCourseNumber() && $optionalSection->getFieldOfStudy() == $v->getFieldOfStudy() && !$v->conflictsWithTime($optionalSection)){
						$newSec = new Section($v->getCourseTitle(), $v->getFieldOfStudy(), $v->getCourseNumber(), $v->getNumUnits(), $v->getCRN());
						foreach($optionalSection->meetingTime as $day=>$times){
							foreach($times as $timeKey=>$time){
								$newSec->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
								$newSec->setMultiples(true);
							}
						}
						foreach($v->meetingTime as $day=>$times){
							foreach($times as $timeKey=>$time){
								$newSec->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
							}
						}
						foreach($optionalSection->getCRN() as $crn){
							if(!(array_search($crn, $newSec->getCRN()) > -1)){
								$newSec->addCRN($crn);
							}
						}
						$newSec->setColor($courseColor);
						array_push($allSections, $newSec);
					}
				}
			}
			else{
				$v->setColor($courseColor);
				array_push($allSections, $v);
			}
		}
	}
}
mysqli_close($link);

$GLOBALS['schedules'] = new LimitedMinHeap(100);
$GLOBALS['numSchedules'] = 0;
$sectionCount = count($allSections);

foreach($allSections as $k=>$v){
	unset($allSections[$k]);
	if(!isset($v->meetingTime)){
		continue;
	}
	$curr = array();
	run($allSections, $curr, $v);
}

$numSchedules = $GLOBALS['numSchedules'];
$temp = array();
$schedCount = $GLOBALS['schedules']->count();
for($i=0; $i<$schedCount; $i++){
	array_push($temp, $GLOBALS['schedules']->pop());
}

$GLOBALS['schedules'] = $temp;
unset($temp);
$GLOBALS['schedules'] = array_reverse($GLOBALS['schedules']);

$runTime = microtime(true)-$starttime;

function run($sections, $curr, $pick){
	array_push($curr, $pick);
	$temp = $sections;
	foreach($temp as $k=>$v){
		if($v->conflictsWith($pick)){
			unset($temp[$k]);
		}
	}
	if(count($temp)==0){
		$a = new Schedule();
		foreach($curr as $b){
			$a->addSection($b);
		}
		$a->setScore($GLOBALS["morning"]);
		$GLOBALS['schedules']->insert($a);
		$GLOBALS['numSchedules']++;
	}
	else{
		foreach($temp as $k=>$v){
			unset($temp[$k]);
			run($temp, $curr, $v);
		}
	}
}

function makeColorString($color){
	 return $color[0].", ".$color[1].", ".$color[2];
}

function printWeek($a){
	$numDays = $a->getLastTime()[0] - $a->getFirstTime()[0] + 1;
	echo "<table class='table table-condensed table-bordered'>";
	echo "<tr>";
	echo "<td></td>";
		for($i = $a->getFirstTime()[0]; $i<($numDays+$a->getFirstTime()[0]); $i++){
			echo "<td>";
			echo $a->intToDay($i);
			echo "</td>";
		}
	echo "</tr>";
	
	$timeArray = array();
	foreach($a->getSchedule() as $k=>$b){
		foreach($b->meetingTime as $day=>$times){
			foreach($times as $key=>$time){
				$timeArray[$time["from"]][$day] = $b;
			}
		}
	}
	ksort($timeArray);
	
	foreach($timeArray as $k=>$v){
		echo "<tr>";
		echo "<td>";
		echo date("g:i a", $k);
		echo "</td>";
		for($i = $a->getFirstTime()[0]; $i<($numDays+$a->getFirstTime()[0]); $i++){
			if(isset($v[$a->intToDay($i)])){
				$crns = $v[$a->intToDay($i)]->getCRN()[0];
				foreach($v[$a->intToDay($i)]->getCRN() as $j=>$crn){
					if($j==0){
						continue;
					}
					$crns = $crns.", ".$crn;
				}
				
				echo "<td class='has-data' style='background:rgba(".makeColorString($v[$a->intToDay($i)]->getColor()).", .60)' data-crns='".$crns."' data-coursenum='".htmlspecialchars($v[$a->intToDay($i)]->getCourseNumber())."' data-fos='".htmlspecialchars($v[$a->intToDay($i)]->getFieldOfStudy())."' data-coursetitle=\"".htmlspecialchars($v[$a->intToDay($i)]->getCourseTitle())."\" data-prof='".htmlspecialchars($v[$a->intToDay($i)]->getProf())."'>";
				echo htmlspecialchars($v[$a->intToDay($i)]->getCourseTitle());
			}
			else{
				echo "<td>";
			}
			echo "</td>";
		}
		echo "</tr>";
	}
	
	echo "</table>";
}
?>
		<div class="container-fluid">
			<div class="col-md-12">
				<div class="page-header" style="margin-top:0px;">
					<h2><strong><?php echo number_format($numSchedules);?></strong>&nbsp;<?php echo plural("Schedule", $numSchedules);?> Generated </h2>
					<h3>from&nbsp;<?php echo number_format($sectionCount)." ".plural('Section', $sectionCount)." of ".number_format($classCount);?>&nbsp;<?php echo plural("Course", $classCount);?></h3>
				</div>
				
				<div class="panel-group" id="calendar-view">
					<?php
					$num = 0;
					foreach($GLOBALS['schedules'] as $k=>$a){
						if($num == 100){
							break;
						}
						if($num%2==0){
							echo "<div class='row' style='margin:2px;'>";
						}
						echo "<div class='col-md-6'>";
						echo "<div class='panel panel-default' style='margin:4px;'>";
						echo "<div class='panel-heading panel-title'>";
						echo "<h5 style='color: #000000;'>".$a->getNumClasses()." ".plural("class", $a->getNumClasses()).", ".$a->getNumUnits()." ".plural("unit", $a->getNumUnits()).", with ".reset($a->getCPD())." ".plural("class", reset($a->getCPD()))." every ".key($a->getCPD());
						if($classCount == $a->getNumClasses()){
							echo '<span style="color:#4CAF50;" data-toggle="tooltip" title="Has all classes you asked for" class="glyphicon glyphicon-ok pull-right"></span>';
						}
						echo "</h5></div>";
						echo "<div class='panel-body table-responsive' id='calendar".$num."'>";
						
						printWeek($a);
						echo "<h6>CRNs: ";
						$crns = "";
						foreach($a->getSchedule() as $v){
							foreach($v->getCRN() as $crn){
								$crns = $crns.", ".$crn;
							}
						}
						echo substr($crns, 2)."</h6>";
						
						echo "</div></div></div>";
						if($num%2==1){
							echo "</div>";
						}
						$num+=1;
						
						ob_flush();
						flush();
					}
					?>
				</div>
			</div>
				
			<div class="panel-group hide" id="list-view">
				<?php 
				$num = 0;
				foreach($GLOBALS['schedules'] as $k=>$a){
					if($num == 100){
						break;
					}
					if($num%4==0){
						echo "<div class='row' style='margin:2px;'>";
					}
					$in = "";
					if($num<4){
						$in = " in";
					}
					echo "<div class='col-md-3'>";
					echo "<div class='panel panel-default'>";
					echo "<div class='panel-heading panel-title' data-toggle='collapse' data-target='#collapse".$num."' style='cursor: pointer;'>";
					echo "<a data-toggle='collapse' href='#collapse".$num."'>".$a->getNumClasses()." ".plural("class", $a->getNumClasses()).", ".$a->getNumUnits()." ".plural("unit", $a->getNumUnits()).", with ".reset($a->getCPD())." ".plural("class", reset($a->getCPD()))." every ".key($a->getCPD());
					if($classCount == $a->getNumClasses()){
						echo '<span style="color:#4CAF50;" data-toggle="tooltip" title="Has all classes you asked for" class="glyphicon glyphicon-ok pull-right"></span>';
					}
					echo "</a></div>";
					echo "<div class='panel-collapse collapse panel-body".$in."' id='collapse".$num."'>";
					echo "<table class='table table-condensed table-responsive'>";
					foreach($a->getSchedule() as $b){
						$crns = $b->getCRN()[0];
						foreach($b->getCRN() as $j=>$crn){
							if($j==0){
								continue;
							}
							$crns = $crns.", ".$crn;
						}
						echo "<tr><td class='has-data' style='background:rgba(".makeColorString($b->getColor()).", .60)' data-crns='".$crns."' data-coursenum='".htmlspecialchars($b->getCourseNumber())."' data-fos='".htmlspecialchars($b->getFieldOfStudy())."' data-coursetitle=\"".htmlspecialchars($b->getCourseTitle())."\" data-prof='".htmlspecialchars($b->getProf())."'>";
						echo htmlspecialchars($b);
						echo "</tr></td>";
					}
					echo "</table><h6>CRNs: ";
					$crns = "";
					foreach($a->getSchedule() as $v){
						foreach($v->getCRN() as $crn){
							$crns = $crns.", ".$crn;
						}
					}
					echo substr($crns, 2)."</h6>";
					echo "</div></div></div>";
					if($num%4==3 || $k==count($GLOBALS['schedules'])){
						echo "</div>";
					}
					$num += 1;
					
					ob_flush();
					flush();
				}
				?>
			</div>
			<div class='col-md-12'>
				<div class='col-md-4'></div>
				<div class='col-md-4'>
				<?php
					if($num == 100){
						echo "<h1 style='text-align:center;'>View Truncated to Best 100 Schedules</h1>";
					}
				?>
				</div>
				<div class='col-md-4'></div>
			</div>
		</div>
		</div>
		<script>
			function html_encode(value){
				return $("<div/>").text(value).html();
			}
			function createPopover(element){
				var coursenum = html_encode($(element).data('coursenum'));
				var fos = html_encode($(element).data('fos'));
				var prof = html_encode($(element).data('prof'));
				var crns = $(element).data('crns');
				var coursetitle = html_encode($(element).data('coursetitle'));
				var html = '<p> '+fos+' '+coursenum+' with CRN: '+crns+'</p><p>Professor: '+prof+'</p>';
				var options = {placement: 'bottom', container: "body", trigger: 'manual', html: true, title: coursetitle};
				
				$(element).data('content', html).popover(options);
			}
			
			function popoverPlacementBottom(){
				createPopover($(this));
			}
			
			var insidePopover=false;
			
			function attachEvents(td) {
				$('.popover').on('mouseenter', function() {
					insidePopover=true;
				});
				$('.popover').on('mouseleave', function() {
					insidePopover=false;
					$(td).popover('hide');
				});
			}

			$('table').on('mouseenter', 'td.has-data', function() {
				var td=$(this);
				setTimeout(function(){
					if (!insidePopover){
						$(td).popover('show');
						attachEvents(td);
					}
				}, 200);
			});

			$('table').on('mouseleave', 'td.has-data', function() {
				var td=$(this);
				setTimeout(function() {
					if (!insidePopover){
						$(td).popover('hide');
					}
				}, 200);
			});
			
			$('td.has-data').each(popoverPlacementBottom);
			
			$(document).ready(function(){
				$('[data-toggle="tooltip"]').tooltip(); 
			});
		</script>
		<div class="container-fluid" style="margin-top:30px;">
			<div class="col-md-12 well well-lg" style="text-align:center;">
				<div class="col-md-6">
					<h4>Made by <a href="http://mikedombrowski.com" style="color:#444444;">Michael Dombrowski</a></h4>
					<h5>Code Available on <a href="https://github.com/md100play/university-schedule-generator" style="color:#444444;">GitHub</a></h5>
					<h5>Feel Free to Contact Me With Issues or Feature Requests at <a href="mailto:michael@mikedombrowski.com" style="color:#444444;">Michael@MikeDombrowski.com&nbsp;<span class="glyphicon glyphicon-envelope" style="vertical-align:top;"></span></a></h5>
				</div>
				<style>
					@media screen and (min-width: 992px){
						div.vdivide {
							border-left: 1px solid #A4A4A4;
						}
						hr.vdivide {
							display:none;
						}
					}
				</style>
				<hr class="vdivide" style="border-top-color:#A4A4A4"></hr>
				<div class="col-md-6 vdivide">
					<h4>Stats For Nerds</h4>
					<ul class="list-group">
						<li class="list-group-item">Time to Compute: <?php if($runTime*1000<1000){echo number_format($runTime*1000, 0)." ms";} else{echo number_format($runTime, 3)." s";}?></li>
						<li class="list-group-item">Maximum Memory Used: <?php echo number_format(memory_get_peak_usage()/1024, 2);?> kilobytes</li>
					</ul>
				</div>
			</div>
		</div>
	</body>
</html>
<?php
	ob_flush();
	flush();
	ob_end_clean();
?>
