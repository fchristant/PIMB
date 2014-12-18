<?php

/***************************************************************************

	getbot.php
	This script retrieves log lines as XML
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

try {
	
	// check incoming parameters: bot and lines
	$bot = $_GET['bot'];
	if (!isset($bot))
		$bot = "";
	else $bot = urldecode($bot);
	$lines = $_GET['lines'];
	if (!isset($log) || $lines <1)
		$lines = 20;
		
	// get bot log file based on bot file
	if ($bot =="")
		$log = "errors.log";
	else {
		$bot_config_file = preg_replace("/(.bot.php)/i",".ini.php",$bot);
		require_once($bot_config_file); 
    	global $settings;
    	$log = $settings["log_file"];
	}
	
	require_once("inc/utils.php");
    
    // output log lines as xml file
    header('Content-Type: text/xml'); 
	$file = "log/" . $log;
	// first check if the file is a log file
	if (preg_match("/.log$/", $log, $matches)) {
	
		// now check if the file exists and if it contains content
		if (file_exists($file) && filesize($file)>0) {
	
			// get the lines from the file
			$log_lines = getLogXML($file, $lines);
			if($log_lines) 
				echo $log_lines;
		}
	} 
	flush();
	exit();
	}
catch (Exception $e) { 
	// error occured
	header("HTTP/1.1 404 Not Found");
	flush();
	}
?>