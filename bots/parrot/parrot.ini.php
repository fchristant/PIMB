<?php

/***************************************************************************

              ===== YOUR BOT CONFIGURATION SETTINGS =====

***************************************************************************/

global $settings;
$settings = array(
"server" => "jabber.com", 									// jabber server where bot user is registered
"bot_name" => "Parrot Bot",									// short descriptive name of bot
"bot_user" => "your_bot_user",							// bot user name
"bot_password" => "your_bot_password",								// bot password
"class_name" => "Parrot",									// name of bot class implementation, case-sensitive!
"resource" => "bot",										// jabber resource type, leave as "bot"
"port" => 5222,												// port used for messaging
"logging" => true,											// log bot events to file
"log_file" => "yourlog.log", 							// name of log file, do not include a path
"author" => "Ferdy Christant",								// author of the bot code
"version" => "v1.00",										// version number of the bot code
"image" => "bot.gif",										// name of bot image file, do not include a path
"description" => "The parrot bot is a simple bot that repeats all messages that you send to it. It is mostly used 
to demonstrate PIMB. Syntax:<br><br>
? = retrieve bot help text<br>
message = text to return to you
"
);