var AjaxSpinner = {
	loading: function() {
		$('ajaxSpinner').setStyle({'display':'block'});
		$('ajaxSpinnerInner').setAttribute('class', 'loading');
	},

	success: function() {
		$('ajaxSpinner').setStyle({'display':'block'});
		$('ajaxSpinnerInner').setAttribute('class', 'okay');
		Effect.Fade('ajaxSpinner', { duration: 1.0, delay: 1.0 });		
	},
	
	failure: function() {
		$('ajaxSpinner').setStyle({'display':'block'});
		$('ajaxSpinnerInner').setAttribute('class', 'error');
	},
	
	clear: function() {
		$('ajaxSpinner').setStyle({'display':'none'});
	}
}

/* Global responder that logs all ajax calls */
Ajax.Responders.register({
		onComplete:function(response){
				if(response){
						switch (response.transport.status) {
								case 404:
										//console.error("----Page not found----",response);
										break;
								case 500:
										//console.error("----server error----",response);
										break;
								default:
										break;
						}
				}

				// we have to do this manually, because often onSuccess is overwritten in the specific component 
				if ((response.transport.status >= 200) && (response.transport.status < 300))
				{
					AjaxSpinner.success();
				} else {
					AjaxSpinner.failure();
				}

				// also, relink all the sortables
				// sorry ie. You can't handle the truth. i mean code. 
				if (!isIE()) {
					if ($('tasks')) { setupSortableTasks(); }
				}

		},
		onFailure: AjaxSpinner.failure,
		onCreate: AjaxSpinner.loading
})

var swf = {
				_fileSelect: null,
				_imgWidth: 445,

		eval: function(str){
				try{
						var obj = eval("("+str+")");
						return obj;
				}catch(e){
						//console.error("eval fail with SWF",e);
						return null;
				}
		},
		log: function(){ 
				//console.log(arguments); 
		},
		progress: function(str){
				var obj = this.eval(str);
				var id = obj.id;
								obj.size = swf._fileSelect.size;
								obj.uploaded = Math.round(parseFloat(obj.size.replace(/ KB/g,"")) * (obj.progress/100));
								var template = "${uploaded}Kb of ${size} (${progress}%)";
								$('newFileName-'+id).innerHTML = "Uploading "+obj.filename;
								$('newFileDetails-'+id).innerHTML = cu.substitute(template, obj);
								swf.updateProgressBar(id, obj.progress);
								//32Kb of 745Kb (5%), 3:32 remaining
		},
		filesSelected: function(str){
				var obj = this.eval(str);
								swf._fileSelect = obj;
				var name = obj.name;
				var size = obj.size;
				var id = obj.id;
				$('newFileDetails-'+id).innerHTML = size;
				$('newFileName-'+id).innerHTML = name;
								swf.updateProgressBar(id, 0);
								//push session details into the swf ready for upload.
								document['swfFile'+id].cookieInfo(cu.cookie.getValue("SESSIONID"),'1');
		},
				updateProgressBar: function(id, percent){
						var pos = (this._imgWidth - (this._imgWidth * (percent/100))) * -1;
						$('fileProgress-'+id).setStyle({backgroundPosition:(pos + "px 0px")});
				},
		uploadComplete: function(str){
				try{
								var obj = this.eval(str);
								var newid = obj.id; //if we are uploading a new file the newid will be updated.
				var oldid = ""; //obj.id;
				if((oldid=="")&&(obj.data)){
						var data = this.eval(obj.data);
						newid = (data.fileid)?data.fileid:"";
				}
				oldid = obj.id;
								var data = swf._fileSelect;
								if(document.location.search.indexOf("fileview")<0){
										var downlink = "<a href='index.php?module=files&checkout=0&fileid="+newid+"&action=filedown'>View</a>";
										var viewlink = "<a href='index.php?module=files&fileid="+newid+"&action=fileview'>Details</a>";
										data.links = downlink + " | " + viewlink;
								}else {
										data.links = "<a href='index.php?module=files&checkout=0&fileid="+newid+"&action=filedown'>Download</a>"
								}
								var str = cu.substitute(this.TEMPLATE, data);
								var div = document.createElement("div");
								div.innerHTML = str;
								var row = $('swfRow-'+oldid);
								var dividers = row.getElementsBySelector('.divider');
								dividers[0].insert({after:str});
								$('newFileDetails-'+oldid).innerHTML = "&nbsp;";
				$('newFileName-'+oldid).innerHTML = "&nbsp;";
				} catch(e){
				//console.log(e);
				}
		},
				TEMPLATE: '<dt>${name}<span class="file-details">${size}Kb</span></dt><dd class="linklist">${links}</dd><dd class="divider"></dd>'
}

// why is this not in with billing? oh well
var projects = {
		billing:{
				updateTotals: function(){
						//console.log('update totals');
						var totalsField = $("totalToBill");
						var total = 0;
						$$("#billablelist .edit")
								.forEach(function(item){
										var val = item.value;
										try{
												val = parseFloat(val);
												if(isNaN(val)){ return;}
										}catch(e){
												return;
										}
										total += val;
								});
						totalsField.innerHTML = Copper.currency_symbol + total;
				},
				setupTriggers: function(){
					// fuck you ie8. seriously. go die in a fire.
					if (($$("#billablelist .edit").length) > 0)
					{
						$$("#billablelist .edit").forEach(function(item){
								item.observe("change", projects.billing.updateTotals);
						});
					}
				}
		}
}
// register the init.
cu.addOnLoad(projects.billing.setupTriggers);


var contacts = {
		editTaskComment: function(id){
				var form = $("task_comment_form"+id);
				var obj = form.serialize(true);
				var hours = obj.hours.toLowerCase().replace("hrs",'');
				if(isNaN(parseFloat(hours))){ hours = 0; }
				var props = {
								txtCommentID: obj.commentid,
								txtTaskID: obj.taskid,
								txtProjectID: obj.projectid,
								txtComment: obj.comment,
								txtHours: hours,
								txtCommentDate: obj.date,
								txtContact: obj.contact
				}
				var html = cu.inpageSubstitute($$('#resources #template')[0].innerHTML,props);
				var div = document.createElement("div");
				div.setAttribute("id",("editSpace-"+id));
				div.setAttribute("class",("editSpace"));
				$("task_comment_"+id).appendChild(div);
				$('task_comment_form'+id).hide();
				div.innerHTML = html;
				return false;
		}
}

