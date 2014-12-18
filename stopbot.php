<?php

/***************************************************************************

	stopbot.php
	This script stops the bot passed in the url
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

try {
	require_once("inc/utils.php");
	
	// initialize bot log and error log
	$bot_log = "";
	$error_log=fopen("log/errors.log", "a");
	
	// check incoming parameter: bot
	$bot = $_GET['bot'];
	if (!isset($bot)) {
		LogEvent($error_log,"Bot parameter is empty");
		fclose($error_log);
		die("Bot parameter is empty");
		}
	else
		$bot = urldecode($bot);
		
	// service manager (to receive bot settings)
	require_once("inc/class.servicemanager.php");
	
	// initialize the service manager
	$SM = new ServiceManager($bot);
	$settings = $SM->get_settings();
	
	// open the log file for appending messages, if logging is enabled
	if ($settings['logging'])
		$bot_log = fopen("log/" . $settings['log_file'], "a");
    
    // assemble full name of bot
	$bot_full_name = $settings["bot_user"] . "@" . $settings["server"];
     // remove lock file, if it exists
     if (file_exists("locks/$bot_full_name.lock")) unlink("locks/$bot_full_name.lock");
    
	if ($error_log) fclose($error_log);
	if ($bot_log) fclose($bot_log);
    exit();
	
	} catch (Exception $e) { 
		// error occured, return error status to calling script
		header("HTTP/1.1 404 Not Found");
		flush();
		LogEvent($error_log, "Error occured: {$e->getMessage()}. ");
		if ($error_log) fclose($error_log);
		if ($bot_log) fclose($bot_log);
		exit();
	}

?>