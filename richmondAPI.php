<?php
spl_autoload_register(function ($class) {
    include "Course.php";
	include "Schedule.php";
	include "Section.php";
});

$database = "*******";
$user = "********";
$pass = "*******";
$link = mysqli_connect("localhost", $user, $pass, $database) or die("Error " . mysqli_error($link));

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
		
		$result = mysqli_query($link, "SELECT * FROM `schedule` WHERE (`CRSE#` like '%".$num."%' AND `SUBJ` LIKE '%".$subj."%' )");
		
		$response = array();
		if(mysqli_num_rows($result) < 1){
			$err = "Nothing returned";
		}
		else{
			while($rows = mysqli_fetch_assoc($result)){
				$temp = array();
				$temp["Title"] = $rows["TITLE"];
				$temp["Course Number"] = $rows["CRSE#"];
				$temp["FOS"] = $rows["SUBJ"];
				$temp["Available"] = "true";
				
				if($rows["M"] == "" && $rows["T"] == "" && $rows["W"] == "" && $rows["R"] == "" && $rows["F"] == "" && $rows["S"] == ""){
					$temp["Available"] = "false";
				}
				
				$skip = false;
				foreach($response as $k=>$v){
					if($temp["Course Number"] == $v["Course Number"] && $temp["FOS"] == $v["FOS"]){
						if($v["FOS"] == "FYS" && $temp["Title"] == $v["Title"]){
							$skip = true;
						}
						else if($v["FOS"] != "FYS"){
							$skip = true;
						}
						else{
							$skip = false;
						}
					}
				}
				if(!$skip){
					array_push($response, $temp);
				}
			}
		}
	}
	else{
		$searchStr = "";
		foreach(explode(" ", $_GET['search']) as $a){
			$searchStr = $searchStr."%".$a;
		}
		$result = mysqli_query($link, "SELECT * FROM `schedule` WHERE `TITLE` LIKE '".$searchStr."%' ");
		
		$response = array();
		while($rows = mysqli_fetch_assoc($result)){
			$temp = array();
			$temp["Title"] = $rows["TITLE"];
			$temp["Course Number"] = $rows["CRSE#"];
			$temp["FOS"] = $rows["SUBJ"];
			$temp["Available"] = "true";
			
			if($rows["M"] == "" && $rows["T"] == "" && $rows["W"] == "" && $rows["R"] == "" && $rows["F"] == "" && $rows["S"] == ""){
				$temp["Available"] = "false";
			}
			
			$skip = false;
			foreach($response as $k=>$v){
				if($temp["Course Number"] == $v["Course Number"] && $temp["FOS"] == $v["FOS"]){
					$skip = true;
				}
			}
			if(!$skip){
				array_push($response, $temp);
			}
		}
	}
	
	
	$arr = ["response"=>$response, "error"=>$err];
	echo $_GET['callback'].'('.json_encode($arr).');';
}

mysqli_close($link);
?>