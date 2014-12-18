<?php

/***************************************************************************

	class.botmanager.php
	This script contains the logic that manages bots
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

class BotManager {
	
	private $basepath = "";
	
	function __construct($basepath) {
       $this->basepath = $basepath;
   }
	
	public function getAllBots() {
		// get all bot settings by scanning the PIMB installation for files ending
		// with .ini.php. For each bot, add the settings to an array
		try {
			$result = array();
			$files = $this->scanDirectory("{$this->basepath}",true,"bot.php");
			for ($i=0;$i<sizeof($files);$i++) {
				$result[$files[$i]] = $this->getBotSettings($files[$i]);
			}
			return $result;
		} catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}
	
	public function getBotSettings($bot_file) {
		// get the bot settings of $bot_file
		try {
			$bot_config_file = preg_replace("/(.bot.php)/i",".ini.php",$bot_file);
			require_once($bot_config_file);
			global $settings;
			return $settings;
		} catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}
	
	public function getBotStatus($bot_user) {
		try {
			// get online status of bot, based on existence of lock file
			// if a lock file exists, the bot is running
			return (file_exists("locks/$bot_user.lock"))?true:false;
		} catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}

	private function scanDirectory($directory, $recursive, $extension) {
		// utility function to scan a directory recursively for a certain extension
		try {
			$array_items = array();
			if ($handle = opendir($directory)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if (is_dir($directory. "/" . $file)) {
							if($recursive)
								$array_items = array_merge($array_items, $this->scanDirectory($directory. "/" . $file, $recursive,$extension));
						} else {
							$file = $directory . "/" . $file;
							if ($extension=="") {
								$array_items[] = $file;
							}
							else {
								if (preg_match("/{$extension}$/", $file,$matches)) {
									$array_items[] = $file;
								}
							}
						
						}
					}
				}
			closedir($handle);
			}
			return $array_items;
			} catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}
	
}