var springboard = {
		getEditForm: function(id){
				return $('form-taskcomment-'+id);
		},
		editTaskComment: function(id){
				var form = $("task_comment_form"+id);
				var obj = form.serialize(true);
				
				// max is always 100%.
				// we're not quite sure why this might not be the case.
				// see index.php in projects, line 5820
				var max_i = 10;
				var selectHTML = this.getEditForm(obj.taskid).getElementsBySelector('.contact-edit')[0].innerHTML;
				var percentage_ddl = '<select name="percentage" class="edit">'
				for (var i=0; i<=max_i; i++) {
						var sel = (obj.percentage == (i*10))?'selected="selected"':'';
						percentage_ddl += '<option value="' + (i*10) + '" ' + sel + '>' + (i*10) + '</option>';
				}
				percentage_ddl += '</select>';

				var props = {
						txtCommentID: obj.commentid,
						txtTaskID: obj.taskid,
						txtComment: obj.comment,
						txtHours: obj.hours,
						txtProjectID: obj.projectid,
						txtCommentDate: obj.date,
						txtSelectBox: selectHTML,
						txtPercentage: percentage_ddl
				}
				
				var html = cu.inpageSubstitute($$('#resources #template')[0].innerHTML,props);

				// so it takes the html that's been dumped elsewhere in the page, and then tries to
				// populate it with values as given above, saving an ajax call. 
				// This is fine for most purposes, except it doesn't deal with the case of things like 
				// the 'checked' attribute in text boxes, or any other attributes for that matter
				// this means that to do these, we have to do ugly ugly search replace hacks. Beware.

				// so now we do some cleanup
				// first, set the check box for "issue"
				if (obj.issue == "1")
				{
					html = html.replace('name="issue" value="1"', 'name="issue" checked')
				}

				// now, out of scope
				if (obj.outofscope == "1")
				{
					html = html.replace('name="outofscope" value="1"', 'name="outofscope" checked')
				}

				var div = document.createElement("div");
				div.setAttribute("id",("editSpace-"+id));
				div.setAttribute("class",("editSpace"));				
				$("task_comment_"+id).appendChild(div);
				$('task_comment_form'+id).hide();
				div.innerHTML = html;
				return false;
		},
		cancelEditTaskComment: function(id){
				var div = $("editSpace-"+id);
				div.parentNode.removeChild(div);
				$("task_comment_form"+id).show();
		},
		_saveTaskComment: function(id, form, successFunc){
				if(form){
						var params = form.serialize(true);
												if(!params.comment||params.comment.length==0){
														alert("Comment Required");
														return;
												}
						//params["caller"]="taskview";
						params["oldhours"]="";
						//params['module']='springboard';
						//params['action']='comment';
						var request = new Ajax.Request('index.php', {
								method:'post',
								parameters: params,
								onSuccess: successFunc,
								onError: function(){
										alert("error saving");
								}
						});
				}else{
						//console.error("form not found to save");
				}
		},
		saveTaskComment: function(id){
				var form = $('form-taskcomment-'+id);
				var success =	function(response) {
						var returnHtml = response.responseText;
						if (returnHtml.indexOf("nukepanel") > 0) {
								var obj = eval("("+returnHtml+")");
								var node = $('task_'+obj.nukepanel);
								node.fade({duration: 0.2});
						}
						else {
								var doWork = function() {
										if(typeof(globalEditspaceNode)=="undefined") {
												globalEditspaceNode = document.createElement("div");
										}
										globalEditspaceNode.innerHTML = stripslashes(returnHtml);
										var editLi = $('form-taskcomment-'+id).parentNode;
										var container = editLi.parentNode;
										var ajaxElement = Element.down(globalEditspaceNode,"li");
										ajaxElement.style.display="none";
										container.insertBefore(ajaxElement, Element.next(editLi,"li"));
										Effect.toggle(ajaxElement,'blind',{duration:0.5});
										//clear out the text box
										var textboxes = $$('#form-taskcomment-'+id+' textarea');
										if(textboxes&&(textboxes.length>0)){
												textboxes[0].value = "";
										}
										$('perc-bar-highlight-'+id).setStyle({width:($('percentage-options-'+id).value + '%')});
								}
								var commentIdField = $$('#form-taskcomment-'+id+' #commentid')[0];
								if(commentIdField.value != ""){
										Effect.toggle($('task_comment_'+(commentIdField.value)), 'appear',{
												duration:0.3,
												afterFinish:doWork
										});
										commentIdField.value='';
								} else {
										doWork();
								}
						}
						springboard.updateHoursFromComments(id);
				}
				this._saveTaskComment(id, form, success);
		},
		
		// this is the comment id
		saveTaskEditComment: function(id){
				var form = $('form-edit-taskcomment-' + id);
				
				var success = function(response){
						var editLi = $('task_comment_' + id);
						var returnHtml = response.responseText;
						if (typeof(globalEditspaceNode) == "undefined") {
								globalEditspaceNode = document.createElement("div");
						}
						globalEditspaceNode.innerHTML = stripslashes(returnHtml);
						var ajaxElement = Element.down(globalEditspaceNode, "li");
						ajaxElement.style.display = "none";
						editLi.parentNode.insertBefore(ajaxElement, Element.next(editLi, "li"));
						//Effect.toggle(ajaxElement,'blind',{duration:0.5});
						var taskId = $('task_comment_' + id).down('[name=taskid]').getValue();
						editLi.fade({duration: 0.2, afterFinish:function(){
								ajaxElement.appear({duration: 0.2});
								editLi.remove();
								springboard.updateHoursFromComments(taskId);

								// also, after reputting the task, we should check the state of the 'issue' 
								// flag on the parent task
								// easiest way to do it, is to see if the span exists under the task anymore.
								springboard.checkStatusOfIssueFlag(taskId);
						}});
				}
				this._saveTaskComment(id, form, success);
		},

		checkStatusOfIssueFlag: function(taskId) {
			if ($('taskholder-' + taskId))
			{
				var issueSpan = $('taskholder-' + taskId).down('span[class=issue]');
				var theHandle = $('task_' + taskId).down('span[class=handle]');

				if (issueSpan)
				{
					// we have a task with the issue flag, make sure the overal task has the flag too.
					if (!(theHandle.down('span[class=issue]')))
					{
						theHandle.insert(issueSpan);
					}
					
				} else 
				{
					if ((theHandle != undefined) && (theHandle.down('span[class=issue]')))
					{
						theHandle.down('span[class=issue]').remove();
					}
				}
			}
		},

		updateHoursFromComments: function(taskid) {
				var hrsTotal = 0.0;
				$$('.commentHrs_' + taskid).each(function(name, index) { 
						if (name.innerHTML.length > 0)
						{
								hrsTotal += parseFloat(name.innerHTML);
						}
				});
				$$('.hrs_actual_' + taskid).each(function(name, index) {
					// Give 2 decimal places
					name.innerHTML = hrsTotal.toFixed(2);
				});
		},
		
		removeTaskComment: function(commentid, projectid, taskid){
				var parameters = {module:"projects",action:"deletecomment","caller":'ajax', "commentid":commentid, "projectid":projectid, "taskid":taskid };
				var url = cu.url.build('index.php',parameters);

				cu.ajaxGet(url, function(response){
						var resultObj = response.responseText.evalJSON();
						if(resultObj.success==1){
								var ajaxElement=$('task_comment_' + commentid);
								Effect.toggle(ajaxElement,'blind', {
									duration:0.5,
									// update the hours
									afterFinish: function() { 
										ajaxElement.remove(); 
										springboard.updateHoursFromComments(taskid); 
									}
								});
						} else {
								alert("failed to delete");
						}
						
				})

				return false;
		}
}

