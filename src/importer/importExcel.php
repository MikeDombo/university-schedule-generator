<?php

// Check if called from web browser or if it was included
if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])){
	importExcel(dirname(__FILE__) . '/Fall_2018.xlsx');
}

function importExcel($filepath){
	require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
	require_once '../config.php';

	try{
		$link = new PDO("mysql:dbname=" . DB_DATABASE . ";host=" . DB_HOST . ";charset=utf8", DB_USER, DB_PASSWORD);
	}
	catch(PDOException $e){
		echo 'Connection failed: ' . $e->getMessage();
		error_log($e->getMessage());
		exit;
	}

	$objPHPExcel = new PHPExcel();

	$objReader = new PHPExcel_Reader_Excel2007();
	$objReader->setReadDataOnly(true);
	$objPHPExcel = $objReader->load($filepath);

	// Make database table if it does not exist
	if(!verifyDB($link)){
		echo "Making new database table: <em>" . DB_DATABASE_TABLE . "</em><br/>";
		makeDB($link);
	}
	echo "Clearing data from database<br/>";
	// Clear existing data in the db
	$link->exec("DELETE FROM `" . DB_DATABASE_TABLE . "`");
	echo "Resetting autoincrement value<br/>";
	// Reset ID autoincrementation
	$link->exec("ALTER TABLE `" . DB_DATABASE_TABLE . "` AUTO_INCREMENT = 1");

	$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();

	$array_data = [];
	$column_keys = [];
	foreach($rowIterator as $row){
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		if(1 == $row->getRowIndex()){
			foreach($cellIterator as $cell){
				array_push($column_keys, $cell->getValue());
			}
			continue;
		}

		$rowIndex = $row->getRowIndex() - 1;
		$array_data[$rowIndex] = [];

		foreach($cellIterator as $cell){
			$array_data[$rowIndex][$column_keys[PHPExcel_Cell::columnIndexFromString($cell->getColumn()) - 1]] = $cell->getValue();
		}
	}

	foreach($array_data as $k => $v){
		$newRow = "";
		$columns = "";
		$q = $link->prepare("SELECT * FROM `" . DB_DATABASE_TABLE . "` LIMIT 1");
		$q->execute();
		$mycol = $q->fetchAll(PDO::FETCH_ASSOC);
		if(isset($mycol[0])){
			$mycol = $mycol[0];
		}
		$mycol = array_keys($mycol);

		foreach($v as $column => $cell){
			if($cell != ""){
				$newRow = $newRow . "'" . $cell . "', ";
				$columns = $columns . "`" . $column . "`, ";
			}
			if(!in_array($column, $mycol, true)){
				echo "Adding column: " . $column . " to database table.<br/>";
				$q = $link->exec("ALTER TABLE `" . DB_DATABASE_TABLE . "` ADD `" . $column . "` TEXT");
			}
		}
		$newRow = substr($newRow, 0, -2);
		$columns = substr($columns, 0, -2);

		echo "Adding row $k of " . count($array_data) . "<br/>";
		$link->exec("INSERT INTO " . DB_DATABASE_TABLE . " (" . $columns . ") VALUES (" . $newRow . ")");
	}
	echo "Done!<br/>";
}

/**
 * @param PDO $pdo
 * @return array
 */
function getDatabaseTables($pdo){
	$p = $pdo->prepare("SHOW TABLES");
	$p->execute();
	$rows = $p->fetchAll(PDO::FETCH_ASSOC);
	$tables = [];
	foreach($rows as $r){
		$tables[] = array_values($r)[0];
	}

	return $tables;
}

function verifyDB($pdo){
	try{
		$tables = getDatabaseTables($pdo);

		return in_array(DB_DATABASE_TABLE, $tables, true);
	}
	catch(PDOException $e){
		echo "ERROR: " . $e->getMessage();
		throw $e;
	}
}

/**
 * @param PDO $pdo
 *
 */
function makeDB($pdo){
	$sql = "CREATE TABLE `" . DB_DATABASE_TABLE . "` (`ID` INT NOT NULL AUTO_INCREMENT , PRIMARY KEY (`ID`)) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
	$q = $pdo->prepare($sql);
	$q->execute();
}
