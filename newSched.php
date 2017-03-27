<?php
require __DIR__ . '/vendor/autoload.php';
require_once("config.php");
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator
This is the most important file. It accepts a GET request that contains all the requested courses and will generate all the non-conflicting schedules using the "run" function.
**/

$starttime = microtime(true);

if(isset($_GET["i"])){//check if we received the correct GET request, and redirect back to the input page if not
	$inputData = json_decode(urldecode($_GET["i"]), true);
	if(count($inputData["allCourses"])<1){
		echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('".SUBDIR."');</script>";
	}
}
else{
	echo "<script>window.alert('You didn\'t enter any courses!');window.location.assign('".SUBDIR."');</script>";
}

try{
	$link = new PDO("mysql:dbname=".DB_DATABASE.";host=".DB_HOST.";", DB_USER, DB_PASSWORD);
}
catch(PDOException $e){
	echo 'Connection failed: ' . $e->getMessage();
	error_log($e->getMessage());
	exit;
}

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

$inputData = json_decode(urldecode($_GET["i"]), true)["allCourses"];
$preregistered = json_decode(urldecode($_GET["i"]), true)["preregistered"];
$GLOBALS["morning"] = json_decode(urldecode($_GET["i"]), true)["timePref"];
$allowFull = json_decode(urldecode($_GET["i"]), true)["fullClasses"];
$startTime = strtotime(json_decode(urldecode($_GET["i"]), true)["startTime"]);
$endTime = strtotime(json_decode(urldecode($_GET["i"]), true)["endTime"]);
$unwantedTimes = json_decode(urldecode($_GET["i"]), true)["unwantedTimes"];
$daysWithUnwantedTimes = array();
$requiredCourseNum = 0;

if(isset($unwantedTimes)){
	$t = new Schedule();
	foreach($unwantedTimes as $k=>$v){
		foreach($v as $k2=>$v2){
			if(!in_array($k2, $daysWithUnwantedTimes)){
				array_push($daysWithUnwantedTimes, $t->intToDay($t->dayToInt($k2)));
			}
		}
	}
	unset($t);
}

foreach($inputData as $key=>$section){
	if(!isset($section["FOS"]) || !isset($section["CourseNum"]) || !isset($section["Title"])){
		continue;
	}
	if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
		$requiredCourseNum++;
	}
	$subj = $section["FOS"];
	$num = $section["CourseNum"];
	$title = $section["Title"];
	$courseColor = generateColor(array(255, 255, 255));

	$q = $link->prepare("SELECT * FROM `".DB_DATABASE_TABLE."` WHERE `CRSE` = :num AND `SUBJ` = :subj");
	$q->bindValue(":num", $num, PDO::PARAM_INT);
	$q->bindValue(":subj", $subj, PDO::PARAM_STR);
	$q->execute();
	$result = $q->fetchAll(PDO::FETCH_ASSOC);

	$tempSection = array();
	$manyOptions = array();
	$multipleOptions = false;
	foreach($result as $rows){
		if(!$allowFull){
			if($rows["MAX"] <= $rows["ENRLLMNT"]){
				continue;
			}
		}
		if($startTime > strtotime($rows["BEGIN"]) || $endTime < strtotime($rows["END"])){
			continue;
		}

		if(($rows["SUBJ"] == "FYS" || $rows["SUBJ"] == "WELL" || strpos($title, "ST:") > -1 || strpos($title, "SP:") > -1 || ($rows["SUBJ"] == "HIST" && $rows["CRSE"] == "199") || ($rows["SUBJ"] == "BIOL" && $rows["CRSE"] == "199") || ($rows["SUBJ"] == "ENGL" && $rows["CRSE"] == "299")) && $rows["TITLE"] != $title){
			if(strpos($rows["TITLE"], " LAB")>-1 || strpos($title, " LAB")>-1){}
			else{
				continue;
			}
		}
		if(isset($section["displayTitle"])){
			$title = $section["displayTitle"];
		}
		$sectionNum = $rows["SEC"];

		if(substr($sectionNum, 0, 1) == "L" || substr($sectionNum, 0, 1) == "P" || substr($sectionNum, 0, 1) == "D"){
			$sectionNum = substr($sectionNum, 1);
			if(substr($sectionNum, 1) == "A" || substr($sectionNum, 1) == "B" || substr($sectionNum, 1) == "C" || substr($sectionNum, 1) == "D"){
				$sectionNum = "0".substr($sectionNum, 0, -2);
				$multipleOptions = true;
			}
		}
		else{
			if(intval($sectionNum)<10){
				$sectionNum = "0".intval($sectionNum);
			}
		}

		if(!isset($rows["INSTR FN"])){
			$rows["INSTR FN"] = "";
		}
		if(!isset($rows["LASTNAME"])){
			$rows["LASTNAME"] = "";
		}

		if(!isset($tempSection[$sectionNum]) && !$multipleOptions){
			$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE"], floatval($rows["UNITS"]), [$rows["CRN"]]);
			if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
				$tempSec->setRequiredCourse(true);
			}
			foreach($rows as $k=>$v){
				if($k == $v){
					$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
				}
			}
			$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
			if(!(array_search($rows["CRN"], $tempSec->getCRN()) > -1)){
				$tempSec->addCRN($rows["CRN"]);
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
			$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
			$tempSection[$sectionNum] = $tempSec;
		}
		else if($multipleOptions){
			$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE"], floatval($rows["UNITS"]), [$rows["CRN"]]);
			if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
				$tempSec->setRequiredCourse(true);
			}
			foreach($rows as $k=>$v){
				if($k == $v){
					$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
				}
			}
			$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
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
					if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
						$tempSec->setRequiredCourse(true);
					}
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