//redirect to actual function
function sb_saveTaskComment(/*str*/id){springboard.saveTaskComment.apply(springboard, arguments);}

var dateHelper = {
		now: function(){
						var now = new Date();
						var parts = {day: now.getDate(), month: (now.getMonth()+1), year: (now.getFullYear())}
						var dateConstruct =	parts.month +"-" + parts.day + "-"+parts.year;
						return dateConstruct;
		},
				dbNow: function(){
						var now = new Date();
						var parts = {day: now.getDate(), month: (now.getMonth()+1), year: (now.getFullYear())}
						var dateConstruct =	parts.year +"-" + parts.month + "-"+parts.day;
						return dateConstruct;
				}
}

function fillResults(searchTerm){
		quickSearch.activate();
		var returnNumber = 10;
		$('search-results').addClassName("active");
		new Ajax.Updater('search-results-gohere', 'index.php?module=search&action=ajaxsearch&searchTerm='+searchTerm+'&returnNumber='+returnNumber, { method: 'get' });
}

var toggle = {
		_generateUrl: function(args){
				var arr = [];
				for(var item in args){ arr.push(item+"="+args[item]); }
				arr.push('&bust='+cu.getUID());
				return ("index.php?"+arr.join("&"));
		},

		_open: function(elementId, args, updateFieldId, shouldScroll){
				var url = this._generateUrl(args);
				var expander = $(elementId).up(2).down('div.expandWrap');
				if (typeof(expander) == 'undefined')
						var expander = $(elementId).up(2).down('ul.expandWrap');
				var deferred = new cu.Deferred();
				if (expander.getStyle('display')=='none'){
								cu.ajaxGet(url, function(transport) {
												Element.update(updateFieldId, transport.responseText);
												modalDialog.initializeLinks();
												d = toggleCollapsable(elementId, shouldScroll);
												d.addCallback(function() {
														 deferred.callback(true);
												});
								})
				} else {
						deferred.callback(true);
				}
				return deferred;
		},

		_toggle: function(elementId, args, updateFieldId, shouldScroll, open_callback, close_callback){
				try {
						var url = this._generateUrl(args);
						var expander = $(elementId).up(2).down('div.expandWrap');
						if (typeof(expander) == 'undefined')
								var expander = $(elementId).up(2).down('ul.expandWrap');
						if (expander.getStyle('display')=='none'){
								cu.ajaxGet(url, function(transport) {
										Element.update(updateFieldId, transport.responseText);
										modalDialog.initializeLinks();
										toggle.adjustComponentDisplay(updateFieldId);
										toggleCollapsable(elementId, shouldScroll);

										// Adjust CSS for projects module
										var params = cu.url.digestSearch();
										if (open_callback)
										{
											open_callback.call(this, elementId, args);
										}
								})
						} else {
								toggleCollapsable(elementId, shouldScroll);
								if (close_callback)
								{
									close_callback.call(this, elementId, args);
								}
						}
				}catch(e){
					// console.log(e)
				}
		},

		init: function() {
			// first do the project details dash. It always starts off closed.
			// check the cookie, then fire the thingo if appropriate.
		},

		projectDetails: function(toggler, new_message) {
			// set variable reference to the expander
			expander = $(toggler).up().up().up().next('.module-cont');
			// set expanded/closed state
			if (expander.getStyle('display')=='none'){
					cu.cookie.create('project-details', 1);
			} else {
					cu.cookie.create('project-details', 0);
			}

			toggler.innerHTML = new_message;
			Effect.toggle(expander,'blind',{duration:0.5});
		},

		adjustComponentDisplay: function(updateFieldId) {
			var taskEl = $(updateFieldId);
			taskEl.select('.toggler a').each(function(link) {
				var theBlock = link.readAttribute('href').substring(1); // extract post '#' stuff. This is now the unique class field.
				taskEl.select('.' + theBlock).each(function(block) {
					// see whether we should close it.
					if (cu.cookie.getValue('toggler-' + theBlock) == 0)
					{
						link.removeClassName('max');
						link.addClassName('min');

						link.up().next('.' + theBlock).setStyle({'display': 'none'});
						// 
						// // $(theBlock).setStyle({'display': 'none'});
					}
				});
				
			});
		},

		task: function(elementId, projectID, taskID, commentID, caller){
			this.taskScroll(elementId, projectID, taskID, commentID, caller, false, 
				function(link_element, args) {
					the_li = $('task_' + args.taskid);
					the_li.addClassName('tree_expanded');
					
					the_li.down('.draggy_handle').addClassName('tree_expanded');
					the_li.down('.content').addClassName('tree_expanded');
					$('task-opener-' + args.taskid).addClassName('tree_expanded');
					
				},
				function(link_element, args) {
					the_li = $('task_' + args.taskid);
					the_li.removeClassName('tree_expanded');
					
					the_li.down('.draggy_handle').removeClassName('tree_expanded');
					the_li.down('.content').removeClassName('tree_expanded');
					$('task-opener-' + args.taskid).removeClassName('tree_expanded');
				}
			);
		},
		taskScroll: function(elementId, projectID, taskID, commentID, caller, scroll, open_callback, close_callback){
				var args = {'module':'projects','action':'ajaxtaskview','projectid':projectID,'taskid':taskID};
				if (commentID) { args['commentid'] = commentID; }
				if (caller) { args['caller'] = caller; }
				this._toggle(elementId, args, 'taskholder-'+taskID, scroll, open_callback, close_callback);
		},
		projectTasks:function(elementId, projectID){
				var args = {'module':'projects','action':'ajaxtasklist','projectid':projectID};
				this._toggle(elementId, args, 'projectholder-'+projectID);
		},
		projectTasksScroll:function(elementId, projectID){
				var args = {'module':'projects','action':'ajaxtasklist','projectid':projectID};
				this._toggle(elementId, args, 'projectholder-'+projectID, true);
		},
		projectEmails: function(element, key, projectID){
				var args = {'module':'projects','action':'ajaxemailview','projectid':projectID,'email':key}
				this._toggle(element, args, 'emailholder-'+key);
		},
		budgetsTasks: function(element, key, projectID){
				var args = {'module':'projects','action':'ajaxgetinvoicerowitems','projectid':projectID,'taskid':key}
				this._toggle(element, args, 'taskholder-'+key);
		},
		invoicesQuotes: function(element, key, projectID){
				var args = {'module':'projects','action':'ajaxgetexistinginvoice','projectid':projectID,'invoiceid':key}
				this._toggle(element, args, 'invoiceholder-'+key);
		},
		contact: function(element, contactID){
				if (contactID == 0) return;
				var args = {'module':'contacts','action':'ajaxview','id':contactID}
				this._toggle(element, args, 'contactholder-'+contactID);
		},
		clientFolders: function(element, clientID, folderID, order, direction){
				var args = {'module':'files','action':'ajaxclientfolders','clientid':clientID,'folderid':folderID,'order':order,'direction':direction};
				this._toggle(element, args, ('folderlist_'+clientID+'_'+folderID));
		},
		clientFoldersOpen: function(element, clientID, folderID, order, direction){
				var args = {'module':'files','action':'ajaxclientfolders','clientid':clientID,'folderid':folderID,'order':order,'direction':direction};
				return this._open(element, args, ('folderlist_'+clientID+'_'+folderID));
		},
		projectFoldersOpen: function(element, projectID, folderID, order, direction){
				var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':folderID,'order':order,'direction':direction};
				return this._open(element, args, ('folderlist_'+projectID+'_'+folderID));
		},
		file: function(element, fileID){
				var args = {'module':'files','action':'ajaxfileview','fileid':fileID}
				this._toggle(element, args, ('fileholder-'+fileID));
		},
		fileOpen: function(element, fileID){
				var args = {'module':'files','action':'ajaxfileview','fileid':fileID}
				this._open(element, args, ('fileholder-'+fileID));
		},
		user: function(element, userID, action){
				if (!action){ action = 'ajaxuserview';}
				var args = {'module':'administration','action':action,'id':userID}
				this._toggle(element, args, ('userholder-'+userID));
		},
		group: function(element, groupID, action){
				if (!action){ action = 'ajaxgroupview';}
				var args = {'module':'administration','action':action,'id':groupID}
				this._toggle(element, args, ('groupholder-'+groupID));
		},
		clientProjects: function(element, clientID){
				var args = {'module':'clients','action':'ajaxprojectlist','id':clientID}
				this._toggle(element, args, ('clientholder-'+clientID));
		},
		projectFolders: function(element, projectID, folderID, order, direction){
				var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':folderID,'order':order,'direction':direction};
				this._toggle(element, args, ('folderlist_'+projectID+'_'+folderID));
		}
};

