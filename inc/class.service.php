<?php

/***************************************************************************

	class.service.php
	This interface defines the methods that all bots must implement
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
    @abstract
  
 ***************************************************************************/

interface Service {
	public function Start();
	// will be called when the service is first called. This is an ideal place
	// to initialize a resource, such as a database connection. 
	public function Stop();
	// will be called when the bot is shutdown. This is the place to close
	// any open resources, such as files and database connections
	public function Help();
	// will be called when users supply the help command to your bot.
	// this method must return a help string that explains how to use
	// your service. you can use \n to return multiple lines of text
	public function Call($arg, $from);
	// will be called each time a message is received by your bot.
	// $arg will contain the message that was sent. the return
	// value of this method will be sent as an answer to the
	// sender of the message
}
?>