$preregSections = array();
foreach($preregistered as $k=>$v){
	$courseColor = generateColor(array(255, 255, 255));
	$crn = $v;

	$q = $link->prepare("SELECT * FROM `".DB_DATABASE_TABLE."` WHERE `CRN` = :crn");
	$q->bindValue(":crn", $crn, PDO::PARAM_INT);
	$q->execute();
	$result = $q->fetchAll(PDO::FETCH_ASSOC);

	$tempSection = array();
	foreach($result as $rows){
		if(!isset($rows["INSTR FN"])){
			$rows["INSTR FN"] = "";
		}
		if(!isset($rows["LASTNAME"])){
			$rows["LASTNAME"] = "";
		}

		$title = $rows["TITLE"];

		$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE"], floatval($rows["UNITS"]), [$rows["CRN"]]);

		foreach($rows as $k=>$v){
			if($k == $v){
				$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
			}
		}
		$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
		$tempSec->setColor($courseColor);
		array_push($preregSections, $tempSec);
	}
}

for($i=0; $i<count($preregSections); $i+=1){
	for($j=0; $j<count($preregSections); $j+=1){
		if($i == $j){
			continue;
		}
		$v = $preregSections[$i];
		$v2 = $preregSections[$j];
		if(similar_text($v->getCourseTitle(), $v2->getCourseTitle()) >= 10 && $v->getCourseNumber() == $v2->getCourseNumber() && $v->getFieldOfStudy() == $v2->getFieldOfStudy()){
			foreach($v2->getCRN() as $crn){
				if(!(array_search($crn, $v->getCRN()) > -1)){
					$v->addCRN($crn);
				}
			}
			foreach($v2->meetingTime as $day=>$times){
				foreach($times as $timeKey=>$time){
					$v->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
				}
			}
			unset($preregSections[$j]);
			$preregSections = array_values($preregSections);
		}
	}
}

foreach($preregSections as $v){
	$v->preregistered = true;
	array_push($allSections, $v);
}

$classCount = count($inputData)+count($preregSections);