cu.addOnLoad(toggle.init, toggle);

/* Support backwards compatability for the toggling */
var toggleTask = toggle.task.bind(toggle);
var toggleProjectTasks = toggle.projectTasks.bind(toggle);
var toggleProjectEmails = toggle.projectEmails.bind(toggle);
var toggleBudgetsTasks = toggle.budgetsTasks.bind(toggle);
var toggleInvoicesQuotes = toggle.invoicesQuotes.bind(toggle);
var toggleContact = toggle.contact.bind(toggle);
var toggleClientFolders = toggle.clientFolders.bind(toggle);
var toggleProjectFolders = toggle.projectFolders.bind(toggle);
var toggleFile = toggle.file.bind(toggle);
var toggleClientProjects = toggle.clientProjects.bind(toggle);
var toggleUser = toggle.user.bind(toggle);
var toggleGroup = toggle.group.bind(toggle);

function editContact(contactID) {
		var url = 'index.php?module=contacts&action=ajaxedit&id='+contactID;
		cu.ajaxGet(url,function(transport) {
				Element.update('contact-li-'+contactID, transport.responseText);
		});
}

function editTask(projectID, taskID, caller) {
		var url = 'index.php?module=projects&action=ajaxtaskedit&projectid='+projectID+'&taskid='+taskID;
		if (typeof caller != 'undefined') { url += '&caller=' + caller; }
/* 		el = $('task_'+taskID).childElements(); */

	// i'm not sure aobut other places, but at least in the springboard, this shouldn't happen.
		if (caller == 'springboard')
		{
			the_ul = '';
		} else {
			the_ul = $('task_'+taskID).childElements().pop();
		}

		cu.ajaxGet(url, function(transport) {
				Element.update('task_'+taskID, transport.responseText);
				Element.insert('task_'+taskID, the_ul);
		});
}
function SubmitTaskForm(projectID, taskID, openState, newTaskOpen, newTaskFormId) {
		// careful, taskID may be an int, or it might be a string like 'new_{longnumber}'
		if ($F('taskname').length == 0) {
				alert('{MSG_ENTER_TASK_NAME}');
				$('taskname').focus();
		} else {
				new Ajax.Request('index.php', {
						method: 'post',
						parameters: $('taskForm_'+taskID).serialize(true), 
						onSuccess: function(transport) {
								UneditTask(projectID, parseInt(transport.responseText), openState, newTaskOpen, newTaskFormId);
						}
				});
		}
}
function UneditTask(projectID, taskID, openState, newTaskOpen, newTaskFormId) {

		// if it's a new task, newTaskFormId will have the custom number that we used
		// to try not clash with other forms.
		// we're just gonna wipe that form.
		var element = null;
		if (newTaskFormId != null) {
			// this is to rename the wrapper li.
			element = $("taskForm_new_" + newTaskFormId).up('li');
			element.writeAttribute("id","task_"+taskID);
			// now we have rewritten the li id, we can remove the form.
			$("taskForm_new_" + newTaskFormId).remove();
		}
		
		// hokay, now we can identify the task li
		element = 'task_' + taskID;

		var url = 'index.php?module=projects&action=ajaxtaskunedit&projectid='+projectID+'&taskid='+taskID;
		el = $(element).childElements();
		$(element).removeClassName("expanded");
		$(element).addClassName("collapsed");

		// remember that in this callback, the this object is bound tot he data array below
		var updater = function(transport) {
			
			if (transport.responseText == 'cancel') {
				Element.remove(this.element);
			} else {
				var the_ul = $(this.element).childElements().pop();
				// first add the contents
				Element.update(this.element, transport.responseText);
				// now add the child uls
				Element.insert(this.element, the_ul);
			}
		
			// 0 - collapse, 1 - view, 2 - edit
			if (this.openState > 0) {
				toggleTask($(this.element).down('a.min'), this.projectID, this.taskID);
			}

			if (this.openState > 1) {
				editTask(this.projectID, this.taskID, "");
			}

			if (this.newTaskOpen) {
				newTask(this.projectID);
			}
			
		}
		 
		var data = {
			'openState': openState,
			'newTaskOpen': newTaskOpen,
			'projectID': projectID,
			'taskID': taskID,
			'newTaskFormId': newTaskFormId,
			'element': element
		};

		cu.ajaxGet(url, updater.bind(data));
}
function copyTask(projectID, taskID, caller) {
	toggleTask($('task-opener-' + taskID), projectID, taskID);
	var id = "task_new_"+cu.getUID();
	$(Copper.trees.get_main_ul()).insert('<li class="sortable droppable expanded draggy_node last leaf" rel="draggy_drag" id="'+id+'"></li>');
	var url = 'index.php?module=projects&action=ajaxtaskcopy&projectid='+projectID+'&taskid='+taskID;
	if (typeof caller != 'undefined') { url += '&caller=' + caller; }
	cu.ajaxUpdate(url, id, function(){
			Effect.ScrollTo($(id),{duration:0.5});
			$('taskname').focus();
	});
}

