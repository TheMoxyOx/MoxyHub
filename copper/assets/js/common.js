/* IE6: Turn on image caching
 * Stops image flicker in Scriptculous effects
 *********************************************/
(function(){
	/*Use Object Detection to detect IE6*/
	var m = document.uniqueID /*IE*/
	&& document.compatMode /*>=IE6*/
	&& !window.XMLHttpRequest /*<=IE6*/
	&& document.execCommand ;
	try{
		if(!!m){
			m("BackgroundImageCache", false, true) /* = IE6 only */
		}
	}catch(oh){};
})();

/* isIE
 * Return true if user agent is IE
 *********************************************/
function isIE() { return cu.browser.isIE(); }

/* DASH
 ************************/
var dashOpen = false; // boolean so we know whether to open the dash or close it
var dashTweening = false; // boolean so we can disable toggling while animation is is progress
function toggleDash(){
	if (dashTweening == false){
		if (dashOpen==false){
			dashOpen = true;
			Effect.BlindDown(
				'dash-content',
				{
					beforeStart: function() {
						dashTweening = true;
					},
					afterFinish: function() {
						dashTweening = false;
					},
					duration:0.5
				}
			);
			$$('.dash-toggler').each(function(el){
				el.innerHTML='Hide dash';
				el.removeClassName('showTheDash');  
				$('clearTheDash').addClassName('active');
			});
		} else {
			dashOpen = false;
			Effect.BlindUp(
				'dash-content',
				{
					beforeStart: function() {
						dashTweening = true;
					},
					afterFinish: function() {
						dashTweening = false;
					}
					,
					duration:0.5
				}

			);
			 $$('.dash-toggler').each(function(el){
				el.innerHTML='Show dash';
				el.addClassName('showTheDash');  
				$('clearTheDash').removeClassName('active');				
			});
		}
	}

}
/* STOPWATCH TOGGLE
 ************************/
