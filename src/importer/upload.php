<?php

$validPasswords = ["maintainer" => "*********"];
$validUsers = array_keys($validPasswords);

$user = empty($_SERVER['PHP_AUTH_USER']) ? '' : $_SERVER['PHP_AUTH_USER'];
$pass = empty($_SERVER['PHP_AUTH_PW']) ? '' : $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $validUsers)) && ($pass == $validPasswords[$user]);


function authenticate(){
	header('WWW-Authenticate: Basic realm="ImportExcel"');
	header('HTTP/1.0 401 Unauthorized');
	echo 'You must authenticate!';
	exit;
}

if (!isset($_SERVER['PHP_AUTH_USER'])) {
	authenticate();
}
else if($validated){
	date_default_timezone_set('UTC');
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		if(is_uploaded_file($_FILES["file"]['tmp_name'])){
			$dateFormat = "Y-m-d\TH.i.s";
			$newFile = dirname(__FILE__) . '/' . date($dateFormat) . "." . pathinfo($_FILES["file"]["name"],
					PATHINFO_EXTENSION);
			move_uploaded_file($_FILES["file"]['tmp_name'], $newFile);
			include 'importExcel.php';
			importExcel($newFile);
		}
	}
	else if($_SERVER['REQUEST_METHOD'] == "GET"){
		echo "<html><body>
		<form action='upload.php' method='post' enctype='multipart/form-data'>
			<input type='file' name='file'><input type='submit'>
		</form>
		</body></html>";
	}
	exit;
}
authenticate();