function editFile(fileID) {
		var url = 'index.php?module=files&action=ajaxfileedit&fileid='+fileID;
		cu.ajaxUpdate(url,'fileholder-'+fileID, function() {
				$('description'+fileID).focus();
		});
}

function saveEditFile(fileID){
		$('file-form-'+fileID).submit();
//		var values = $('file-form-'+fileID).serialize(true);
//		values['caller']="ajax";
//		cu.ajaxPost("index.php?fileid="+fileID,values,function(response){
//				$('fileholder-'+fileID).innerHTML = response.responseText;
//		});
}

function editUser(userID){
		var url = 'index.php?module=administration&action=ajaxuseredit&id='+userID+'&bust='+cu.getUID();
		cu.ajaxUpdate(url,'userholder-'+userID);
}

function editGroup(groupID){
		var url = 'index.php?module=administration&action=ajaxgroupedit&id='+groupID+'&bust='+cu.getUID();
		cu.ajaxUpdate(url,'groupholder-'+groupID);
}

function newGroup() {
		var id = "group_new_"+cu.getUID();
		$('grouplist').insert('<li class="sortable droppable collapsed" id="'+id+'"></li>');
		var url = 'index.php?module=administration&action=ajaxgroupnew';
		cu.ajaxUpdate(url, id, function(){
						toggleCollapsable($(id).down('a.min'), true);
						Effect.ScrollTo($(id),{duration:0.5});
				});
}

function groupMemberRemove(groupID, userID) {
		var url = 'index.php?module=administration&action=ajaxgroupmember&groupid='+groupID+'&userid='+userID+'&direction=remove&bust='+cu.getUID();
		new Ajax.Request(url, { method:'get',
				onSuccess: function(transport){
						var obj = eval('('+transport.responseText+')');
						if (obj.success == 1) {
								$$("[name='groupmemberitem"+userID+"']").each(function(item){item.remove()});
								opt = document.createElement('OPTION');
								opt.value = userID;
								opt.innerHTML = obj.name;
								$('groupadduser').appendChild(opt);
						}
				}
		});
}

function groupMemberAdd(groupID, userID) {
		var url = 'index.php?module=administration&action=ajaxgroupmember&groupid='+groupID+'&userid='+userID+'&direction=add&bust='+cu.getUID();
		new Ajax.Request(url, {
				method: 'get',
				onSuccess: function(transport) {
						if (transport.responseText.length > 0) {
								opt = $('groupadduser').options[$('groupadduser').selectedIndex];
								opt.parentNode.removeChild(opt);
								Element.insert('memberlist', transport.responseText);
						}
				}
		});
}

function taskSort(order,direction, projectID) {
	var url = 'index.php?module=projects&action=ajaxtasklist_project&projectid='+projectID+'&order='+order+'&direction='+direction;
	cu.ajaxUpdate(url, $('tasklist'), function(){
	});

}

