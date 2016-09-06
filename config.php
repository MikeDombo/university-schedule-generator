<?php
spl_autoload_register(function ($class) { //load all external classes to run the algorithm
	require_once($class.".php");
	require_once("MinHeap.php");
});

define("SUBDIR", "/university-schedule-generator");
define("DB_DATABASE", "****");
define("DB_USER", "****");
define("DB_PASSWORD", "****");
define("DB_HOST", "****");