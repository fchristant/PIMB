<?php

/***************************************************************************

	startbot.php
	This script contains the main logic that is used by all bots
	to run, receive messages and pass them to the bot implementation
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

// to keep your bot running forever
set_time_limit(0);

try {
	
	// load the PIMB config settings
	require_once("pimb.conf.php");
	// load utility functions
	require_once("inc/utils.php");
	
	// initialize bot log and error log
	$bot_log = "";
	$error_log=fopen("log/errors.log", "a");
	
	// check incoming parameter: bot
	
	if ($argv[1])
		// script is called from CLI.
		$bot = $argv[1];
	else
		$bot = $_GET['bot'];
		
	if (!isset($bot)) {
		// no bot to start
		LogEvent($error_log, "Bot parameter is empty");
		fclose($error_log);
		die("Bot parameter is empty");
		}
	else
		$bot = urldecode($bot);
	
		
	// load jabber and service manager engines
	require_once("inc/class.jabber.php");
	require_once("inc/class.servicemanager.php");

	// initialize the service manager with the bot to start
	$SM = new ServiceManager($bot);
	$settings = $SM->get_Settings();
	
	// assemble full name of bot
	$bot_full_name = $settings["bot_user"] . "@" . $settings["server"];
	
	// open the log file for appending messages, if logging is enabled
	if ($settings['logging'])
		$bot_log = fopen("log/" . $settings['log_file'], "a");

	// block notices, the jabber class seems to output zillions of them
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
	$JABBER = new Jabber;
	
	// initialize the service manager
	LogEvent($bot_log, "Initializing service for bot: $bot_full_name");
	$SM->startService();
	LogEvent($bot_log, "Setting connection values for bot: $bot_full_name");
		
	// set the Jabber connection configuration values	
	global $JABBER;
	$JABBER->server = $settings['server'];
	$JABBER->port = $settings['port'];
	$JABBER->username = $settings['bot_user'];
	$JABBER->password = $settings['bot_password'];
	$JABBER->resource = $settings['resource'];
	
	// try to connect to the jabber service, a maximum of 5 attempts will be made
	if (!$JABBER->connected)
    	for ($i=1; $i<=5; $i++) {
    		LogEvent($bot_log, "Connecting bot: $bot_full_name, attempt $i");
    		if ($JABBER->Connect()) break;
    		}
		
    if (!$JABBER->connected)
		throw new Exception("Error connecting to the Jabber server: {$settings["server"]}");
	
	LogEvent($bot_log, "Bot $bot_full_name connected");
			
	// try to authenticate on the jabber service
	LogEvent($bot_log, "Authenticating bot: $bot_full_name");
	$JABBER->SendAuth();
	
	// write lock file for current bot
	$bot_lock = fopen("locks/$bot_full_name.lock", "w");
	if ($bot_lock) fwrite($bot_lock, "lock");
	fclose($bot_lock);
	
	// let the bot wait for incoming messages in an infinite loop.
	// the Handler_message_chat and Handler_message_normal methods will
	// deal with the actual incoming messages. the bot can be shutdown
	// by sending it the message "die <bot password>"
	LogEvent($bot_log, "Bot is online, listening for incoming messages: $bot_full_name");
		
	// return to ajax script calling this script, yet continue to execute it.
	header("HTTP/1.0 204 No Response");
	flush(); 
	
	$JABBER->SendPresence(NULL, NULL, "online");
	while (true) {
		
		set_time_limit(0);
		
		$JABBER->CruiseControl(5);
		
		// debug: log a keep alive signal
		if (DEBUG) LogEvent($bot_log, "debug: keep alive");
		
		// shutdown bot as soon as the lock file is gone
		if (!file_exists("locks/$bot_full_name.lock")) stopBot();
		
	}
} catch (Exception $e) { 
	// error occured, shut down bot
	header("HTTP/1.1 404 Not Found");
	flush();
	LogEvent($error_log, "Error occured: {$e->getMessage()}. ");
	LogEvent($bot_log, "Shutdown request due to error");
   	stopBot();
   	LogEvent($bot_log, "Bot shutdown completed");
	}

function stopBot() {
	try {
		global $SM;
		global $settings;
		global $bot_log;
		global $error_log;
		global $JABBER;
		global $bot_full_name;
		
		// return bot failure to AJAX call
		header("HTTP/1.1 404 Not Found");
		flush();
		
		LogEvent($bot_log, "Shutting down bot: $bot_full_name");
		
		// stop the service
		LogEvent($bot_log, "Stopping service: $bot_full_name");
		if ($SM) $SM->stopService();
		LogEvent($bot_log, "Service stopped: $bot_full_name");
		
		// disconnect bot
		LogEvent($bot_log, "Disconnecting bot: $bot_full_name");
        if ($JABBER) $JABBER->DisConnect();
        LogEvent($bot_log, "Disconnected bot: $bot_full_name");
        
        // remove lock file, if it exists
        if (file_exists("locks/$bot_full_name.lock")) unlink("locks/$bot_full_name.lock");
        
        // close log files
        if ($bot_log) fclose($bot_log);
        if ($error_log) fclose($error_log);
    	exit();
	} catch(Exception $e) { exit(); }
}

function Handler_message_chat($packet) {
	Handler_message_normal($packet);
	}
	
function Handler_message_normal($packet) {

	global $JABBER;
	global $SM;
	global $bot_log;
	global $error_log;
	global $settings;
	
	try {
	 
		// get the from address from the message
    	$from = $JABBER->GetInfoFromMessageFrom($packet);
    	$jid = $JABBER->StripJID($from);
    	// get the message, but replace HTML chars that conflict with XML
    	$body = $JABBER->GetInfoFromMessageBody($packet);
    
    	// shutdown bot if user sends "die <bot password>"
    	if ($body == "die " . $settings["bot_password"]) {
    		LogEvent($bot_log, "Shutdown request from $from received");
    		stopBot();
    		LogEvent($error_log, "Bot shutdown completed");
    	}
    	// return help message of bot if user sends "?"
    	elseif ($body == "?") {
    		LogEvent($bot_log, "Help request from $from received");
        	$JABBER->SendMessage($from, "normal", NULL, array("body" =>htmlentities($SM->getServiceHelp())));
        	LogEvent($bot_log, "Help response to $from sent");
    	}
    	// all other messages, let the bot implementation deal with it
    	else {
    		LogEvent($bot_log, "Request from $from received: $body");
    		$response = $SM->callService($body,$from);
			if ($response) {
	        	$JABBER->SendMessage($from, "normal", NULL, array("body" =>htmlentities($response)));
    	    	LogEvent($bot_log, "Response to $from sent: $response");
			} else {
				LogEvent($bot_log, "Bot executed. This bot has not defined a response");
			}
    	}
	} catch (Exception $e) {
		// error occured in message, log it but keep bot online
		LogEvent($error_log, "Error occured: {$e->getMessage()}. ");
		} 
}

function Handler_presence_subscribe($packet) 
{ 
    global $JABBER; 
    global $bot_log;
    $jid = $JABBER->StripJID($JABBER->GetInfoFromPresenceFrom($packet));
    $from = $JABBER->GetInfoFromPresenceFrom($packet);
    $JABBER->SubscriptionAcceptRequest($from);
    $JABBER->Subscribe($jid);
    LogEvent($bot_log, "Subscription request from $jid approved");
}

function Handler_presence_subscribed($packet)
{
    global $JABBER;
    global $bot_log;
    $jid = $JABBER->StripJID($JABBER->GetInfoFromPresenceFrom($packet));
    $from = $JABBER->GetInfoFromPresenceFrom($packet);
    $JABBER->Subscribe($jid);
    LogEvent($bot_log, "Subscription allowed by $from");
}

function Handler_presence_unsubscribe($packet)
{
    global $JABBER; 
    global $bot_log;
    $JABBER->SendPresence("unsubscribed", $from);
    $JABBER->RosterUpdate();
    LogEvent($bot_log, "Unsubscribe from $from's presence");
}

function Handler_presence_unsubscribed($packet)
{
    global $JABBER;
    global $bot_log;
    $from = $JABBER->GetInfoFromPresenceFrom($packet);
    $JABBER->RosterUpdate();
    LogEvent($bot_log, "Unsubscribed from $from's presence");
} 