var stopwatch = {
	isTimerActive: false,
	startTime: 0,
	_hours:0, _mins:0, _secs:0,
	_tweening: false,
	_open: false,

	_getPauseLink: function(){return $('sw-pause-btn'); },
	_getTimerBlock: function(){ return $('stopwatch-timer'); },
	_getUserBlock: function(){ return $('stopwatch-username'); },
	_getTimerOptsBlock: function(){ return $$('.task-opts')[0]; },
	_getUserOptsBlock: function(){ return $$('.user-opts')[0]; },
	_getToggler: function(){ return $('stopwatch-toggler'); },
	_getIsActive: function(){ return this._getTimerBlock().hasClassName('active'); },
	_getIsPaused: function(){ return this._getPauseLink().innerHTML == Copper.language.msgResume },
	_getStopwatchBlock: function(){return $('stopwatch');},
	initTime: function(timestr){
		if((timestr)&&(timestr.length>0)){
			var parts = timestr.split(":");
			this._hours = parseInt(parts[0]);
			this._mins = parseInt(parts[1]);
			this._secs = parseInt(parts[2]);
		}
	},
	setup: function(){
		var timerToggleElem = this._getTimerBlock();
		if (!timerToggleElem) { return; }
		this.isTimerActive = this._getIsActive();
		this.timer._paused = this._getIsPaused();
		if(this.isTimerActive){
			this.showTimer();
			this.timer.init();
		} else {
			this.showUser();
		}
	},
	togglePause: function(){ this.timer.togglePause(); },
	toggleStopwatch: function(){
		if(this._open){
			this.closeStopwatch();
		} else {
			this.openStopwatch();
		}
	},

	newTimerPrompt: function(projectID, taskID, projectName, taskName){
			// no prompt until we can support languages as well as variable dialog options.
			// for now, just start it.
			stopwatch.newTimer(projectID, taskID, projectName, taskName);
		// if(stopwatch.isTimerActive){
		//	 modalDialog.show("New Timer", "Create a new timer?", function(){stopwatch.newTimer(projectID, taskID, projectName, taskName);}, function(){});
		// } else {
		//	 stopwatch.newTimer(projectID, taskID, projectName, taskName);
		// }
	},
	newTimer: function(projectID, taskID, projectName, taskName){
		var timer = stopwatch.timer;
		var callbackFunction = function(){
			$('swProjectID').value = projectID;
			$('swTaskID').value = taskID;
			stopwatch.timer.resetAll();
			stopwatch.timer.init();
			stopwatch.timer.start();
			var projectUrl = 'index.php?module=projects&action=taskview&projectid='+projectID+'&taskid='+taskID;
			var taskUrl = 'index.php?module=springboard&action=taskview&projectid='+projectID+'&taskid='+taskID;
			$('swProjectName').innerHTML = projectName;
			$('swTaskName').innerHTML = taskName;
			$('swProjectName').setAttribute('href', projectUrl);
			$('swTaskName').setAttribute('href', taskUrl);
			$('stopwatch-timer').innerHTML = '<span></span>';
			$('sw-pause-btn').innerHTML = Copper.language.msgPause;

			// When there are no timers, the username will be shown instead. The user then clicks New Timer,
			// and we have to hide the username and show the stopwatch.
			if (stopwatch.isTimerActive) {
				stopwatch.showTimer();
			}
			timer.update();
		}

		stopwatch.setStopwatchMode(true);
		callbackFunction.apply(stopwatch, []);
		return false;
	},
	cancelTimer: function(){
		this.timer.resetAll();
		this.setStopwatchMode(false);
		this.timer.remove();
	},
	_numberFormat:function(numArr){
		var numRet = [];
		numArr.each(function(num){
			var numStr = String(num);
			if(numStr.length==1){ numStr = "0"+num; }
			numRet.push(numStr)
		})
		return numRet;
	},
	_getTime: function(){
		var parts = [];
		if(this._hours>0){
			parts = this._numberFormat([this._hours,this._mins,this._secs]);
		} else if(this._mins>0){
			parts = this._numberFormat([this._mins,this._secs]);
		} else if(this._secs>0){
			parts = this._numberFormat([this._secs]);
		}
		return parts.join(":");
	},
	_getFullTime: function(){
		parts = this._numberFormat([this._hours,this._mins,this._secs]);
		return parts.join(":");
	},
	_setVisibleTime: function(){
		if(!this._tweening){
			this._getTimerBlock().innerHTML = '<span>'+this._getTime()+'</span>';
			this.updateTogglePosition();
		}
	},

	setStopwatchMode: function(isTimer){
		this.isTimerActive = isTimer;
		if(isTimer){
			this.showTimer();
		} else {
			this.showUser();
		}
	},
	show: function(arr){
		arr.each(function(item){ item.setStyle({display:"block"}); })
	},
	hide: function(arr){
		arr.each(function(item){ item.setStyle({display:"none"}); })
	},
	showTimer: function(){
		this.isTimerActive = true;
		this.show([this._getToggler(), this._getTimerOptsBlock(), this._getTimerBlock()]);
		this.hide([this._getUserOptsBlock(), this._getUserBlock()]);
		$('stopwatch-toggler').removeClassName("stopwatch-toggler-user");
		$('stopwatch-toggler').addClassName("stopwatch-toggler-timer");
		this.updateTogglePosition();
	},
	showUser: function(){
		this.isTimerActive = false;
		this.hide([this._getTimerOptsBlock(), this._getTimerBlock()]);
		this.show([this._getToggler(), this._getUserOptsBlock(), this._getUserBlock()]);
		$('stopwatch-toggler').removeClassName("stopwatch-toggler-timer");
		$('stopwatch-toggler').addClassName("stopwatch-toggler-user");
		this.updateTogglePosition();
	},
	getToggleOffset: function(){
		if(this._open){
			return 5;
		} else {
			var toggleWidth = 29;
			var padding = 20;
			var buffer = 3;
			var panelWidth = 0;
			var elem = null;
			var panelWidth = 0;
			if (this.isTimerActive){
				elem = this._getTimerBlock();
			} else {
				elem = this._getUserBlock();
			}
			//if the element is hidden when we try and get it's width it'll come back as 0.
			//hence flick on and off to get size.
			var toggleToHidden = false;
			if(elem.style.display=="none"){
				toggleToHidden = true;
				elem.setStyle({display:"block"});
			}
			panelWidth = elem.down().getWidth();
			if(toggleToHidden){ elem.setStyle({display:"none"})};

			return (this._getStopwatchBlock().getWidth() - panelWidth - toggleWidth - padding - buffer);
		}
	},
	updateTogglePosition: function(){
		this._getToggler().setStyle({left: (this.getToggleOffset()+"px")});
	},
	openStopwatch: function(){
		if(!stopwatch._tweening){
			this._open = true;
			if(!this.isTimerActive){
				new Effect.Fade('stopwatch-username',{duration:0.4, delay:0});
			}
			new Effect.Move(
				'stopwatch-toggler',
				{ x:5, y:-8, duration:0.4, delay:0, mode:'absolute',
					beforeStart: function() {
						stopwatch._tweening = true;
					}
				}
			);
			$('stopwatch-toggler').addClassName('active');
			new Effect.BlindDown(
				'stopwatch-pulldown',
				{ duration:0.5, delay:0.4, scaleFrom:20,
					afterFinish: function() {
						stopwatch._tweening = false;
					}
				}
			);
		}
	},
	closeStopwatch: function(){
		if(!stopwatch._tweening){
			this._open = false;
			var stopwatchActiveWidgetWidth = this.getToggleOffset();
			new Effect.BlindUp(
				'stopwatch-pulldown',
				{ duration:0.5, delay:0, scaleTo:20,
					beforeStart: function() {
						stopwatch._tweening = true;
					}
				}
			);
						$('stopwatch-toggler').removeClassName('active');
			if(!this.isTimerActive){
		$('stopwatch-username').style.display="block";
			//	new Effect.Appear('stopwatch-username',{duration:0.4, delay:0});
				//$('stopwatch-username').appear()
			}
			new Effect.Move(
				'stopwatch-toggler',
				{ x:stopwatchActiveWidgetWidth, y:-8, duration:0.4, delay:0.5, mode:'absolute',
					afterFinish: function() {
						stopwatch._tweening = false;
					}
				}
			);
		}
	},
	timer: {
		//the interval updating code.
		PING_FREQUENCY: 10,
		_pingCount:0,
		_timerRef: 0,
		_timeStart:null,
		_paused: false,

				getHoursDiff: function(){
					var timer = stopwatch.timer;
					var timeDiff = timer._lastUpdate - timer._timeStart;
					var hours = ((timeDiff / 60) /60)/1000;
					return hours;
				},

		init: function(){
			var sw = stopwatch;
			var self = stopwatch.timer;
			if(!self._paused){
				if(self._timeStart == null){ self._timeStart = new Date(); }
				//self._timeStart.setHours(sw._hours, sw._mins, sw._secs);
				self._timeStart.setHours(self._timeStart.getHours()-sw._hours);
				self._timeStart.setMinutes(self._timeStart.getMinutes()-sw._mins);
				self._timeStart.setSeconds(self._timeStart.getSeconds()-sw._secs);
				self._startTicker();
				self._lastUpdate = new Date();
			}
			stopwatch._setVisibleTime();
		},
		_startTicker: function(){
			this._timerRef = setInterval("stopwatch.timer.update()", 1000);
		},
		increment: function(secsDiff){
			stopwatch._secs += secsDiff;
			if(stopwatch._secs >= 60){
				stopwatch._mins++;
				stopwatch._secs = stopwatch._secs%60;
				if(stopwatch._mins >= 60){
					stopwatch._mins = stopwatch._mins%60;
					stopwatch._hours++;
				}
			}
		},

		update: function(){
			var self = stopwatch.timer;
			if(self._paused){ return;}
			var now = new Date();
			var secsDiff = Math.round((now - self._lastUpdate)/1000);
			self.increment(secsDiff);
			stopwatch._setVisibleTime();
			this._pingCount++;
			if (self._pingCount >= self.PING_FREQUENCY) {
				self._pingCount = 0;
				self.ping();
				self._pingCount++;
			}
			this._lastUpdate = now;
		},

		ping: function(callback){
			callback = (callback)?callback:function(){};
			var swTaskID = parseInt($F('swTaskID'));
			if (swTaskID > 0) {
				var time = stopwatch._getFullTime();
				var url = 'index.php?module=springboard&action=stopwatchping&taskid='+swTaskID+'&time='+time;
				new Ajax.Request(url, {
					method: 'get',
					onSuccess: function(response){
											var obj = eval('('+response.responseText+')');
											// console.log(obj);
											if(obj.success==1){
												if(obj.exists==0){
													//timer didn't exist so cancel it.
													stopwatch.cancelTimer();
												}
											}else {
												//timer failed at server so cancel it.
												stopwatch.cancelTimer();
											}
										}
				});
			}
		},
		remove: function(callback){
			callback = (callback)?callback:function(){};
			var swTaskID = parseInt($F('swTaskID'));
			if (swTaskID > 0) {
				var url = 'index.php?module=springboard&action=stopwatchcancel&taskid='+swTaskID;
				new Ajax.Request(url, {
					method: 'get',
					onSuccess: callback
				});
			}
		},
		resetAll: function(){
			var sw = stopwatch;
			var timer = stopwatch.timer;
			sw._hours = 0;
			sw._mins = 0;
			sw._secs = 0;
			timer._timeStart = null;
			timer._clearTimerRef();
		},
		_getPauseButton: function(){return $('sw-pause-btn');},
		_clearTimerRef: function(){
			var self = stopwatch.timer;
			clearTimeout(self._timerRef);
			self._timerRef  = 0;
			self._timerRef = null;
		},
		togglePause: function(){
			var self = stopwatch.timer;
			if (!self._paused) {
				self.pause();
			} else {
				self.unpause();
			}
		},

		start: function(callback){
			callback = (callback)?callback:function(){};
			var swProjectID = parseInt($F('swProjectID'));
			var swTaskID = parseInt($F('swTaskID'));
			if (swTaskID > 0) {
				var time = stopwatch._getFullTime();
				var url = 'index.php?module=springboard&action=stopwatchstart&projectid='+swProjectID+'&taskid='+swTaskID+'&time='+time;
				new Ajax.Request(url, {
						method: 'get',
						onSuccess: callback
				});
			}
		},
		pause: function(){
			var self = stopwatch.timer;
			self._clearTimerRef()
			self._paused = true;
			self._getPauseButton().innerHTML = Copper.language.msgResume;
				var swTaskID = parseInt($F('swTaskID'));
				var time = stopwatch._getFullTime();
				var url = 'index.php?module=springboard&action=stopwatchpause&paused=1&taskid='+swTaskID+'&time='+time;
				new Ajax.Request(url, {
					method: 'get'
				});
		},
		unpause: function(){
			var self = stopwatch.timer;
			this._lastUpdate = new Date();
			self._startTicker();
			self._paused = false;
			self._getPauseButton().innerHTML = Copper.language.msgPause;
				var swTaskID = parseInt($F('swTaskID'));
				var url = 'index.php?module=springboard&action=stopwatchpause&paused=0&taskid='+swTaskID;
				new Ajax.Request(url, {
					method: 'get'
				});
		}
	}
}
cu.addOnLoad(stopwatch.setup, stopwatch);

