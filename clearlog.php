<?php

/***************************************************************************

	getbot.php
	This script deletes the log file of the specified bot
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

try {
	
	// check incoming parameters: log
	$bot = $_GET['bot'];
	if (!isset($bot)) die("Bot parameter is empty");
	else $bot = urldecode($bot);
    
    // get bot log file based on bot file
	if ($bot =="") $log = "errors.log";
	else {
		$bot_config_file = preg_replace("/(.bot.php)/i",".ini.php",$bot);
		require_once($bot_config_file); 
    	global $settings;
    	$log = $settings["log_file"];
	}
	$file = "log/" . $log;
	// first check if the file is a log file
	if (preg_match("/.log$/", $log, $matches)) {
		// now check if the file exists
		if (file_exists($file))
			unlink($file);
	} 
	exit();
}
catch (Exception $e) { 
	// error occured
	header("HTTP/1.1 404 Not Found");
	flush();
	}
?>