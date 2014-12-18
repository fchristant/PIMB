<?php

/***************************************************************************

	class.servicemanager.php
	This script is a factory class that dynamically instantiates
	bots and call its methods
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

class ServiceManager {
	
	private $service;
	private $settings;
	
	public function __construct($bot_file) {
		
		// check if the bot file exists
        if (!file_exists($bot_file)) { 
        	// bot file not found
            throw new Exception("Requested bot does not exist at $bot_file", 1); 
        } 
        	
        // get the settings of the bot
        $bot_config_file = preg_replace("/(.bot.php)/i",".ini.php",$bot_file);
        require_once($bot_config_file); 
        global $settings;
        $this->settings = $settings;
        
        // bot file found, include it
        require_once($bot_file); 
        
        // check if the specified bot class exists in the bot file
        if (!class_exists($this->settings["class_name"])) { 
        	// class not found in bot file
           	throw new Exception("Requested class does not exist at $bot_file.", 1); 
	    } 

	    // check if the class implements the service interface
    	$class = new ReflectionClass($this->settings["class_name"]); 
        if (!$class->implementsInterface(new ReflectionClass('Service'))) { 
        	// bot class does not implement service interface	
            throw new Exception("Bot class {$this->settings["class_name"]} found,
             but it does not implement the service interface",1); 
        } 
	}
	
	public function get_Settings() {
		// returns the array of bot settings of the current bot
		return $this->settings;
	}
	
	public function startService() { 
	
    	try {    
			if ($this->service) return true; // service already loaded
    
			// dynamically create an instance of the bot class, and call its start method
        	$class = new ReflectionClass($this->settings["class_name"]); 
        	$this->service = $class->newInstance(); 
        	$this->service->Start();
        	return true;
        
	    } catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}
	
	public function stopService() {
		// dynamically call the stop method of the bot
		try {
			if ($this->service) {
				$this->service->Stop();
				unset($this->service);
			}
		} catch(Exception $e) {
    		throw new Exception($e->getMessage(),$e->getCode());
    	}
	}
	
	public function callService($arg,$from) {
		// dynamically call the bot logic
		try {
			if (!$this->service)
    			// service not yet loaded, load it
    			if (!$this->startService())
    				//service failed to load, throw error
    				throw new Exception("unable to load service {$this->settings["class_name"]}");
    				
    		// service is loaded. call it.
        	return $this->service->Call($arg,$from);
			} 
		catch(Exception $e) {
    			return "an error occured while calling service $service : {$e->getMessage()}";
    		}
	}
		
	public function getServiceHelp() {
		// dynamically get the bot help
		try {
			if (!$this->service)
    			// service not yet loaded, load it
    			if (!$this->startService())
    				//service failed to load, throw error
    				throw new Exception("unable to load service {$this->settings["class_name"]}");
    				
    		// service is loaded. get the help
        	return $this->service->Help();
			} 
		catch(Exception $e) {
    			return "an error occured while calling service $service : {$e->getMessage()}";
    		}
	}
}