var sw_new = stopwatch.newTimerPrompt;

function sw_initTextArea(isEnteringField){
	var initString = "Type a new task comment here";
	var initValue = $F('comment');
	if((isEnteringField)&&(initValue == initString)){ $('comment').value = ""; }
	if((!isEnteringField)&&(initValue == "")){ $('comment').value = initString; }
}

function sw_saveTaskComment(/*boolean*/andNewTimer){
		var timediff = stopwatch.timer.getHoursDiff();
	var projId = $F('swProjectID');
	var taskId = $F('swTaskID');
	var comment = $F('comment');
		if(!comment||comment.length==0){
			alert("Comment Required");
			return;
		}
	var hours = timediff;
	var dateVal = dateHelper.dbNow();
	var parameters = {
		"module":"projects",
		"action":"taskcomment",
		"hours":hours,
		"taskid":taskId,
		"projectid":projId,
		"comment":comment,
		"gendate":dateVal,
		"caller":"stopwatch",
		"oldhours":""
	}
	
	if ( ! andNewTimer)
	{
		stopwatch.cancelTimer();
	}
	
	cu.ajaxPost('index.php', parameters, function() {

		$("comment").value = "";
		if(andNewTimer)
		{
			stopwatch.newTimer(projId, taskId, $('swProjectName').innerHTML, $('swTaskName').innerHTML)
		}
		
		// the other thing, is that we really need to update task list (if it's open), with that task description.
		// note that we cheat a bit here, and instead of injecting the relevant comment, we close and reopen the
		// slider. much easier, and it draws attention a bit better.
		if ($('task_' + taskId))
		{
			// if its open, close it first.
			if ($('task_' + taskId).hasClassName('expanded'))
			{
				toggleTask($('task-opener-' + taskId), projId, taskId);
				// if it's already open, we have to wait a sec after the close, as the animation takes some time.
				
			} else {
				// open it up!
				toggleTask($('task-opener-' + taskId), projId, taskId);
			}
		}
	});
}


