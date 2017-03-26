<?php
spl_autoload_register(function ($class) { //load all external classes to run the algorithm
	require_once("classes/".$class.".php");
});

define("SUBDIR", "/ur");
define("DB_DATABASE", "schedule");
define("DB_USER", "root");
define("DB_PASSWORD", "");
define("DB_HOST", "localhost");

/**
 * Returns Pug (Jade) rendered HTML for a given view and options
 * @param $view string Name of Pug view to be rendered
 * @param $title string Title of the webpage
 * @param array $options Additional options needed to render the view
 * @param bool $prettyPrint If prettyPrint is false, all HTML is on a single line
 * @return string Pug generated HTML
 */
function generatePug($view, $title, $options = [], $prettyPrint = false){
		$initialOptions = array(
		'title' => $title,
		'subdir' => SUBDIR
	);

	$options = array_merge($initialOptions, $options);

	$pug = new Pug\Pug(array('prettyprint' => $prettyPrint));
	return $pug->render($view, $options);
}
