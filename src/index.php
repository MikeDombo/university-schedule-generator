<?php
require __DIR__ . '/vendor/autoload.php';
require_once("config.php");

$required = [];
$optional = [];

if(isset($_GET["i"])){
	$get = $_GET["i"];
	$get = json_decode($get, true)["allCourses"];
	foreach($get as $k=>$v){
		$c = [];
		// Required courses
		if(isset($v["requiredCourse"]) && $v["requiredCourse"]){
			$display = $v["Title"];
			if(isset($v["displayTitle"])){
				$display = $v["displayTitle"];
			}
			$required[] = ["FOS"=>$v["FOS"], "CourseNum"=>$v["CourseNum"], "Title"=>$v["Title"],
				"DisplayTitle"=>$display];
		}
		// Optional courses
		else if(!isset($v["requiredCourse"]) || !$v["requiredCourse"]){
			$display = $v["Title"];
			if(isset($v["displayTitle"])){
				$display = $v["displayTitle"];
			}
			$optional[] = ["FOS"=>$v["FOS"], "CourseNum"=>$v["CourseNum"], "Title"=>$v["Title"],
				"DisplayTitle"=>$display];
		}
	}
}

$preregisteredCRNs = "";
if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["preregistered"])){
	$print = "";
	foreach(json_decode($_GET["i"], true)["preregistered"] as $v){
		$print = $print.$v.", ";
	}
	$preregisteredCRNs = substr($print, 0, -2);
}

$fullClasses = !(isset($_GET["i"]) && !json_decode($_GET["i"], true)["fullClasses"]);

$timePref = (isset($_GET["i"]) && json_decode($_GET["i"], true)["timePref"]);

$slider = ["start"=>480, "end"=>1320];
if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["startTime"])){
	$slider["start"] = (strtotime(json_decode($_GET["i"], true)["startTime"])-strtotime("today"))/60;
}
if(isset($_GET["i"]) && isset(json_decode($_GET["i"], true)["endTime"])){
	$slider["end"] = (strtotime(json_decode($_GET["i"], true)["endTime"])-strtotime("today"))/60;
}

$options = ["courses"=>["required"=>$required, "optional"=>$optional], "preregisteredCRNs"=>$preregisteredCRNs,
	"full_classes"=>$fullClasses, "time_pref"=>$timePref, "slider"=>$slider];

echo generatePug("views/home.pug", "Student Schedule Creator", $options);