function newTask(projectID) {
		var id = "task_new_"+cu.getUID();
		$(Copper.trees.get_main_ul()).insert('<li class="sortable droppable expanded draggy_node last leaf" rel="draggy_drag" id="'+id+'"></li>');
		var url = 'index.php?module=projects&action=ajaxtaskedit&projectid='+projectID;
		cu.ajaxUpdate(url, id, function(){
				Effect.ScrollTo($(id),{duration:0.5});
				$('taskname').focus();
		});
}

function newFile(projectID){
		if ($('input_fileid') != null) {
				if ($F('input_fileid') == '0') {
						return false;
				} 
		}

		//in the general files section add to general files.
		if($('toggler_0_0')!=null){
				if ($('toggler_0_0').hasClassName('min')) {
						var deferred = toggle.projectFoldersOpen($('toggler_0_0'), 0, 0, '', '');
						// the deferred object will conduct the following once the initial
						// general files is expanded properly.
						deferred.addCallback(function(){
								var id = "file_new_"+cu.getUID();
								var node = $('folderlist_0_0');
								if (node) {
										node.insert({top: '<li class="sortable droppable file expanded" id="'+id+'"></li>'});
								} else {
								}
								var url = 'index.php?module=files&action=filenew';
								cu.ajaxUpdate(url, id, function() {
										var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':0,'order':'','direction':''};
										//setupSortable('folderlist_'+projectID+'_0', args, 'folderlist_'+projectID+'_0');
								});
						}, this);
				} else {
						var id = "file_new_"+cu.getUID();
						var node = $('folderlist_0_0');
						if (node) {
								node.insert({top: '<li class="sortable droppable file expanded" id="'+id+'"></li>'});
						} else {
						}
						var url = 'index.php?module=files&action=filenew';
						cu.ajaxUpdate(url, id, function() {
								var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':0,'order':'','direction':''};
								//setupSortable('folderlist_'+projectID+'_0', args, 'folderlist_'+projectID+'_0');
						});
				}
		} else {
				var urlObj = cu.url.digestSearch();
				urlObj.module = "files";
				urlObj.action = "filenew";
				var id = "file_new_"+cu.getUID();
				$$('.project_files_tree ul')[0].insert({top: '<li class="sortable droppable file expanded" id="'+id+'"></li>'});
				var url = cu.url.build('index.php',urlObj);
				cu.ajaxUpdate(url, id, function(){
						$$('ul.folder-contents')[0].setStyle({paddingLeft:'0'});
						//$$('div.cell-name').each(function(item){ item.setStyle({width:'622px'}); });
						var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':0,'order':'','direction':''};
				});
		}
}


function changeClientPerm(sID, accessID, itemID) {
	var f = document.forms.addclient;
	// f.accessid.options.selectedIndex = accessID;
	f.submit();
}

function changeProjectPerm(sID, accessID, itemID) {
	var f = document.forms.addproj;
	f.submit();
}

function changeModulePerm(objectID, accessID) {
		var f = $("addmodule");
		$("objectid").value = objectID;
		$("accessid").value = accessID;
		var params = f.serialize(true);
		cu.ajaxPost("index.php", params, function(result){
				//console.log("success");
		})
}

function getProjectTasks(fileID, projectID) {
		var url = 'index.php?module=files&action=projecttasks&projectid='+projectID;
		cu.ajaxUpdate(url,'taskSelect-'+fileID);
}

function newUser(){ loadNewUser('ajaxusernew', 0); }

function copyUser(userID) { 
	loadNewUser('ajaxusercopy', userID);
}

function loadNewUser(action, userID){
		//var date = new Date();
		//var ms = date.getMilliseconds(); // Try to prevent duplicate element IDs on the LI below.
		$('userlist').insert('<li class="sortable droppable collapsed" id="user_new"></li>');
		var url = 'index.php?module=administration&action='+action+'&id='+userID;
		cu.ajaxUpdate(url, 'user_new', function() {
			toggleCollapsable($('user_new').down('a.min'));
			Effect.ScrollTo($('user_new'),{duration:0.5});
		});
}

function newContact(){
		$('contacts').insert('<li class="sortable droppable collapsed" id="contact_new"></li>');
		var url = 'index.php?module=contacts&action=ajaxnew';
		cu.ajaxUpdate(url,'contact_new',function(){toggleCollapsable($('user_new').down('a.min'));});
}

function toggleFileHierarchy(projectID, folderID, fileID, order, direction)
{
		var element = $('toggler_'+projectID+'_0');
		var expander = $(element).up(2).down('div.expandWrap');
		if (typeof(expander) == 'undefined')
				var expander = $(element).up(2).down('ul.expandWrap');
		if (expander.getStyle('display')=='none'){
				var url = 'index.php?module=files&action=ajaxprojectfolders&projectid='+projectID+'&folderid=0&order='+order+'&direction='+direction;
				cu.ajaxGet(url, function(transport) {
						Element.update('folderlist_'+projectID+'_0', transport.responseText);
						toggleCollapsable(element);
						// Project is now opened. Open a folder if needed.
						if (folderID > 0) {
								var url = 'index.php?module=files&action=ajaxprojectfolders&projectid='+projectID+'&folderid='+folderID+'&order='+order+'&direction='+direction;
								cu.ajaxGet(url, function(transport2) {
										Element.update('folderlist_'+projectID+'_'+folderID, transport2.responseText);
										var id = 'toggler_'+projectID+'_'+folderID;
										setTimeout("toggleCollapsable($('"+id+"'))", 500);
										toggleFile($('toggler-file-'+fileID), fileID);
								});
						} else {
								toggleFile($('toggler-file-'+fileID), fileID);
						}
				});
		} else {
				toggleCollapsable(element);
		}
}

function insertProjectFiles(id){
		var params = cu.url.digestSearch();
		params.module = "files";
		params.action="ajaxprojectfolders";
		var url = cu.url.build("index.php",params);
		cu.ajaxGet(url, function(transport) {
						Element.update($('filelist'), transport.responseText);
						modalDialog.initializeLinks();
		});
}

