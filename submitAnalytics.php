<?php
$database = "******";
$user = "*******";
$pass = "*********";
$link = mysqli_connect("just148.justhost.com", $user, $pass, $database) or die("Error " . mysqli_error($link));
date_default_timezone_set('UTC');

$today = intval(date("U", strtotime("today")));
$inputData = json_decode(urldecode($_POST["i"]), true);
if(!isset($inputData["allCourses"])){
	exit;
}
$uid = $_POST["userID"];
$inputData["timestamp"] = time();

$new = 0;
$returning = 0;
$result = mysqli_query($link, "SELECT `userID`, `history` from `byUser` WHERE `userID`='".$uid."'");
$row = mysqli_fetch_array($result);
if ($row != NULL && isset($row["history"])){
	$prev = json_decode($row["history"], true);
	array_push($prev, $inputData);
	if(intval(mysqli_fetch_assoc(mysqli_query($link, "SELECT `Last Time` from `byUser` WHERE `userID`='".$uid."'"))["Last Time"])>=$today){
		$returning = 0;
	}
	else{
		$returning = 1;
	}
	mysqli_query($link, "UPDATE `byUser` SET `history`='".json_encode($prev)."' WHERE `userID`= '".$uid."'");
	mysqli_query($link, "UPDATE `byUser` SET `Last Time`= ".time()." WHERE `userID`= '".$uid."'");
	$lookups = intval(mysqli_fetch_array(mysqli_query($link, "SELECT `numLookups` from `byUser` where `userID`='".$uid."'"))["numLookups"])+1;
	mysqli_query($link, "UPDATE `byUser` SET `numLookups`= ".$lookups." WHERE `userID`= '".$uid."'");
}
else {
	$unique = intval(mysqli_fetch_array(mysqli_query($link, "SELECT `id` from `byUser` ORDER BY `id` DESC LIMIT 1"))[0])+1;
	$arr = array();
	array_push($arr, $inputData);
	mysqli_query($link, "INSERT INTO `byUser` (`id`, `userID`, `history`, `numLookups`, `First Time`, `Last Time`) VALUES ('".$unique."', '".$uid."', '".json_encode($arr)."', 1, ".time().", ".time().")");
	$new = 1;
}

$unique = intval(mysqli_fetch_array(mysqli_query($link, "SELECT `id` from `byLookup` ORDER BY `id` DESC LIMIT 1"))[0])+1;
mysqli_query($link, "INSERT INTO `byLookup` (`id`, `userID`, `lookup`, `timestamp`, `numCourses`) VALUES ('".$unique."', '".$uid."', '".json_encode($inputData)."', ".time().", ".count($inputData["allCourses"]).")");

$fos = array();
foreach($inputData["allCourses"] as $v){
	if(!isset($fos[$v["FOS"]])){
		$fos[$v["FOS"]] = 1;
	}
	else{
		$fos[$v["FOS"]] = $fos[$v["FOS"]]+1;
	}
}

$chkcol = mysqli_query($link, "SELECT * FROM `byLookup` LIMIT 1");
$mycol = mysqli_fetch_array($chkcol);

foreach($fos as $k=>$v){
	if(!isset($mycol[$k])){
		mysqli_query($link, "ALTER TABLE `byLookup` ADD `".$k."` INT NOT NULL");
	}
	mysqli_query($link, "UPDATE `byLookup` SET `".$k."`= ".$v." WHERE `userID`= '".$uid."'");
}

$result = mysqli_fetch_assoc(mysqli_query($link, "SELECT * from `byDay` ORDER BY `day` DESC LIMIT 1"));
if($result["day"] == $today){
	mysqli_query($link, "UPDATE `byDay` SET `new`=".($result["new"]+$new).", `returning`=".($result["returning"]+$returning).", `numLookups`=".($result["numLookups"]+1)." WHERE `day`=".$today);
}
else{
	mysqli_query($link, "INSERT INTO `byDay` (`day`, `new`, `returning`, `numLookups`) VALUES (".$today.", ".$new.", ".$returning.", 1)");
}
?>