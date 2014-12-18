<?php

/***************************************************************************

	*.bot.php
	This script contains the implementation of your bot
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  

/***************************************************************************

                   ===== YOUR BOT IMPLEMENTATION =====

***************************************************************************/

// load interface definition
require_once("inc/class.service.php");

// load interface definition, required for all bots
class Parrot implements Service {
	
	public function Start() {
		// initialization code. is ran when the bot is started
	}
	
	public function Stop() {
		// cleanup code. is ran when the bot is killed
	}
	
	public function Help() {
		// help text to return when users send the help command (?) to this bot
		return "The parrot bot is a simple bot that repeats all messages that you send to it. It is mostly used 
to demonstrate PIMB. Syntax:\n
? = retrieve bot help text\n
message = text to return to you";
	}
	
	public function Call($arg, $from) {
		// logic that is ran when users send a message to your bot. 
		// $arg contains the message that was sent to this bot
		// $from is the sender ID
		// anything you return in this function will be send back to $from
		try {
			// extract recipient name from IM address
			$recipient = substr($from, 0, strpos($from, "@"));
			return "hi $recipient, you said: " . $arg;
		} catch (Exception $e) {
			// error occured
			return "error: invalid statement";
		} 
	}
}
?>