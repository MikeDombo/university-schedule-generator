<?php
require_once("config.php");
try{
	$link = new PDO("mysql:dbname=".DB_DATABASE.";host=".DB_HOST.";", DB_USER, DB_PASSWORD);
}
catch(PDOException $e){
	echo 'Connection failed: ' . $e->getMessage();
	exit;
}

header('Content-Type: text/javascript; charset=utf8');

if(isset($_GET['search'])){
	$err = "";
	$_GET['search'] = urldecode($_GET['search']);
	
	if(preg_match("/[a-zA-Z]{1,4}\s*\d{1,3}/", $_GET['search']) || preg_match("/\d{1,3}\s*[a-zA-Z]{1,4}/", $_GET['search'])){
		$num = preg_split("/\s*[a-zA-Z]{1,4}\s*/", $_GET['search']);
		$subj = preg_split("/\s*\d{1,3}\s*/", $_GET['search']);
		if($subj[0] == ""){
			$subj = $subj[count($subj)-1];
		}
		else{
			$subj = $subj[0];
		}
		$subj = strtoupper($subj);
		if($num[0] == ""){
			$num = $num[count($num)-1];
		}
		else{
			$num = $num[0];
		}
		try{
			$q = $link->prepare("SELECT * FROM `schedule` WHERE `CRSE` like :num AND `SUBJ` LIKE :subj");
			$q->bindValue(":num", "%".$num."%", PDO::PARAM_INT);
			$q->bindValue(":subj", "%".$subj."%", PDO::PARAM_STR);
			$q->execute();
			$result = $q->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			echo "Exception ".$e->getMessage();
		}

		$response = array();
		if(count($result) < 1){
			$err = "Nothing returned";
		}
		else{
			$response = getResponseArrayFromDB($result);
		}
	}
	else{
		$searchStr = urldecode($_GET['search']);
		try{
			$q = $link->prepare("SELECT * FROM `schedule` WHERE `TITLE` LIKE :searchStr");
			$q->bindValue(":searchStr", "%".$searchStr."%", PDO::PARAM_STR);
			$q->execute();
			$result = $q->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(PDOException $e){
			echo "Exception ".$e->getMessage();
		}

		$response = getResponseArrayFromDB($result);
	}
	
	if(count($response>50)){
		$response = array_slice($response, 0, 50);
	}
	$arr = ["response"=>$response, "error"=>$err];
	echo $_GET['callback'].'('.json_encode($arr).');';
	exit;
}
if(isset($_GET["subj"])){
	$err="";
	$subj = $_GET["subj"];
	try{
		$q = $link->prepare("SELECT * FROM `schedule` WHERE `SUBJ` = :subj");
		$q->bindValue(":subj", $subj, PDO::PARAM_STR);
		$q->execute();
		$result = $q->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e){
		echo "Exception ".$e->getMessage();
	}

	$response = array();
	if(count($result) < 1){
		$err = "Nothing returned";
	}
	else{
		$response = getResponseArrayFromDB($result);
	}
	$arr = ["response"=>$response, "error"=>$err];
	echo $_GET['callback'].'('.json_encode($arr).');';
}

function getResponseArrayFromDB($result){
	$response = [];
	foreach($result as $rows){
		$temp = array();
		$temp["Title"] = $rows["TITLE"];
		$temp["Course Number"] = $rows["CRSE"];
		$temp["FOS"] = $rows["SUBJ"];
		$temp["Available"] = true;
		$temp["crns"] = array();
		array_push($temp["crns"], $rows["CRN"]);

		if($rows["M"] == "" && $rows["T"] == "" && $rows["W"] == "" && $rows["R"] == "" && $rows["F"] == "" && $rows["S"] == ""){
			$temp["Available"] = false;
		}
		$skip = false;
		foreach($response as $k=>$v){
			if($temp["Course Number"] == $v["Course Number"] && $temp["FOS"] == $v["FOS"]){
				if(testCourseOverlap($temp, $rows, $v)){
					$skip = true;
				}
				if($temp["Title"] == $v["Title"] || similar_text($v["Title"], $temp["Title"]) >= 10){
					array_push($response[$k]["crns"], $rows["CRN"]);
				}
			}
		}

		if(!$skip){
			if($rows["SUBJ"] == "FYS"){
				$fys = getFYSDescr($rows["CRN"]);
				if(isset($fys["displayTitle"])){
					$temp["displayTitle"] = $fys["displayTitle"];
					$temp["description"] = $fys["description"];
				}
			}
			array_push($response, $temp);
		}
	}
	return $response;
}

function getFYSDescr($crn){
	$fysFile = file_get_contents('Seminar.html');
	if(strpos($fysFile, $crn)>-1){
		$fysArr = explode("CRN: ", $fysFile);
		foreach($fysArr as $k => $v){
			if(strpos($v, $crn)>-1){
				$title = substr($fysArr[$k-1], strpos($fysArr[$k-1], "class=\"title\">")+strlen("class=\"title\">"));
				$title = substr($title, 0, strpos($title, "</div>"));
				preg_match("/(id=\"[a-zA-Z0-9]+\">)/", $v, $matches, PREG_OFFSET_CAPTURE);
				$descr = substr($v, $matches[0][1]+strlen($matches[0][0]));
				$descr = substr($descr, 0, strpos($descr, "</p>"));
				$title = htmlspecialchars_decode($title);
				$descr = htmlspecialchars_decode($descr);
				return ["displayTitle"=>$title, "description"=>$descr];
			}
		}
	}
}

function testCourseOverlap($temp, $rows, $v){
	if(($v["FOS"] == "WELL" || $v["FOS"] == "FYS" || (strpos($v["Title"], "ST:") > -1) || (strpos($v["Title"], "SP:") > -1) || ($v["FOS"] == "HIST" && $v["Course Number"] == "199") || ($v["FOS"] == "BIOL" && $v["Course Number"] == "199") || ($v["FOS"] == "ENGL" && $v["Course Number"] == "299") || ($v["FOS"] == "HIST" && $v["Course Number"] == "299")) && ($temp["Title"] == $v["Title"]  || strpos($temp["Title"], " LAB")>-1)){
		return true;
	}
	else if($v["FOS"] != "FYS" && $v["FOS"] != "WELL" && !(strpos($v["Title"], "ST:") > -1) && !(strpos($v["Title"], "SP:") > -1) && !($v["FOS"] == "HIST" && $v["Course Number"] == "199") && !($v["FOS"] == "BIOL" && $v["Course Number"] == "199") && !($v["FOS"] == "ENGL" && $v["Course Number"] == "299")  && !($v["FOS"] == "HIST" && $v["Course Number"] == "299")){
		return true;
	}
	else if(strpos($v["Title"], " LAB")>-1){
		return true;
	}
	else{
		return false;
	}
}
?>