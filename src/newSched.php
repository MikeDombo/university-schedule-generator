<?php
require __DIR__ . '/vendor/autoload.php';
require_once("config.php");

/**
 * Authored by Michael Dombrowski, http://mikedombrowski.com
 * Original repository available at http://github.com/md100play/university-schedule-generator
 * This is the most important file. It accepts a GET request that contains all the requested courses and will generate
 * all the non-conflicting schedules using the "run" function.
 **/

$startTime = microtime(true);

if(isset($_GET["i"])){//check if we received the correct GET request, and redirect back to the input page if not
	$inputData = json_decode(urldecode($_GET["i"]), true);
	if(count($inputData["allCourses"]) < 1){
		echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('" . SUBDIR . "');</script>";
		exit(0);
	}
}
else{
	echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('" . SUBDIR . "');</script>";
	exit(0);
}

try{
	$link = new PDO("mysql:dbname=" . DB_DATABASE . ";host=" . DB_HOST . ";", DB_USER, DB_PASSWORD);
}
catch(PDOException $e){
	echo 'Connection failed: ' . $e->getMessage();
	error_log($e->getMessage());
	exit;
}

$ingest = new Ingest(new MySQLDAL($link), urldecode($_GET["i"]));
$ingest->generateSections();

$scheduleGenerator = new ScheduleGenerate($ingest);
$scheduleGenerator->generateSchedules($ingest->getAllSections());
$numSchedules = $scheduleGenerator->getNumSchedules();

// Write number of generated schedules to a local text file
$numSchedulesCreatedFile = "schedules-created.txt";
$scheduleFile = 0;
if(file_exists($numSchedulesCreatedFile)){
	$scheduleFile = intval(file_get_contents($numSchedulesCreatedFile));
}
file_put_contents("schedules-created.txt", $scheduleFile + $numSchedules);

// Update history cookie
updateHistoryCookie($numSchedules, json_decode(urldecode($_GET["i"]), true)["allCourses"]);

// Get schedules as an array in order of highest score to lowest
$schedules = $scheduleGenerator->getSchedules()->getMaxArray();
$d = $scheduleGenerator->makeDataForCalendarAndList($schedules);

// Generate runtime and max memory used
$runTime = microtime(true) - $startTime;
if($runTime * 1000 < 1000){
	$timeUsed = number_format($runTime * 1000, 0) . " ms";
}
else{
	$timeUsed = number_format($runTime, 3) . " s";
}
$maxMemoryUsed = number_format(memory_get_peak_usage() / 1024, 2);

$options = ["time_used" => $timeUsed, "max_memory_used" => $maxMemoryUsed,
	"numSchedules" => ["num" => number_format($numSchedules),
		"string" => $scheduleGenerator->plural("Schedule", $numSchedules)],
	"sectionCount" => ["num" => number_format($scheduleGenerator->getSectionCount()),
		"string" => $scheduleGenerator->plural("Section", $scheduleGenerator->getSectionCount())],
	"classCount" => ["num" => number_format($ingest->getClassCount()),
		"string" => $scheduleGenerator->plural("Course", $ingest->getClassCount())],
	"weekSchedule" => $d[0], "listSchedule" => $d[1]];

// Make the schedule view page from the options above
echo generatePug("views/scheduleViewer.pug", "Student Schedule Creator", $options);

/**
 * Updates the history cookie
 *
 * @param int $numSchedules
 * @param array $inputData
 */
function updateHistoryCookie($numSchedules, $inputData){
	$cookieData = [];
	$inputData["schedules"] = $numSchedules;
	if(!isset($_COOKIE["history"])){
		$cookieData[] = $inputData;
	}
	else{
		$cookieData = json_decode($_COOKIE["history"], true);
		$add = true;
		foreach($cookieData as $v){
			$counter = 0;
			foreach($v as $v2){
				foreach($inputData as $i){
					if($v2["Title"] == $i["Title"] && $v2["FOS"] == $i["FOS"] && $v2["CourseNum"] == $i["CourseNum"]){
						$counter = $counter + 1;
					}
				}
			}
			if($counter == count($v)){
				$add = false;
			}
		}
		if($add){
			$cookieData[] = $inputData;
		}
		if(count($cookieData) - 10 >= 0){
			$start = count($cookieData) - 10;
		}
		else{
			$start = 10;
		}
		array_splice($cookieData, $start);
	}
	setcookie("history", json_encode($cookieData), strtotime("+30 days"));
}