/* H4 COMPONENT TOGGLING
 * Requirements: Prototype, Scriptaculous
 *****************************************/
function toggleComponent(toggler, expander_in) {

	// set variable reference to the expander
	expander = ($(toggler).up().id == '')?$(toggler).up().next('.'+expander_in):$(toggler).up().up().next('.'+expander_in);
	// set expanded/closed state
	if (expander.getStyle('display')=='none'){
		cu.cookie.create('toggler-' + expander_in, 1);
		toggler.addClassName('max');
		toggler.removeClassName('min');
	} else {
		cu.cookie.create('toggler-' + expander_in, 0);
		toggler.addClassName('min');
		toggler.removeClassName('max');
	}
	Effect.toggle(expander,'blind',{duration:0.5});
}

/* TASKS
 * Requirements: Prototype, Scriptaculous
 * Using these expanders inside of Scriptaculous sortables seems to mess up in IE,
 * so we detect IE and use a simple dislay non/block instead of animated Blind effect
 *************************************************************************************/
function oldtoggleCollapsable(element, /*function||boolean*/endCallback) {
	try{
		var props = {duration:0.5}
		if(Object.isString(element)){ element = $(element);}
		var expander = element.up(2).down('div.expandWrap');
		if (typeof(expander) == 'undefined')
			var expander = element.up(2).down('ul.expandWrap');

		if(endCallback){
			//pass either a function to call or true if we just want to scroll to it.
			if(Object.isFunction(endCallback)){
				props['afterFinish'] = endCallback;
			} else if(endCallback==true){
				if((expander.getStyle('display')=='none')){
					props['afterFinish'] = function(){
						Effect.ScrollTo(element,{duration:0.5});
					};
				}
			}
		}
		// set variable reference to the expander
		// set expanded/closed state
		if (expander.getStyle('display')=='none'){
			element.up(2).removeClassName('collapsed');
			element.up(2).addClassName('expanded');
			element.addClassName('max');
			element.removeClassName('min');
			if (isIE()){
				expander.setStyle({display:'block'});
			} else {
				Effect.toggle(expander,'blind',props);
			}
		} else {
			element.up(2).removeClassName('expanded');
			element.up(2).addClassName('collapsed');
			element.addClassName('min');
			element.removeClassName('max');
			if (isIE()){
				expander.setStyle({display:'none'});
			} else {
				Effect.toggle(expander,'blind',props);
			}
		}
	}catch(e){
		console.error(e)
	}
}