$t = new Schedule();
foreach($allSections as $key=>$section){
	if($section->preregistered){
		continue;
	}
	foreach($section->meetingTime as $day=>$times){
		if(!in_array($day, $daysWithUnwantedTimes)){
			continue;
		}
		foreach($times as $days=>$time){
			foreach($unwantedTimes as $dayVal){
				foreach($dayVal as $val){
					if($t->intToDay($t->dayToInt($val)) != $days){
						continue;
					}
					else{
						if(strtotime($dayVal["startTime"]) <= $time["to"] && strtotime($dayVal["endTime"]) >= $time["from"]){
							unset($allSections[$key]);
							continue 5;
						}
					}
				}
			}
		}
	}
}

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
$scheduleFile = intval(file_get_contents("schedules-created.txt"));
file_put_contents("schedules-created.txt", $scheduleFile+$numSchedules);

$temp = array();
$schedCount = $GLOBALS['schedules']->count();
for($i=0; $i<$schedCount; $i++){
	array_push($temp, $GLOBALS['schedules']->pop());
}

$GLOBALS['schedules'] = $temp;
unset($temp);
$GLOBALS['schedules'] = array_reverse($GLOBALS['schedules']);

$runTime = microtime(true)-$starttime;

if(!isset($_COOKIE["history"])){
	$inputData["schedules"] = $numSchedules;
	$cookieData = Array();
	array_push($cookieData, $inputData);
	setcookie("history", json_encode($cookieData), strtotime("+30 days"));
}
else{
	$inputData["schedules"] = $numSchedules;
	$cookieData = json_decode($_COOKIE["history"], true);
	$add = true;
	foreach($cookieData as $v){
		$counter = 0;
		foreach($v as $v2){
			foreach($inputData as $i){
				if($v2["Title"] == $i["Title"] && $v2["FOS"] == $i["FOS"] && $v2["CourseNum"] == $i["CourseNum"]){
					$counter = $counter+1;
				}
			}
		}
		if($counter == count($v)){
			$add = false;
		}
	}
	if($add){
		array_push($cookieData, $inputData);
	}
	if(count($cookieData)-10 >= 0){
		$start = count($cookieData)-10;
	}
	else{
		$start = 10;
	}
	array_splice($cookieData, $start);
	setcookie("history", json_encode($cookieData), strtotime("+30 days"));
}

