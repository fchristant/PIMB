<?php

/***************************************************************************

	getbot.php
	This script retrieves the configuration settings of
	the specified bot
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

try {
	
	// check incoming parameter: bot
	$bot = $_GET['bot'];
	if (!isset($bot)) die("Bot parameter is empty");
	else $bot = urldecode($bot);
	
	// calculate bot ini file location
	$bot_config_file = preg_replace("/(.bot.php)/i",".ini.php",$bot);
	require_once($bot_config_file); 
    // get the settings of the bot
    global $settings;
    
    // output settings as xml file
    header('Content-Type: text/xml'); 
	echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	echo "<bot>\n";
	echo "<bot_name>" . htmlentities($settings["bot_name"]) . "</bot_name>\n";
  	echo "<bot_user>" . htmlentities($settings["bot_user"] . "@" . $settings["server"]) . "</bot_user>\n";
  	echo "<implementation_file>" . htmlentities($bot) . "</implementation_file>\n";
  	echo "<log_file>" . ($settings["logging"]?htmlentities($settings["log_file"]):"-none-") . "</log_file>\n";
  	echo "<author>" . htmlentities($settings["author"]) . "</author>\n";
  	echo "<version>" . htmlentities($settings["version"]) . "</version>\n";
  	echo "<image>" . htmlentities($settings["image"]) . "</image>\n";
  	echo "<description>" . htmlentities($settings["description"]) . "</description>\n";
	echo "</bot>\n";
	flush();
	exit();
	}
catch (Exception $e) { 
	// error occured
	header("HTTP/1.1 404 Not Found");
	flush();
	}
?>