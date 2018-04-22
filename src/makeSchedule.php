<?php
require_once("config.php");

if(isset($_GET["i"])){//check if we received the correct GET request, and redirect back to the input page if not
	$inputData = json_decode(urldecode($_GET["i"]), true);
	if($inputData == null || !isset($inputData["allCourses"]) || count($inputData["allCourses"]) < 1){
		echo "<script type=\"text/javascript\">window.alert('You didn\'t enter any courses!');window.location.assign('" . SUBDIR . "');</script>";
		exit;
	}
}
else{
	echo "<script type=\"text/javascript\">window.alert('You didn\'t enter any courses!');window.location.assign('" . SUBDIR . "');</script>";
	exit;
}

$options = ["inputDataJSON" => urlencode(json_encode($inputData)),
	"max_exec_time" => ini_get("max_execution_time")];

echo generatePug("views/makeSchedule.pug", "Student Schedule Creator", $options);
