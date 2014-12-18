<?php

/***************************************************************************

	utils.php
	This file contains utility functions called by various parts of PIMB
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
    @abstract
  
 ***************************************************************************/

function LogEvent($handle, $message) {
	// write event to log file
	if ($handle) {
		$timestamp = strftime("%Y/%m/%d %H:%M:%S");
		fwrite($handle, "$timestamp : $message \n");
	}
}



function readLinesEnd($file, $lines) {
	// read the last x lines from the specified file and return
	// it as an array
	$handle = fopen($file, "r");
	$linecounter = $lines;
	$pos = -2;
       $beginning = false;
       $text = array();
       while ($linecounter > 0) {
         $t = " ";
         while ($t != "\n") {
           if(fseek($handle, $pos, SEEK_END) == -1) {
				$beginning = true; break; 
           }
           $t = fgetc($handle);
           $pos --;
         }
         $linecounter --;
         if($beginning) rewind($handle);
         $text[$lines-$linecounter-1] = fgets($handle);
         if($beginning) break;
       }
       fclose ($handle);
       return $text;
}

function getLogXML($file, $lines) {
	// return the last x lines from the specified file as XML
	$result = "";
	$log_lines = readLinesEnd($file,$lines);
	if ($log_lines) {
		$result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n
		<log>";
		foreach ($log_lines as $line) {
			$result = $result .
			 "<line>\n
			  <id>" . md5($line) . "</id>\n
			  <message>" . htmlentities($line) . "</message>\n
			  </line>\n";
		}
		$result = $result . "</log>";
	}
	return $result;
}

?>