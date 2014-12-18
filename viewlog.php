<?php

/***************************************************************************

	viewlog.php
	This script displays the live log to the user
	@copyright Copyright &copy; 2007, Ferdy Christant
  
 ***************************************************************************/

// load PIMB config settings
require_once("pimb.conf.php");
// load bot manager engine
require_once("inc/class.botmanager.php");
// load util functions
require_once("inc/utils.php");

$page_title = "PIMB live log panel (" . VERSION . ")";
$page_id = "log";

// load HTML modules specific for this display page
require_once("inc/html_header.php");
require_once("inc/html_log_introduction.php");
require_once("inc/html_col1_2.php");
?>

<form name="panel">
	
<script language="javascript">
	botspymax = <?=MAX_LOG?>;
</script>
	<h1>PIMB Log panel</h1>
	<table width="100%" cellspacing="0">
	<thead>
	<th width="100">log settings</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</thead>
	<tr>
	<td width="100">Select log</td>
	<td>
	<select name="bots">
	<option value="">- General error Log -</option>";
	<?php
	// get the log file that was passed by the URL
	$bot_current = $_GET["file"];
	
	// get the list of available bots
	$BM = new BotManager("bots");
	$bots = $BM->getAllBots();
		
	foreach ($bots as $file => $bot) {
		if ($bot_current == $file)
			echo "<option value=\"$file\" selected=\"selected\">{$bot["bot_name"]}</option>";
		else
 			echo "<option value=\"$file\">{$bot["bot_name"]}</option>";
	}
	?>
	</select>
	</td>
	<td width="280">
	<button value="view" name="btnView" type="button" class="button_enabled" 
	onClick="if(isButtonEnabled(this)) viewLog(document.panel.bots.options[document.panel.bots.selectedIndex].value); else return false;">
	View log</button>
	<button value="clear" name="btnClear" type="button" class="button_enabled" 
	onClick="if(isButtonEnabled(this)) clearLog(document.panel.bots.options[document.panel.bots.selectedIndex].value); else return false;">
	Clear log</button>
	<button value="main" name="btnMain" type="button" class="button_enabled" 
	onClick="if(isButtonEnabled(this)) location.href='.'; else return false;">
	Back to main</button>
	</td>
	</tr>
	</table>
	
	<br/>
	
	<table width="100%" cellspacing="0">
	<thead><th>log messages</th></thead>
		
	<tr><td>
	<img id="bsplay" src="img/icon_play.gif" onclick="playBotSpy();">
	<img id="bspause" src="img/icon_pause_up.gif" onclick="pauseBotSpy();">
	</td></tr>
	<tr><td id="log_content">
	</td></tr>
	</table>
	
</form>

<?php 
require_once("inc/html_col2_3.php");
require_once("inc/html_bot_details.php");
require_once("inc/html_footer.php");
?>