<?php

/***************************************************************************

	index.php
	PIMB main file
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

// load PIMB config settings
require_once("pimb.conf.php");

// load the bot engine classes
require_once("inc/class.botmanager.php");
require_once("inc/class.servicemanager.php");

$page_title = "PIMB administration panel (" . VERSION . ")";
$page_id = "admin";

// load the HTML modules that are specific to this page
require_once("inc/html_header.php");
require_once("inc/html_introduction.php");
require_once("inc/html_help.php");
require_once("inc/html_col1_2.php");
?>

<form name="panel">
<h1>PIMB administration panel</h1>
	<?php
	// get the list of available bots
	$BM = new BotManager("bots");
	$bots = $BM->getAllBots();

	if (sizeof($bots)>0) {
		
		// loop through available bots and print details in a table
		echo 
		"<table width=\"90%\" cellspacing=\"0\">
		<thead>
		<th width=\"30\">&nbsp;</th>
		<th width=\"150\">bot name</th>
		<th>account</th>
		<th width=\"150\">status</th>
		</thead>";
		
		foreach ($bots as $file => $bot) {
			
			$bot_full_name = $bot["bot_user"] . "@" . $bot["server"];
			if ($BM->getBotStatus($bot_full_name)==false)
				$bot_status_text = "offline";
			else
				$bot_status_text = "online";
			
 			echo 
 			"<tr>
 			<td><input type=\"radio\" name=\"bot\" value=\"$file\" onclick=\"setBot(this);\"></td>
 			<td>{$bot["bot_name"]}</td>
 			<td>$bot_full_name</td>
 			<td id=\"status_$file\" class=\"bot_$bot_status_text\">$bot_status_text</td>
 			</tr>";
		}
		
		echo "</table>"
		?>
	
	<br/>
	
	<button value="start" name="btnStart" type="button" class="button_disabled" 
	onClick="if(isButtonEnabled(this)) startBot(); else return false;">
	Start</button>
	
	<button value="stop" name="btnStop" type="button" class="button_disabled" 
	onClick="if(isButtonEnabled(this)) stopBot(); else return false;">
	Stop</button>

	<?php
	} else { 
		// no bots found
		echo "No bots found.";
	}
	?>
	</form>


<br/>
<br/>
<?php 
require_once("inc/html_col2_3.php");
require_once("inc/html_bot_details.php");
require_once("inc/html_footer.php");
?>