function toggleCollapsable(element) {
	// set variable reference to the expander
	expander = $(element).up(2).down('.expandWrap');
	// set expanded/closed state
	var d = new cu.Deferred();
	if (expander.getStyle('display')=='none'){
		element.up(2).removeClassName('collapsed');
		element.up(2).addClassName('expanded');
		element.addClassName('max');
		element.removeClassName('min');
		if (isIE()){
			expander.setStyle({display:'block'});
		} else {
			Effect.toggle(expander,'blind',{duration:0.5, afterFinish:function(){ d.callback(true); } });
		}	
	} else {
		element.up(2).removeClassName('expanded');
		element.up(2).addClassName('collapsed');
		element.addClassName('min');
		element.removeClassName('max');
		if (isIE()){
			expander.setStyle({display:'none'});
		} else {
			Effect.toggle(expander,'blind',{duration:0.5});
		}
	}
	return d;
}

/* NEW TASK
 * Requirements: Prototype, Scriptaculous
 * This is a test to demonstrate how revealing & scrolling to a new task might work
 *************************************************************************************/

function newTask(){
	// set variable reference to the expander
	elementRef = $('newTask');
	// reveal the task
	elementRef.setStyle({display:'block'});
	// expand the task
	expander = elementRef.down('div.expandWrap');
	if (typeof(expander) == 'undefined')
		expander = elementRef.down('ul.expandWrap');
	if (expander.getStyle('display')=='none'){
		toggleCollapsable(elementRef.down(1).down(1));
	}
	// scroll to the task
	Effect.ScrollTo('newTask');
}

/* AJAX LOAD
 * Requirements: Prototype, Scriptaculous
 * This is a test to simulate how AJAX data might be loaded/displayed
 *************************************************************************************/
