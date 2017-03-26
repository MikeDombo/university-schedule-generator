<?php
require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
require_once '../config.php';

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE) or die("Error " . mysqli_error($link));

$objPHPExcel = new PHPExcel();

$objReader = new PHPExcel_Reader_Excel2007();
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load( dirname(__FILE__) . '/Fall_2017.xlsx' );

mysqli_query($link,"DELETE FROM `schedule`");

$rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();

$array_data = array();
$column_keys = array();
foreach($rowIterator as $row){
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
    if(1 == $row->getRowIndex ()){
		foreach ($cellIterator as $cell) {
			array_push($column_keys, $cell->getValue());
		}
		continue;
	}
	
    $rowIndex = $row->getRowIndex ();
    $array_data[$rowIndex] = array();
     
    foreach ($cellIterator as $cell) {
        $array_data[$rowIndex][$column_keys[PHPExcel_Cell::columnIndexFromString($cell->getColumn())-1]] = $cell->getValue();
	}
}

foreach($array_data as $k=>$v){
	$newRow = "";
	$columns = "";
	$chkcol = mysqli_query($link, "SELECT * FROM `schedule` LIMIT 1");
	$mycol = mysqli_fetch_array($chkcol);
	
	foreach($v as $column=>$cell){
		if($cell != ""){
			$cell = mysqli_real_escape_string($link, $cell);
			$newRow = $newRow . "'" . $cell . "', ";
			$columns = $columns .  "`" . $column . "`, ";
		}
		if(!isset($mycol[$column])){
			mysqli_query($link, "ALTER TABLE `schedule` ADD `".$column."` TEXT NOT NULL");
		}
	}
	$newRow = substr($newRow, 0, -2);
	$columns = substr($columns, 0, -2);
	
	mysqli_query($link, "INSERT INTO `schedule` (".$columns.") VALUES (".$newRow.")");
}
?>