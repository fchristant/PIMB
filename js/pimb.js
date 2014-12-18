/***************************************************************************

	pimb.js
	This file contains all the PIMB javascript logic
	@author Ferdy Christant <www.ferdychristant.com>
	@copyright Copyright &copy; 2007, Ferdy Christant
    @abstract
  
 ***************************************************************************/

window.onerror = handleError; // generic error handling

var botspymax;						// holds the max lines of the bot log screen
var play = 1;						// start live log by default
var logids = new Array(0);			// holds unique identifiers of log entries
var logmessages = new Array(0);		// holds messages of published log entries
var logpushed = new Array(0);		// holds unique identifiers of published log entries
var bot_file;						// bot file of the current bot we're working with

function initialize(page) {
	// initializes the page
	if (page == 'admin') {
		// do not show the bot details panel until the user selected one
		hideBlock('bot_details');
	} else if (page == 'log') {
		// get bot file from the URL
		bot_file = getURLParam('file');
		// load the bot details panel for the specified bot
		getBot();
		// get the XML stream of log messages for this bot
   		getLogXML(true);
	}
}

function setBot(selected) {
	
	// retrieves bot file of currently selected
	// bot and update the buttons so that only
	// relevant actions are enabled
	bot_file = selected.value;
	updateBotButtons();
	getBot();
}

function updateBotButtons() {
	
	// this function toggles the button's
	// ability to be clickable, depending
	// on the status of the selected bot
	form = document.panel;
	var bot_status = document.getElementById('status_' + bot_file);
	bot_status = bot_status.innerHTML

	if (bot_status.toUpperCase()=="ONLINE" || bot_status.toUpperCase()=="STARTING...") {
		// selected bot is online or starting, disable irrelevant buttons in this state
		disableButton(form.btnStart);
		enableButton(form.btnStop);
	}
	else {
		// selected bot is offline, only enable start button
		enableButton(form.btnStart);
		disableButton(form.btnStop);
	}
}

function enableButton(button) {
	// changes look & feel of a disabled button to be enabled
	button.className = "button_enabled";
}

function disableButton(button) {
	// changes look & feel of a enabled button to be disabled
	button.className = "button_disabled";
}

function isButtonEnabled(button) {
	// returns true if button is enabled, false if disabled
	var enabled = false;
	if (button.className == "button_enabled")
		enabled = true;
	return enabled;
}

function startBot() {
	
	// tries to start the currently selected bot
	// first, get a handle to status container of the current bot
	var bot_status = document.getElementById('status_' + bot_file);
	// update its status text and the buttons status
   	bot_status.innerHTML = 'starting...';
   	bot_status.className = 'bot_starting';
   	updateBotButtons();
   	
   	// try to start the selected bot by invoking a remote call
   	var xmlhttp =  new XMLHttpRequest(); 
	xmlhttp.open('GET', 'startbot.php?bot=' + bot_file + '&ms=' + new Date().getTime(), true);        
	xmlhttp.onreadystatechange = function() { 
       	if (xmlhttp.status != 200 && xmlhttp.status != 204 && xmlhttp.status != 1223) {
       		// failed to start bot, update status and buttons
       		bot_status.innerHTML = 'error during startup, check the log.';
       		bot_status.className = 'bot_offline';
       		updateBotButtons();
       		}
       	else {
       		// bot is online, update status and buttons
       		bot_status.innerHTML = 'online';
       		bot_status.className = 'bot_online';
       		Effect.Pulsate('status_' + bot_file);
       		updateBotButtons();
       	}
    } 
	// Send the POST request 
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); 
	xmlhttp.send(null); 
	
}

function stopBot() {

   	// try to stop the selected bot by invoking a remote call
	var bot_status = document.getElementById('status_' + bot_file);
   	var xmlhttp =  new XMLHttpRequest(); 
	xmlhttp.open('GET', 'stopbot.php?bot=' + bot_file + '&ms=' + new Date().getTime(), true);        
	xmlhttp.onreadystatechange = function() { 
    if (xmlhttp.readyState == 4) {
        // only if "OK"
        if (xmlhttp.status == 200) {
        	// bot is offline, update status and buttons
       		bot_status.innerHTML = 'offline';
       		bot_status.className = 'bot_offline';
       		updateBotButtons();
       		}
       	else {
       		// failed to stop bot, update status and buttons
       		bot_status.innerHTML = 'error during shutdown. see log for details';
       		bot_status.className = 'bot_online';
       		updateBotButtons();
       	}
    }
	}
	// Send the POST request 
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); 
	xmlhttp.send(null); 
	
}