function ajaxLoad(element) {
	// set variable reference to the expander
	elementRef = $(element);
	expander = element.up(2).down('div.expandWrap');
	if (typeof(expander) == 'undefined')
		expander = element.up(2).down('ul.expandWrap');
	loadindicator = element.up(2).down('div.loadindicator');
	dataholder = element.up(2).down('div.dataholder');
	// set expanded/closed state
	if (expander.getStyle('display')=='none'){
		element.up(2).removeClassName('collapsed');
		element.up(2).addClassName('expanded');
		element.addClassName('max');
		element.removeClassName('min');
		// show load indicator
		loadindicator.setStyle({display:'block'});
		// hide dataholder
		dataholder.setStyle({display:'none'});
		if (isIE()){
			expander.setStyle({display:'block'});
		} else {
			Effect.toggle(expander,'blind',{duration:0.5});
		}
		// This is just a javascript timer to simulate data loading. We will wait 5 sec then display the loaded data
		// In reality we would start some AJAX loading of data here...
		t = setTimeout ("displayData(elementRef)", 5000 );
	} else {
		// Closing the task. Here we will also need a call to a function to kill the AJAX data load...
		killAjaxLoad(elementRef);
		// Collapse the expander and reset the expand/collapse button icon
		element.up(2).removeClassName('expanded');
		element.up(2).addClassName('collapsed');
		element.addClassName('min');
		element.removeClassName('max');
		if (isIE()){
			expander.setStyle({display:'none'});
		} else {
			Effect.toggle(expander,'blind',{duration:0.5});
		}
	}
}
function displayData(element) {
	expander = element.up(2).down('div.expandWrap');
	if (typeof(expander) == 'undefined')
		expander = element.up(2).down('ul.expandWrap');
	loadindicator = element.up(2).down('div.loadindicator');
	dataholder = element.up(2).down('div.dataholder');
	// Populate the data holder. In reality this content would need to be constructed using the data obtained via AJAX
	dataholder.innerHTML = "<p>This is some test content.</p><p>It would be loaded dynamically via AJAX.</p><p>I am going to insert a bunch of linebreaks to make this content area quite tall, so we have an idea of how the expand/collapse works with tall content.</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>This is the bottom of the content.</p>";
	// hide load indicator
	loadindicator.setStyle({display:'none'});
	// show dataholder
	if (isIE()){
		dataholder.setStyle({display:'block'});
	} else {
		Effect.BlindDown(dataholder,{duration:0.5});
	}
}
function killAjaxLoad(element) {
	// There would need to be some logic here to stop an AJAX load. For out testing purposes we will just clear out timeout:
	clearTimeout(t);
}

function RemoveResource(id, taskID)
{
	var url = 'index.php?module=projects&action=ajaxremoveresource&taskid='+taskID+'&id='+id;
	var removeFunction = (function(id){
	return function(transport) {
	  $('resource-list-'+taskID).getElementsBySelector('#resource-item-'+id)[0].remove(); 
	}
	})(id)
	new Ajax.Request(url, {
	  method: 'get', 
	  onSuccess: removeFunction 
	});
}

function AddResource(taskID)
{
  // Get the selected permission item before we delete it.
  var sel =  $$('.add-new-'+taskID)[0].getElementsBySelector('#resourceAdd')[0];
  var id = sel.options[sel.selectedIndex].value;
  sel.removeChild(sel.options[sel.selectedIndex]);

  var url = 'index.php?module=projects&action=ajaxaddresource&taskid='+taskID+'&id='+id;
  new Ajax.Updater('resource-list-'+taskID, url, {
	method: 'get',
	insertion: Insertion.Bottom
  });
  return false;
}


// Squareweave got rid of toggleProjectDetails at some point
// Luke to make it within the toggler object.
function toggleProjectDetails(toggler, hide_msg, show_msg) {
	// set variable reference to the expander
	expander = $(toggler).up().up().up().next('.module-cont');
	// set expanded/closed state
	if (expander.getStyle('display')=='none'){
		toggler.innerHTML = hide_msg;
		createCookie("proj-details","1");
	} else {
		toggler.innerHTML = show_msg;
		createCookie("proj-details","0");
	}
	Effect.toggle(expander,'blind',{duration:0.5});
}


function popupWindow(module, id) {
	window.open('index.php?module=' + module + '&action=popup&id=' + id, "popupWindow", "scrollbars=yes,resizable=yes,width=768,height=700");
}

function createCookie(name,value,days) {
  if (days) {
	var date = new Date();
	date.setTime(date.getTime()+(days*24*60*60*1000));
	var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
	var c = ca[i];
	while (c.charAt(0)==' ') c = c.substring(1,c.length);
	if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name,"",-1);
}
