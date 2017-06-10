<?php
spl_autoload_register(function ($class) { //load all external classes to run the algorithm
	if(file_exists(__DIR__."/classes/".$class.".php")){
		require_once(__DIR__."/classes/".$class.".php");
	}
});
require_once(__DIR__ . '/vendor/autoload.php');

define("SUBDIR", "/ur/src");
define("DB_DATABASE", "schedule");
define("DB_DATABASE_TABLE", "schedule");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_HOST", "localhost");

// Database column name variables
define("COLUMNS_CRN", "CRN");
define("COLUMNS_COURSE_NUM", "CRSE");
define("COLUMNS_FOS", "SUBJ");
define("COLUMNS_UNITS", "UNITS");
define("COLUMNS_TIME_BEGIN", "BEGIN");
define("COLUMNS_TIME_END", "END");
define("COLUMNS_ENROLLMENT_MAX", "MAX");
define("COLUMNS_ENROLLMENT_CURRENT", "ENRLLMNT");
define("COLUMNS_COURSE_TITLE", "TITLE");
define("COLUMNS_COURSE_SECTION", "SEC");
define("COLUMNS_PROF_FN", "INSTR FN");
define("COLUMNS_PROF_LN", "LASTNAME");


/**
 * Returns Pug (Jade) rendered HTML for a given view and options
 * @param $view string Name of Pug view to be rendered
 * @param $title string Title of the webpage
 * @param array $options Additional options needed to render the view
 * @param bool $prettyPrint If prettyPrint is false, all HTML is on a single line
 * @return string Pug generated HTML
 */
function generatePug($view, $title, $options = [], $prettyPrint = false){
		$initialOptions = [
		'title' => $title,
		'subdir' => SUBDIR,
	];

	$options = array_merge($initialOptions, $options);

	$pug = new Pug\Pug(['prettyprint' => $prettyPrint]);
	return $pug->render($view, $options);
}