function getBot() {
	
	// tries to get the configuration settings from the currently
	// selected bot by making a remote call
   	var xmlhttp =  new XMLHttpRequest();  
	xmlhttp.open('GET', 'getbot.php?bot=' + bot_file + '&ms=' + new Date().getTime(), true);        
	xmlhttp.onreadystatechange = function() { 
	if (xmlhttp.readyState == 4) {
     // only if "OK"
        if (xmlhttp.status == 200) {
       		// bot settings retrieved, parse config values from response XML
       		// and print them to the settings panel
       		var settings = xmlhttp.responseXML.documentElement;
       		
       		var bot_name = settings.getElementsByTagName('bot_name')[0].firstChild.nodeValue;
       		document.getElementById('bot_name').innerHTML = bot_name;
       		var implementation_file = settings.getElementsByTagName('implementation_file')[0].firstChild.nodeValue;
       		var image = settings.getElementsByTagName('image')[0].firstChild.nodeValue;
       		document.getElementById('image').innerHTML = '<img src="' + implementation_file.substr(0,implementation_file.lastIndexOf("/")+1) + image + '" width="100" height="100">';
       		var description = settings.getElementsByTagName('description')[0].firstChild.nodeValue;
       		document.getElementById('description').innerHTML = description;
       		var author = settings.getElementsByTagName('author')[0].firstChild.nodeValue;
       		document.getElementById('author').innerHTML = author;
       		var version = settings.getElementsByTagName('version')[0].firstChild.nodeValue;
       		document.getElementById('version').innerHTML = version;
       		var bot_user = settings.getElementsByTagName('bot_user')[0].firstChild.nodeValue;
       		document.getElementById('bot_user').innerHTML = bot_user;
       		var log_file = settings.getElementsByTagName('log_file')[0].firstChild.nodeValue;
       		if (log_file != "-none-")
       			document.getElementById('log_file').innerHTML = '<a href="viewlog.php?file=' + bot_file + '">' + log_file + '</a>';
       		else 
       			document.getElementById('log_file').innerHTML = log_file;
       			
       		// values are all set, display the panel (if it wasn't visible already)
       		showBlock('bot_details');
       		
       	}
    } 
	}
	// Send the POST request 
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); 
	xmlhttp.send(null); 
	
}

function viewLog(log) {
	// opens page that shows the log of the specified file
	location.href = 'viewlog.php?file=' + log;
}

function clearLog(log) {
	// delete the log file associated with the current bot
	if (confirm('This will delete the full log history of the bot from the server. Please make sure that your bot is stopped before you delete the log, otherwise remaining messages of the bot will not be recorded in the log. Do you want to continue?')) {
		pauseBotSpy();
		var log_content = document.getElementById("log_content");
		log_content.innerHTML = '<span id=\'log_delete\'>Deleting log from server...</span>';
		Effect.Pulsate('log_delete', {duration: 5.0});
		var xmlhttp =  new XMLHttpRequest(); 
		xmlhttp.open('GET', 'clearlog.php?bot=' + bot_file + '&lines=' + botspymax + '&ms=' + new Date().getTime(), true);        
		xmlhttp.onreadystatechange = function() { 
    	if (xmlhttp.readyState == 4) {
        	// only if "OK"
        	if (xmlhttp.status == 200) {
       			log_content.innerHTML = 'Log deleted.'
       		}
       	
    	}
	}
	// Send the POST request 
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); 
	xmlhttp.send(null); 
	}
}

function showBlock(control) {
	// show specified block
	Element.setOpacity(control, 0.0);
	Effect.Appear(control, { duration: 1.0 });
}

function hideBlock (control) {
	// hide specified block
	Element.setOpacity(control, 0.0);
}

function pauseBotSpy() {
	// pause the live log
    var playimg = document.getElementById("bsplay");
    var pauseimg = document.getElementById("bspause");
    playimg.src = "img/icon_play_up.gif";
    playimg.alt = "Click to Play";
    pauseimg.src = "img/icon_pause.gif";
    pauseimg.alt = "Paused...";
    pause();
}
        
function playBotSpy() {
	// play the live log
    var playimg = document.getElementById("bsplay");
    var pauseimg = document.getElementById("bspause");
    playimg.src = "img/icon_play.gif";
    playimg.alt = "Playing...";
    pauseimg.src = "img/icon_pause_up.gif";
    pauseimg.alt = "Click to Pause";
    resume();
}
        