function dependencyAdd(projectID, taskID) {
		var value = $F('dependencyAddSelect').split(',');
		otherTaskID = value[0];
		var url = 'index.php?module=projects&action=taskdependencyadd&projectid='+projectID+'&taskid='+taskID+'&taskdependency='+$F('dependencyAddSelect');
		cu.ajaxGet(url, function(transport) {
				// Remove the selected option and the ones with the same task in the list.
				$$('#dependencyAddSelect option').each(function(opt) {
						if ((opt.value == otherTaskID+',1') || (opt.value == otherTaskID+',2') || (opt.value == otherTaskID+',3')) {
								opt.parentNode.removeChild(opt);
						}
				});
				Element.insert('add-minus-list', { top: transport.responseText });
		});
}

function dependencyRemove(projectID, taskID, otherTaskID, dependency) {
		var url = 'index.php?module=projects&action=taskdependencyremove&projectid='+projectID+'&taskid='+taskID+'&taskdependency='+otherTaskID+','+dependency;
		cu.ajaxGet(url, function(transport) {
				var obj = eval('('+transport.responseText+')');
				if (obj.success == 1) {
						$('task-dep-'+projectID+'-'+taskID+'-'+otherTaskID+'-'+dependency).remove();
						opt = document.createElement('OPTION');
						opt.value = otherTaskID+',1';
						opt.innerHTML = obj.msg1;
						$('dependencyAddSelect').appendChild(opt);
						opt = document.createElement('OPTION');
						opt.value = otherTaskID+',2';
						opt.innerHTML = obj.msg2;
						$('dependencyAddSelect').appendChild(opt);
						opt = document.createElement('OPTION');
						opt.value = otherTaskID+',3';
						opt.innerHTML = obj.msg3;
						$('dependencyAddSelect').appendChild(opt);
				}
		});
}