function run($sections, $curr, $pick){
	array_push($curr, $pick);
	$temp = $sections;
	foreach($temp as $k=>$v){
		/** @var \Section $v */
		if($v->conflictsWith($pick)){
			unset($temp[$k]);
		}
	}
	if(count($temp)==0){
		global $requiredCourseNum;
		$a = new Schedule();
		$requiredCourses = 0;
		foreach($curr as $b){
			/** @var \Section $b */
			if($b->isRequiredCourse()){
				$requiredCourses++;
			}
			$a->addSection($b);
		}
		if($requiredCourses == $requiredCourseNum){
			$a->setScore($GLOBALS["morning"]);
			$GLOBALS['schedules']->insert($a);
			$GLOBALS['numSchedules']++;
		}
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

$myPlural = function($word, $num){return plural($word, $num);};
$numFormat = function($num, $digit=0){return number_format($num, $digit);};

$weekSchedule = [];
$listSchedule = [];
$num = 0;
foreach($GLOBALS['schedules'] as $k=>$a){
	/** @var Schedule $a */
	$cpd = $a->getCPD();
	if($num == 100){
		break;
	}
	$daysString = [];

	$numDays = $a->getLastTime()[0] - $a->getFirstTime()[0] + 1;
	for($i = $a->getFirstTime()[0]; $i < ($numDays + $a->getFirstTime()[0]); $i++){
		$daysString[] = $a->intToDay($i);
	}

	$crnList = "";
	$listRows = [];
	foreach($a->getSchedule() as $b){
		/** @var Section $b */
		foreach($b->getCRN() as $crn){
			if($b->preregistered){
				$crn = "<em>".$crn."</em>";
			}
			$crnList = $crnList.", ".$crn;
		}
		$crns = $b->getCRN()[0];
		foreach($b->getCRN() as $j=>$crn){
			if($j==0){
				continue;
			}
			$crns = $crns.", ".$crn;
		}
		$listRows[] = ["color"=>makeColorString($b->getColor()),
			"crns"=>$crns, "coursenum"=>$b->getCourseNumber(),
			"fos"=>$b->getFieldOfStudy(), "preregistered"=>$b->preregistered,
			"title"=>$b->getCourseTitle(), "prof"=>$b->getProf(), "titleWithDate"=>$b->__toString()];
	}

	$listSchedule[intval($num/4)][] = ["rows"=> $listRows, "collapse"=>$num>=4, "num"=> $num,
		"hasAllClasses"=> $classCount == $a->getNumClasses(),
		"numUnits"=> ["num"=>$a->getNumUnits(), "string"=>plural("unit", $a->getNumUnits())],
		"numClasses"=>["num"=>$a->getNumClasses(), "string"=>plural("class", $a->getNumClasses())],
		"numCPD"=>["num"=>reset($cpd), "string"=>plural("class", reset($cpd))],
		"dayCPD"=>key($cpd), "crnList"=>substr($crnList, 2)
	];

	$timeArray = array();
	foreach($a->getSchedule() as $b){
		foreach($b->meetingTime as $day=>$times){
			foreach($times as $key=>$time){
				$timeArray[$time["from"]][$day] = $b;
			}
		}
	}
	ksort($timeArray);

	$rows = [];
	$rowCount = 0;
	foreach($timeArray as $k2=>$v){
		$rows[$rowCount] = [];
		$rows[$rowCount]["timestamp"] = date("g:i a", $k2);
		$rows[$rowCount]["rowData"] = [];

		for($i = $a->getFirstTime()[0]; $i < ($numDays + $a->getFirstTime()[0]); $i++){
			if(isset($v[$a->intToDay($i)])){
				$crns = $v[$a->intToDay($i)]->getCRN()[0];
				foreach($v[$a->intToDay($i)]->getCRN() as $j => $crn){
					if($j == 0){
						continue;
					}
					$crns = $crns.", ".$crn;
				}
				$rows[$rowCount]["rowData"][] = ["color"=>makeColorString($v[$a->intToDay($i)]->getColor()),
					"crns"=>$crns, "coursenum"=>$v[$a->intToDay($i)]->getCourseNumber(),
					"fos"=>$v[$a->intToDay($i)]->getFieldOfStudy(),
					"preregistered"=>$v[$a->intToDay($i)]->preregistered,
					"title"=>$v[$a->intToDay($i)]->getCourseTitle(), "prof"=>$v[$a->intToDay($i)]->getProf()];
			}
			else{
				$rows[$rowCount]["rowData"][] = ["empty"=>true];
			}
		}
		$rowCount += 1;
	}

	$weekSchedule[intval($num/2)][] = ["rows"=>$rows, "daysString"=>$daysString,
		"hasAllClasses"=>$classCount == $a->getNumClasses(),
		"numUnits"=>["num"=>$a->getNumUnits(), "string"=>plural("unit", $a->getNumUnits())],
		"numClasses"=>["num"=>$a->getNumClasses(), "string"=>plural("class", $a->getNumClasses())],
		"numCPD"=>["num"=>reset($cpd), "string"=>plural("class", reset($cpd))],
		"dayCPD"=>key($cpd), "num"=>$num, "crnList"=>substr($crnList, 2)
	];

	$num += 1;
}

$timeUsed = "";
if($runTime*1000<1000){
	$timeUsed = number_format($runTime*1000, 0)." ms";
}
else{
	$timeUsed = number_format($runTime, 3)." s";
}
$maxMemoryUsed = number_format(memory_get_peak_usage()/1024, 2);

$options = ["time_used"=>$timeUsed, "max_memory_used"=>$maxMemoryUsed, "pluralize"=>$myPlural, "number_format"=>$numFormat,
	"numSchedules"=>$numSchedules, "sectionCount"=>$sectionCount, "classCount"=>$classCount,
	"weekSchedule"=>$weekSchedule, "listSchedule"=>$listSchedule];

echo generatePug("views/scheduleViewer.pug", "Student Schedule Creator", $options);