function pause() {
	// toggle pause
    play = 0;               
}       

function resume() {
	// toggle play
    play = 1;
}
                 

function getLogXML(init) {
	
	// read xml from the log of the current bot, and push the results to the screen
	var found = false;
   	var xmlhttp =  new XMLHttpRequest();  
	xmlhttp.open('GET', 'getlog.php?bot=' + bot_file + '&lines=' + botspymax + '&ms=' + new Date().getTime(), true);        
	xmlhttp.onreadystatechange = function() { 
	if (xmlhttp.readyState == 4) {
     // only if "OK"
        if (xmlhttp.status == 200) {
       		// log XML retrieved succesfully
       		var log;
        	var line;

        	try {
        		log = xmlhttp.responseXML.getElementsByTagName("log")[0];
        		if (log) {
        			lines = log.getElementsByTagName("line");
        			if (lines)
        				found = true;
        		}
        	}
        	catch (e) {
                setTimeout("getXML()", 10000);
                return;
        	}

        	if (found) {
        		for (var i = 0; i < lines.length; i++) {
                	try {
                		id = lines[i].getElementsByTagName("id")[0].firstChild.nodeValue;
                		message = lines[i].getElementsByTagName("message")[0].firstChild.nodeValue;
                		if (logpushed.indexOf(id)<0) {
                        	logids[i] = id;
                        	logmessages[i] = htmlEntities(message);
                		}
                	}
                	catch (e) {
                        logids[i] = "000000";
                        logmessages[i] = "------";
                	}
        		} 
        	}
        	
			if (init) {
				// draw the results on screen
				var log_content = document.getElementById("log_content");
				var log_html = '';
				
				if (found) {
				
					for (i = 1; i < botspymax+1; i++) {
						if (logmessages[i-1]) {		
							log_html = log_html + '<span id=\'line-' + i + '\'>' + logmessages[i-1] + '</span><br/>';
							logpushed[i-1] = logids[i-1];
						}
						else
							log_html = log_html + '<span id=\'line-' + i + '\'>&nbsp;</span><br/>';
					}
				
					logids.splice(0,logids.length);
					logmessages.splice(0,logmessages.length);
					log_content.innerHTML = log_html;
				} else log_content.innerHTML = 'Log file is empty or does not exist';
			}
			
			push();
       }
    } 
	}
	// Send the POST request 
	xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); 
	xmlhttp.send(null); 
	
}

function push() {
// keep pushing messages to the live log
    if (play == 0) {
	    setTimeout("push()", 1000);
        return;
    }
       	
    var cell;
    var cellnext;
    var text;
    var style = "";
    var id = logids.pop();
    var message = logmessages.pop();
      
    if (id) {
    	if (logpushed.indexOf(id)<0) {

        	Element.setOpacity('line-1', 0.0);

        	for (i = (botspymax - 1); i >= 1; i--) {
            	cell = document.getElementById("line-" + i);
                cellnext = document.getElementById("line-" + (i + 1));
                if (cell.innerHTML != "") {
                	cellnext.innerHTML = cell.innerHTML;
                }
        	}

        	document.getElementById("line-1").innerHTML = message;
        	logpushed[logpushed.length] = id;	
    
			if (logpushed.length > 2*botspymax)
				logpushed.splice(0,1)

       		Effect.Appear('line-1', { duration: 1.5 });
      	}
      }
        
      if (logids.length > 0) {
      	setTimeout("push()", 2000);
       }
       else {
       		setTimeout("getLogXML(false)", 5000);
        }
}
		
function getURLParam(name) {
	// utility function to extract a variable from the URL
  	var regexS = "[\\?&]"+name+"=([^&#]*)";
  	var regex = new RegExp( regexS );
  	var tmpURL = window.location.href;
  	var results = regex.exec( tmpURL );
  	if( results == null )
    	return "";
  	else
    	return results[1];
}

function htmlEntities(text) {
	// utility function to encode HTML characters
	var result = '';
  	for (var i = 0; i < text.length; i++){
    	var c = text.charAt(i);
    	result += {'<':'&lt;', '>':'&gt;', '&':'&amp;', '"':'&quot;'}[c] || c;
  	}
  return result;
}

function handleError()
// this is needed to hide a IE6 Javascript bug
{ return true; }