function stripslashes(str) {
		str=str.replace(/\\'/g,'\'');
		str=str.replace(/\\"/g,'"');
		str=str.replace(/\\0/g,'\0');
		str=str.replace(/\\\\/g,'\\');
		return str;
}
function cancelProject(projectID) {
		url = "index.php?module=projects&action=view&ajax=1&projectid="+projectID;
		cu.ajaxGet(url, function(transport) {
				Element.update($('borg'), transport.responseText);
		});
}

function editProject(projectID) {
		url = "index.php?module=projects&action=edit&ajax=1&projectid="+projectID;
		cu.ajaxGet(url, function(transport) {
				Element.update($('borg'), transport.responseText);
				toggle.adjustComponentDisplay('borg');
		});

}

function SaveProject(projectID)
{
	if ($F('projectname').length == 0) {
			alert('{MSG_ENTER_PROJECT_NAME}');
			$('projectname').focus();
	} else
	{
		var copy = jQuery('[name=copy]');
		if (copy.val() == 1) {
			copy.parents('form').submit();
			return true;
		}
		
			//Ajax post
			new Ajax.Request('index.php', {
					method: 'post', 
					parameters: $('projectform').serialize(true), 
					onSuccess: function(transport){
							url = "index.php?module=projects&action=view&ajax=1&projectid="+projectID;
							cu.ajaxGet(url, function(transport) {
									Element.update($('borg'), transport.responseText);
							});
					}
			});
	}
}
				
// Creates option element and inserts it into the select element,
// maintaining alphabetical order of options.
function addOption(elementID, str, value)
{
		var obj = str.evalJSON();
		var name = obj.name;
		if(obj.isGroup){ name ="Group: "+name; }
		var sel = $(elementID);
		var len = sel.options.length;
		var searchName = name.replace("Group: ","aaa");
		for (i = 0; i < len; i++)
		{
				//replace Group: with aaa to force groups to the top of the list
				var comparisonStr=(sel.options[i].text).replace(/Group: /, "aaa");
				if (searchName.toLowerCase() < comparisonStr.toLowerCase())
				{
						var opt = document.createElement('option');
						opt.setAttribute('value', value);
						opt.appendChild(document.createTextNode(name));
						sel.insertBefore(opt, sel.options[i]);
						return true;
				}
		}
		return false;
}

function AddRelatedProject()
{
		// Get the selected project before we delete it.
		var relatedProjectID = $F('relatedProjectID');

		var sel = $('relatedProjectID');
		sel.removeChild(sel.options[sel.selectedIndex]);

		var url = 'index.php?module=projects&action=ajaxaddrelatedproject&projectid='+$F('projectid')+'&relatedprojectid='+relatedProjectID;
		new Ajax.Updater('related-projects-list', url, {
				method: 'get',
				insertion: Insertion.Bottom
		});
		return false;
}

function RemoveRelatedProject(relatedProjectID)
{
		var url = 'index.php?module=projects&action=ajaxremoverelatedproject&projectid='+$F('projectid')+'&relatedprojectid='+relatedProjectID;
		new Ajax.Request(url, {
				method: 'get',
				onSuccess: function(transport) {
						addOption('relatedProjectID', transport.responseText, relatedProjectID);
						$('related-project-'+relatedProjectID).remove();
				}
		});
		return false;
}

function AddAccess(mode)
{
		// Get the selected permission item before we delete it.
		var id = $F(mode+'AccessID');

		var sel = $(mode+'AccessID');
		sel.removeChild(sel.options[sel.selectedIndex]);

		// Remove the user from the other drop down - can't simultaneously have read and write perms.
		var otherMode = (mode == 'write') ? 'read' : 'write';
		var otherSel = $(otherMode+'AccessID');
		for (i = 0; i < otherSel.options.length; i++)
		{
				if (id == otherSel.options[i].value)
				{
						otherSel.removeChild(otherSel.options[i]);
				}
		}

		var url = 'index.php?module=projects&action=ajaxaddaccess&projectid='+$F('projectid')+'&mode='+mode+'&id='+id;
		new Ajax.Updater(mode+'-access-list', url, {
				method: 'get',
				insertion: Insertion.Bottom
		});
		return false;
}

function RemoveAccess(mode, id)
{
		var url = 'index.php?module=projects&action=ajaxremoveaccess&projectid='+$F('projectid')+'&mode='+mode+'&id='+id;
		new Ajax.Request(url, {
				method: 'get',
				onSuccess: function(transport) {
						addOption(mode+'AccessID', transport.responseText, id);
						var otherMode = (mode == 'write') ? 'read' : 'write';
						addOption(otherMode+'AccessID', transport.responseText, id);
						$('permission-item-'+id).remove();
				}
		});
		return false;
}

/* these should all get removed in preference for the stuff in Copper.projects.files */
function editFolder(projectID, folderID) {
		var children = $$('#folderlist_'+projectID+'_'+folderID+' ul.horiz-nav li');
		children[0].hide();
		children[1].hide();
		children[2].show();
		children[3].show();
		$('span_'+folderID).hide();
		$('inputwrapper_'+folderID).show();
}

function saveFolderName(projectID, folderID) {
		var folderName = $F('input_'+folderID);
		$('span_'+folderID).update(folderName);
		var children = $$('#folderlist_'+projectID+'_'+folderID+' ul.horiz-nav li');
		children[3].hide();
		children[2].hide();
		children[1].show();
		children[0].show();
		$('inputwrapper_'+folderID).hide();
		$('span_'+folderID).show();
		var url = 'index.php?module=files&action=renamefolder&id='+folderID+'&name='+folderName;
		cu.ajaxGet(url, function(transport) { });
}

function cancelEditFolder(projectID, folderID) {
		if ($('inputwrapper_'+folderID).visible()) {
				var children = $$('#folderlist_'+projectID+'_'+folderID+' ul.horiz-nav li');
				children[3].hide();
				children[2].hide();
				children[1].show();
				children[0].show();
				$('inputwrapper_'+folderID).hide();
				$('span_'+folderID).show();
		}
}

function newFolder(projectID) {
		if ($('folder_0') != null) {
				return false;
		}

		//in the general files section add to general files.
		if($('toggler_0_0')!=null){
				if ($('toggler_0_0').hasClassName('min')) {
						var deferred = toggle.projectFoldersOpen($('toggler_0_0'), 0, 0, '', '');
						// the deferred object will conduct the following once the initial
						// general files is expanded properly.
						deferred.addCallback(function(){
								var id = "folder_0";
								var node = $('fakefile_0');
								if (node) {
										node.insert({after: '<li class="sortable droppable expanded new-folder" id="'+id+'"></li>'});
								} else {
								}
								var url = 'index.php?module=files&action=foldernew';
								cu.ajaxUpdate(url, id);
						}, this);
				} else {
						var id = "folder_0";
						var node = $('fakefile_0');
						if (node) {
								node.insert({after: '<li class="sortable droppable expanded new-folder" id="'+id+'"></li>'});
						} else {
						}
						var url = 'index.php?module=files&action=foldernew';
						cu.ajaxUpdate(url, id, function() { 
								$('input_0').focus();	
						});
				}
		} else 
		{
			// this is the files tab of the projects section
				var urlObj = cu.url.digestSearch();
				urlObj.module = "files";
				urlObj.action = "foldernew";
				var id = "folder_0";
				$$('.project_files_tree ul')[0].insert({bottom: '<li class="sortable droppable expanded new-folder" id="'+id+'"></li>'});
				var url = cu.url.build('index.php',urlObj);
				cu.ajaxUpdate(url, id, function(){
						$('input_0').focus();	
				});
		}
}

function cancelNewFolder(projectID, folderID) {
		$('folder_0').remove();
}

function saveNewFolder(projectID, folderID) {
		var folderName = $F('input_'+folderID).strip();
		if (folderName == '') {
				$('input_'+folderID).focus();
				return false;
		}
		var url = 'index.php?module=files&action=savenewfolder&projectid='+projectID+'&name='+folderName;
		cu.ajaxGet(url, function(transport) { 
				var json = transport.responseText.evalJSON(true);
				$('folder_0').remove();
				var urlObj = cu.url.digestSearch();
				var url = 'index.php?module=files&action=ajaxfolder&projectid='+projectID+'&folderid='+json.folderID+'&caller='+urlObj.module;
				cu.ajaxGet(url, function(transport){
					console.log(jQuery('.project_files_tree > ul'));
					console.log(transport.responseText);
					$('fakefile_' + projectID).insert({after: transport.responseText});
					// now add it in at the end of the new tree.
					jQuery('.project_files_tree > ul').append(transport.responseText);
					
					// var urlObj = cu.url.digestSearch();
					// var args = {'module':'files','action':'ajaxprojectfolders','projectid':projectID,'folderid':0,'order':'','direction':''};
					// setupSortable('folderlist_'+projectID+'_0', args, 'folderlist_'+projectID+'_0');
					
				});
		});
}

function cancelNewFile(obj) {
		var newFileID = $F('input_fileid');
		$(obj).up('li', 1).remove();
		var url = 'index.php?module=files&action=filedel&fileid='+newFileID+'&confirm=1';
		cu.ajaxGet(url);
}

function getIconFromFilename(file) {
		var icon = 'file_icon.gif';
		if ( /\.(jpg|gif|jpeg|png|tiff|ps)$/i.test(file) ) {
				icon = 'file_icon_image.gif';
		} else if( /\.(xls|numbers)$/i.test(file) ) {
				icon = 'file_icon_spreadsheet.gif';
		} else if( /\.(doc|docx)$/i.test(file) ) {
				icon = 'file_icon_word.gif';
		} else if( /\.(pdf)$/i.test(file) ) {
				icon = 'file_icon_pdf.gif';
		}
		return icon;
}

function showType() {
				var selected = $F('availabilityType');
				var states = {
						"always":[],
						"all":['hours','weekday','starting','weekday2'],
						"day":['hours'],
						"weekday":['hours'],
						"week":['weekday'],
						"fortnight":['weekday','starting','weekday2']
				}
				states['all'].each(function(item){$(item).hide();});
				states[selected].each(function(item){$(item).show();})
}

function displayHours(tableID,cellID,maxDayLength,noHours) {
		if (noHours && document.getElementById('notAvailable' + tableID).checked) cellID = '0';
		for (i = 0; i <= (maxDayLength - 1); i++) {
				if (cellID != 0) document.getElementById('notAvailable' + tableID).checked = false;
				if (i < cellID) document.getElementById('hours' + tableID).rows[0].cells[i].style.background = '#999999';
				else document.getElementById('hours' + tableID).rows[0].cells[i].style.background = '#E7E7E7';
		}
		if (cellID > maxDayLength) cellID = maxDayLength;
		document.getElementById('hoursAvailable' + tableID).value = cellID;
}
