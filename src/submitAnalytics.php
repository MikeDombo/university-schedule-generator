<?php
require_once("config.php");

try{
	$link = new PDO("mysql:dbname=" . DB_DATABASE . ";host=" . DB_HOST . ";", DB_USER, DB_PASSWORD);
}
catch(PDOException $e){
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}
$mysqlLink = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);

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

$q = $link->prepare("SELECT `userID`, `history` from `byUser` WHERE `userID`=:uid");
$q->bindValue(":uid", $uid);
$q->execute();
$row = $q->fetchAll(PDO::FETCH_ASSOC);
if($row != null && count($row) > 0 && isset($row["history"])){
	$prev = json_decode($row["history"], true);
	array_push($prev, $inputData);
	$q = $link->prepare("SELECT `Last Time` from `byUser` WHERE `userID`=:uid");
	$q->bindValue(":uid", $uid);
	$q->execute();
	if(intval($q->fetchAll(PDO::FETCH_ASSOC)["Last Time"]) >= $today){
		$returning = 0;
	}
	else{
		$returning = 1;
	}
	$q = $link->prepare("UPDATE `byUser` SET `history`=':history', `Last Time`=" . time() . ", `numLookups`=`numLookups`+1 
	WHERE `userID`=:uid");
	$q->bindValue(":uid", $uid);
	$q->bindValue(":history", json_encode($prev), PDO::PARAM_STR);
	$q->execute();
}
else{
	$unique = intval(mysqli_fetch_array(mysqli_query($mysqlLink, "SELECT `id` from `byUser` ORDER BY `id` DESC LIMIT 1"))[0]) + 1;
	$arr = array();
	array_push($arr, $inputData);
	mysqli_query($mysqlLink, "INSERT INTO `byUser` (`id`, `userID`, `history`, `numLookups`, `First Time`, `Last Time`) VALUES ('" . $unique . "', '" . $uid . "', '" . json_encode($arr) . "', 1, " . time() . ", " . time() . ")");
	$new = 1;
}

$unique = intval(mysqli_fetch_array(mysqli_query($mysqlLink, "SELECT `id` from `byLookup` ORDER BY `id` DESC LIMIT 1"))[0]) + 1;
mysqli_query($mysqlLink, "INSERT INTO `byLookup` (`id`, `userID`, `lookup`, `timestamp`, `numCourses`) VALUES ('" . $unique . "', '" . $uid . "', '" . json_encode($inputData) . "', " . time() . ", " . count($inputData["allCourses"]) . ")");

$fos = array();
foreach($inputData["allCourses"] as $v){
	if(!isset($fos[$v["FOS"]])){
		$fos[$v["FOS"]] = 1;
	}
	else{
		$fos[$v["FOS"]] = $fos[$v["FOS"]] + 1;
	}
}

$chkcol = mysqli_query($mysqlLink, "SELECT * FROM `byLookup` LIMIT 1");
$mycol = mysqli_fetch_array($chkcol);

foreach($fos as $k => $v){
	if(!isset($mycol[$k])){
		mysqli_query($mysqlLink, "ALTER TABLE `byLookup` ADD `" . $k . "` INT NOT NULL");
	}
	mysqli_query($mysqlLink, "UPDATE `byLookup` SET `" . $k . "`= " . $v . " WHERE `userID`= '" . $uid . "'");
}

$result = mysqli_fetch_assoc(mysqli_query($mysqlLink, "SELECT * from `byDay` ORDER BY `day` DESC LIMIT 1"));
if($result["day"] == $today){
	mysqli_query($mysqlLink, "UPDATE `byDay` SET `new`=" . ($result["new"] + $new) . ", `returning`=" . ($result["returning"] + $returning) . ", `numLookups`=" . ($result["numLookups"] + 1) . " WHERE `day`=" . $today);
}
else{
	mysqli_query($mysqlLink, "INSERT INTO `byDay` (`day`, `new`, `returning`, `numLookups`) VALUES (" . $today . ", " . $new . ", " . $returning . ", 1)");
}

mysqli_close($mysqlLink);
?>
