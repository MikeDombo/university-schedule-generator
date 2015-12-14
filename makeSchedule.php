<?php
spl_autoload_register(function ($class) {
    include "Course.php";
	include "Schedule.php";
	include "Section.php";
	include "MinHeap.php";
});

ob_start();
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
		  ga('create', 'UA-69105822-1', 'auto');
		  ga('send', 'pageview');
		</script>
		<style>
			td{
				color: #000000;
			}
		</style>
	</head>
	<body>
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
		<div class="container-fluid">
			<div class="row col-md-12">				
				<div class="row col-sm-12"><div class="col-sm-6">

<?php
ob_flush();
flush();

$database = "*************";
$user = "*************";
$pass = "**************";
$link = mysqli_connect"localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));


function generateColor($c){
	$red = rand(0, 255);
	$green = rand(0, 255);
	$blue = rand(0, 255);
	$red = ($red + $c[0]) / 2;
	$green = ($green + $c[1]) / 2;
	$blue = ($blue + $c[2]) / 2;
		
	return array(intval($red), intval($green), intval($blue));
}

function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
	if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
	   $jsonp = substr($jsonp, strpos($jsonp, '('));
	}
	return json_decode(trim($jsonp,'();'), $assoc);
}

$allSections = array();
if(isset($_GET["i"])){
	$inputData = json_decode(urldecode($_GET["i"]), true);
	$classCount = count($inputData);
	foreach($inputData as $key=>$section){
		if(preg_match("/\d{3}/", $section["CourseNum"]) && preg_match("/[a-zA-Z]{4}/", $section["FOS"])){
			$subj = $section["FOS"];
			$num = preg_split("/d{3}/", $section["CourseNum"])[0];
			
			$result = mysqli_query($link, "SELECT * FROM `schedule` WHERE (`CRSE#` = '".$num."' AND `SUBJ` = '".$subj."' )");

			$tempSection = array();
			$manyOptions = array();
			$multipleOptions = false;
			while($rows = mysqli_fetch_assoc($result)){
				$sectionNum = $rows["SECTION"];
				if(substr($sectionNum, 0, 1) == "L" || substr($sectionNum, 0, 1) == "P" || substr($sectionNum, 0, 1) == "D"){
					$sectionNum = substr($sectionNum, 1);
					if(substr($sectionNum, 1) == "A" || substr($sectionNum, 1) == "B" || substr($sectionNum, 1) == "C" || substr($sectionNum, 1) == "D"){
						$sectionNum = "0".substr($sectionNum, 0, -2);
						$multipleOptions = true;
					}
				}
				
				if(!isset($tempSection[$sectionNum]) && !$multipleOptions){
					$data = jsonp_decode(file_get_contents('http://assets.richmond.edu/catalogs/courses.php?orderby=subjnum&archiveYear=2015&term=&catalogtype=ug&paginate=false&subj='.$rows["SUBJ"].'&level='.substr($rows["CRSE#"], 0, 1) .'00&keyword=&callback=?'), true)["courses"];
					$initial = substr($data, strpos($data, "</span>".$rows["SUBJ"]." ".$rows["CRSE#"]));
					$end = substr($initial, 0, strpos($initial, '<!--close inner-content-wrap'));
					$title = substr($end, strlen($rows["SUBJ"])+9+strlen($rows["CRSE#"]), strpos($end, "</a>"));
					$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE#"], floatval($rows["CREDIT"]), [$rows["CRN"]]);
					
					foreach($rows as $k=>$v){
						if($k == $v){
							$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
						}
					}
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
					$data = jsonp_decode(file_get_contents('http://assets.richmond.edu/catalogs/courses.php?orderby=subjnum&archiveYear=2015&term=&catalogtype=ug&paginate=false&subj='.$rows["SUBJ"].'&level='+$rows["CRSE#"].substr(0, 1) .'00&keyword=&callback=?'), true)["courses"];
					$initial = $data.substr(strpos($data, "</span>".$rows["SUBJ"]." ".$rows["CRSE#"]), strlen($data));
					$end = $initial.substr(0, strpos($initial, '<!--close inner-content-wrap'), strlen($initial));
					$title = $end.substr($rows["SUBJ"].length+9+$rows["CRSE#"].length, strpos($end, "</a>"), strlen($end));
					
					$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE#"], floatval($rows["CREDIT"]), [$rows["CRN"]]);
					foreach($rows as $k=>$v){
						if($k == $v){
							$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
						}
					}
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
									$newSec->setColor(generateColor(array(127, 127, 127)));
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
							array_push($allSections, $newSec);
						}
					}
				}
				else{
					$v->setColor(generateColor(array(127, 127, 127)));
					array_push($allSections, $v);
				}
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
		$a->setScore();
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
				
				echo "<td class='has-data' style='background:rgba(".makeColorString($v[$a->intToDay($i)]->getColor()).", .60)' data-crns='".$crns."' data-coursenum='".$v[$a->intToDay($i)]->getCourseNumber()."' data-fos='".$v[$a->intToDay($i)]->getFieldOfStudy()."' data-coursetitle='".$v[$a->intToDay($i)]->getCourseTitle()."'>";
				echo $v[$a->intToDay($i)]->getCourseTitle();
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
<?php echo "<h1><strong>".number_format($numSchedules)."</strong>&nbsp;Schedules Generated </h1><h2>from ".number_format($sectionCount)." Sections of ".number_format($classCount)." Courses</h2>";?>
				</div><div class="col-sm-6"><h1 class="pull-right"><a style="color:black;" href="/sched/richmond/?i=<?php echo urlencode(json_encode($inputData));?>"><button class="btn btn-sucess" type="button">Edit Sections</button></a>
				&nbsp;&nbsp;<button class="btn btn-success btn-expand glyphicon glyphicon-collapse-down" type="button"> Expand All Schedules</button>
				&nbsp;&nbsp;<button class="btn-listview btn glyphicon glyphicon-list" type="button"> List View</button></h1></div></div>
				<hr width="100%" />
				
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
						echo "<h4 style='color: #000000;'>".$a->getNumClasses()." classes, ".$a->getNumUnits()." units, with ".reset($a->getCPD())." classes every ".key($a->getCPD())." with score ".$a->score."</h4></div>";
						echo "<div class='panel-body table-responsive' id='calendar".$num."'>";
						
						printWeek($a);						
						
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
					echo "<a data-toggle='collapse' href='#collapse".$num."'>".$a->getNumClasses()." classes, ".$a->getNumUnits()." units, with ".reset($a->getCPD())." classes every ".key($a->getCPD())."</a></div>";
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
						echo "<tr><td class='has-data' style='background:rgba(".makeColorString($b->getColor()).", .60)' data-crns='".$crns."' data-coursenum='".$b->getCourseNumber()."' data-fos='".$b->getFieldOfStudy()."' data-coursetitle='".$b->getCourseTitle()."'>";
						echo $b;
						echo "</tr></td>";
					}
					echo "</table></div></div></div>";
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
		<script>
			function createPopover(element){
				var coursenum = $(element).data('coursenum');
				var fos = $(element).data('fos');
				var crns = $(element).data('crns');
				var coursetitle = $(element).data('coursetitle');
				var html = '<p> '+fos+' '+coursenum+' with CRN: '+crns+'</p>';
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
		</script>
	</body>
</html>
<?php
	echo memory_get_peak_usage()/1024 ."kb peak usage";
	ob_flush();
	flush();
	ob_end_clean();
?>
