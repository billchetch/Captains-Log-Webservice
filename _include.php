<?php
spl_autoload_register(function ($class) {
	$dir = dirname(__FILE__); //the directory of this script (not the script that includes this)
	
	$class = str_replace("\\", "/", $class);
	
	$paths = array('../common/php/classes', 'classes');
	if(defined('_CLASS_PATHS_')){
		$paths = array_merge($paths, explode(',', _CLASS_PATHS_));
	}
	
	foreach($paths as $path){
		$classdir = realpath(dirname(__FILE__).'/'.$path);
		$fn = $classdir.'/'.$class.'.php';
		if(file_exists($fn)){
			include $fn;
			return;
		}
		
		$it = new RecursiveDirectoryIterator($classdir);
		foreach(new RecursiveIteratorIterator($it) as $file){
			if(basename($file) == $class.'.php'){
				include $file;
				return;
			}
		}
	} //end looping through paths
});


require('_config.php');


use chetch\Config as Config;
if(Config::get('ERROR_REPORTING')){
	error_reporting(Config::get('ERROR_REPORTING'));
}

use chetch\db\DB as DB;
use chetch\sys\Logger as Logger;

$dbh = null;
try{
	
	date_default_timezone_set('UTC');

	DB::connect(Config::get('DBHOST'), Config::get('DBNAME'), Config::get('DBUSERNAME'), Config::get('DBPASSWORD'));
	DB::setUTC();

	Logger::setLog(basename($_SERVER['PHP_SELF'], ".php"), Logger::LOG_TO_DATABASE);

} catch (Exception $e){
	echo "exception: ".$e->getMessage();
	die;
}
?>