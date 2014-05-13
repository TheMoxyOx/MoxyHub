jQuery(document).ready(function($) {
	// setup for ajax
	jQuery(document).ajaxStart(function() {
		AjaxSpinner.loading();
	});

	jQuery(document).ajaxSuccess(function() {
		AjaxSpinner.success();
	});

	jQuery(document).ajaxError(function(e, xhr, options, error) {
		console.log(error);
		AjaxSpinner.failure();
	});

	Copper.init($);
	CopperUI.init();

	// we have to do this after trees. coz trees are annoying.
	modalDialog.initializeLinks();
});


jQuery.extend(true, Copper, {
	post: function(url, data, callback) {
		Copper.ajax('post', url, data, callback);
	},

	get: function(url, data, callback) {
		Copper.ajax('get', url, data, callback);
	},

	ajax: function(type, url, data, callback) {
		cb = jQuery.isFunction(callback) ? callback : (function(data) {});

		jQuery.ajax({
			// first do data
			'type': type,
			'url': url,
			'data': jQuery.extend({ajax: true}, data),
			'dataType': 'json',
			'success': cb
		});
	},

	// core init.
	init: function($) {
		// todo
		$('.js_close_alert').click(function(e) {
			e.preventDefault();
			Copper.post(Copper.base_url, {
				'module': 'core',
				'action': 'close_alert',
				'alert_id': jQuery(e.currentTarget).attr('rel')
			}, function(response) {
				jQuery('a[rel=' + response.data.alert_id + ']').parents('.alert_bar').slideUp();
			});
		});

		// aaaaand pass down.
		Copper.trees.init($);
		Copper.projects.init($);
		Copper.tasks.init();
		Copper.reports.init($);
		Copper.timer.init($);
	}
});



// projects object.
jQuery.extend(true, Copper, { projects: {

	init: function($)
	{
		this.files.init();
		this.budgets.init();
	},

	// for new budgets functionality. note that this works in tandem with the billings.js file.
	budgets: {
		init: function() {
			jQuery('.js_project_budgets_addnewotheritem_overview').submit(function(e) {
				e.preventDefault();
				var data = {};

				var data = {};
				var inputs = jQuery(e.currentTarget).find('input');
				for(var i = 0; i < inputs.length; i++)
				{
					if (jQuery(inputs[i]).attr('name') != '')
					{
					  data[jQuery(inputs[i]).attr('name')] = jQuery(inputs[i]).attr('value');
					}
				}

				Copper.projects.budgets.add_new_other_item(data);
			});

			jQuery('.js_project_budgets_show_addotheritems').click(function(e) {
				e.preventDefault();
				jQuery('.js_other_items_list').slideToggle();
				jQuery('.js_show_purchase_header').show();
			});

			jQuery('.js_project_budgets_removeotheritem').click(function(e) {
				e.preventDefault();
				Copper.billing.deleteOther(jQuery(e.currentTarget).attr('rel'));
			});
		},

		add_new_other_item: function(data) {
			// gay copper. just gay.
			Copper.get(jQuery('.js_project_budgets_addnewotheritem_overview').attr('action'), data, function(response) {
				jQuery('.js_other_items_itemlist').append(response.data.html);
			});
		}

	},

	// for files within projects
	files: {
		init: function() {
			jQuery('.js_project_files_edit').click(function(e) { Copper.projects.files.folder_edit(jQuery(e.currentTarget).attr('rel')) } );
			jQuery('.js_project_files_save').click(function(e) { Copper.projects.files.folder_save(jQuery(e.currentTarget).attr('rel')) } );
			jQuery('.js_project_files_cancel').click(function(e) { Copper.projects.files.folder_cancel(jQuery(e.currentTarget).attr('rel')) } );
		},

		folder_toggle_menu_items: function(folder_id) {
			// show the fields
			var hidden = jQuery('#folderholder-' + folder_id + ' li.js_hidden');
			var visible = jQuery('#folderholder-' + folder_id + ' li:not(.js_hidden)');

			hidden.removeClass('js_hidden');
			visible.addClass('js_hidden');
		},

		folder_edit: function(folder_id) {
			// show the fields
			Copper.projects.files.folder_toggle_menu_items(folder_id);
		},

		folder_save: function(folder_id) {
			Copper.get(
				'index.php',
				{
					module: 'files',
					action: 'renamefolder',
					id: folder_id,
					name: jQuery('input[name=folder_name_' + folder_id + ']').val()
				},
				function(response)
				{
					jQuery('#folder-opener-' + folder_id).text(response.data.folder.Folder);
					Copper.projects.files.folder_toggle_menu_items(folder_id);
				}
			);
		},

		folder_cancel: function(folder_id) {
			Copper.projects.files.folder_toggle_menu_items(folder_id);
		}

	}

}});

jQuery.extend(true, Copper, { tasks: {

	init: function()
	{
		if (Copper.auto_open_task)
		{
			Copper.tasks.open(Copper.item_ids.task_id);
		}
	},

	open: function(task_id)
	{
		// this should eventually only need to take the task_id as a parameter.
		// wrap the old functionality for now.
		if (jQuery('#task-opener-' + Copper.item_ids.task_id).length > 0)
		{
			toggleTask($('task-opener-' + Copper.item_ids.task_id), Copper.item_ids.project_id, Copper.item_ids.task_id, Copper.item_ids.comment_id, Copper.module);
			window.location.hash = "#task-" + Copper.item_ids.task_id;
		}
	}
}});

jQuery.extend(true, Copper, { reports: {

	init: function($)
	{
		if ($('.js_reports_period_select').length > 0)
		{
			$('.js_reports_period_select').bind('change', function(event) {
				Copper.reports.selectPeriod(event.currentTarget);
			});
		}

		if ($('.js_reports_form_submit').length > 0)
		{
			$('.js_reports_form_submit').bind('click', function(event) {
				Copper.reports.submitTimesheetForm(event.currentTarget);
			});
		}

	},

	// okay this stuff is grose, but at least it's in the right place.
	// pass int he select box being modified.
	selectPeriod: function(sel)
	{
		var opt = sel.options[sel.selectedIndex];
		var sd = jQuery(opt).attr( 'sd' );
		var ed = jQuery(opt).attr( 'ed' );

		if (sd && ed)
		{
			jQuery(sel).parents('form').find('#startdate').val(sd);
			jQuery(sel).parents('form').find('#enddate').val(ed);
		}
	},

	// hack hack. at the moment the 'submit' button is a link, outside the form.
	// at least now the js is in the js file :(
	submitTimesheetForm: function(anchor)
	{
		jQuery('form[name=' + jQuery(anchor).attr('rel') + ']').submit();
	}

}});

// trees object.
jQuery.extend(true, Copper, { timer: {

	// note that we have some vars from the globally instantiated object. These include:
	// elapsed
	// taskid
	// updated
	// paused
	update_seconds_interval: null,
	ping_server_interval: null,
	running: false,

	init: function($) {
		if (this.enabled && !this.paused)
		{
			// do stuff!!!
			// set up the interval updater.
			this.start();
		}
		
		// regardless of whether it's enabled or not, we should attach live events for start timer etc.
		jQuery('.js_timer_start').live('click', function(e) {
			e.preventDefault();
			Copper.timer.create(jQuery(e.currentTarget).attr('rel'));
		});

		jQuery('.js_timer_pause').click(function(e) {
			e.preventDefault();
			Copper.timer.pause();
		});

		jQuery('.js_timer_save').click(function(e) {
			e.preventDefault();
			Copper.timer.save();
		});

		jQuery('.js_timer_cancel').click(function(e) {
			e.preventDefault();
			Copper.timer.cancel();
		});

		$(window).unload(function() {
			if (Copper.timer.running)
			{
				Copper.timer.stop();
				Copper.timer.ping_server();
			}
		});
		

	},
	
	create: function(task_id)
	{
		if (this.enabled)
		{
			// first prompt the user to commit their current one. 
			alert("You need to save or cancel your existing timer before starting a new one.");
		} else 
		{
			this.taskid = task_id;
			this.zero();
			this.enabled = true;
			this.start();
			Copper.timer.update_server('stopwatchstart', function(response) {
				jQuery('.js_timer_currenttasklink').text(response.data.task_name);
				jQuery('.js_timer_currenttasklink').attr('href', response.data.task_permalink);
				jQuery('.js_complete').val(response.data.task_pc);
			});
			jQuery('.js_profile').addClass('profile_timer');
		}
		
	},
	
	zero: function() {
		this.elapsed.seconds = 0;
		this.elapsed.minutes = 0;
		this.elapsed.hours = 0;
	},
	
	start: function() {
		this.running = true;
		this.update_seconds_interval = setInterval(Copper.timer.update_seconds, 1000);
		this.ping_server_interval = setInterval(Copper.timer.ping_server, 10000);
	},
	
	stop: function() {
		// ensure we show the most recent data.
		this.refresh_display();
		this.running = false;
		clearInterval(this.update_seconds_interval);
		clearInterval(this.ping_server_interval);
	},
	
	pause: function() {
		if (this.running)
		{
			jQuery('.js_timer_pause').text('Resume');
			this.stop();
		} else 
		{
			jQuery('.js_timer_pause').text('Pause');
			this.start();
		}
		
		Copper.timer.update_server('stopwatchpause');
	},
	
	// remember in the UI, save is actually save and close
	save: function() {
		if (this.enabled)
		{
			this.stop();

			Copper.get('index.php', {
				module: 'springboard',
				action: 'stopwatchsave',
				taskid: Copper.timer.taskid,
				hours: jQuery('.js_hours').val(),
				comment: jQuery('.js_comment').val(),
				complete: jQuery('.js_complete').val(),
			});
			
			this.destroy();
		}
	},

	cancel: function() {
		Copper.timer.update_server('stopwatchcancel');
		this.destroy();
	},

	destroy: function() {
		this.stop();
		this.zero();
		this.enabled = false;
		this.refresh_display();
		jQuery('.js_profile').removeClass('profile_timer');
	},
	
	update_server: function(action, callback){
		Copper.get('index.php', {
			module: 'springboard',
			action: action,
			taskid: Copper.timer.taskid,
			time: Copper.timer.get_elapsed(),
			paused: ! this.running
		}, callback);
	},
	
	update_seconds: function() {
		Copper.timer.elapsed.seconds++;
		if (Copper.timer.elapsed.seconds >= 60)
		{
			Copper.timer.elapsed.minutes++;
			Copper.timer.elapsed.seconds = 0;
		}
		
		if (Copper.timer.elapsed.minutes >= 60)
		{
			Copper.timer.elapsed.hours++;
			Copper.timer.elapsed.minutes = 0;
		}
		
		Copper.timer.refresh_display();
	},
	
	ping_server: function() {
		// Copper.timer.update_server('stopwatchping');
	},
	
	get_elapsed: function() {
		var str = '';
		str += ((Copper.timer.elapsed.hours < 10) ? '0' : '') + Copper.timer.elapsed.hours + ':';
		str += ((Copper.timer.elapsed.minutes < 10) ? '0' : '') + Copper.timer.elapsed.minutes  + ':';
		str += ((Copper.timer.elapsed.seconds < 10) ? '0' : '') + Copper.timer.elapsed.seconds;
		return str;
	},
	
	refresh_display: function() {
		jQuery('.js_timer_count').text(Copper.timer.get_elapsed());
	},
	
	update_estimate: function()
	{
		jQuery('.js_hours').val(Copper.timer.elapsed.hours + Copper.timer.elapsed.minutes / 60);
	}
}});

// trees object.
jQuery.extend(true, Copper, { trees: {

	enabled: false,

	get_main_ul: function() {
		if (Copper.trees.enabled)
		{
			var kids = jQuery('#jstree_1').children('ul');
		} else {
			var kids = jQuery('.js_jstree').children('ul');
		}
		if (kids.length > 0)
		{
			return kids[0];
		} else {
			return null;
		}
	},

	init: function($) {
		if ( jQuery.browser.msie )
		{
			// no trees for ie yet.

			jQuery('.js_jstree').attr('id', 'jstree_1');
			jQuery('.js_jstree').addClass('explorerfail');
			return;
		}

		Copper.trees.enabled = true;
		Copper.trees.init_project_tree($);
		Copper.trees.init_project_files_tree($);
	},

	init_project_tree: function($)
	{
		// set up trees
		var tree_ref = '.js_jstree';
		if ($(tree_ref).length > 0)
		{

			var the_tree = jQuery.tree.create();
			the_tree.init(tree_ref, {
				types: {
					"default" : {
						clickable				: true,
						renameable			: false,
						deletable				: false,
						creatable				: false,
						draggable				: false,
						max_children		: -1,
						max_depth				: -1,
						valid_children	: "all"
					},
					"draggy_drag" : {
							clickable				: true,
							renameable			: false,
							deletable				: false,
							creatable				: false,
							draggable				: true,
							max_children		: -1,
							max_depth				: 4,
							valid_children	: "all"
					}
				},
				callback: {
					onmove: function(moved_node, reference_node, rel_position, the_tree, rollback_obj) {
						var url = 'index.php?module=projects&action=ajaxupdatetasktree';

						jQuery.post(url, {treedata: the_tree.get('', 'json')}, function(data, status, xhr) { /* do a thing about how we finished posting */});
					},

					oninit: function(the_tree) {
						// get rid of jstrees pesky css.
						jQuery("head style[type=text/css]:contains('/* TREE LAYOUT */')").remove();
					}

					// note that we use the .live bind below instead of onchange this.
				}
			});

			// now we want to be sure that links to the right thing here, so bind another event to 'a's inside the tree.
			// note that this only works with a modification to the jstree code.
			// L392 L403 of 0.9.9a
			// commented with the line below:
			// Squareweave Edit. Cancel the return false so that we can have other .live() calls.
			$(tree_ref + ' a').live('click', function(e) {
				the_a = $(e.currentTarget);
				// okay, so only redirect if we have a proper link, and we don't have a lightbox prompt. if we have a lightbox, then it will redirect for us.
				if ((the_a.length > 0) && (the_a.attr('href') != '#') && (the_a.attr('href') != '') && ( ! the_a.hasClass('lbOn')))
				{
					// follow the link.
					document.location.href = the_a.attr('href');
				}
			});

			// dragging
			jQuery.tree.drag_start = function(){
				jQuery('.tree-default li a').addClass('dragging');
				jQuery('#jstree-dragged').addClass('js_jstree');
			};
			jQuery.tree.drag_end = function(){
				jQuery('.tree-default li a').removeClass('dragging');
			};

			// hovering
			jQuery.tree.reference(tree_ref).container.find('em.content:not(.edit_mode)').mouseenter(function(){
				$(this).siblings('a').addClass('active');
				$(this).children('em').addClass('active');
				$(this).children('span').addClass('active');
			});
			jQuery.tree.reference(tree_ref).container.find('em.content').mouseleave(function(){
				$(this).siblings('a').removeClass('active');
				$(this).children('em').removeClass('active');
				$(this).children('span').removeClass('active');
			});


			}
		},

		init_project_files_tree: function($)
		{
			// set up trees
			var tree_ref = '.project_files_tree';
			if ($(tree_ref).length > 0)
			{
				var the_tree = jQuery.tree.create();
				the_tree.init(tree_ref, {
					types: {
						"default" : {
							clickable				: true,
							renameable			: false,
							deletable				: false,
							creatable				: false,
							draggable				: false,
							max_children		: -1,
							max_depth				: -1,
							valid_children	: "all"
						},
						"draggy_file" : {
								clickable				: true,
								renameable			: false,
								deletable				: false,
								creatable				: false,
								draggable				: true,
								max_children		: 0,
								max_depth				: 0,
								valid_children	: "all"
						},
						"draggy_folder" : {
								clickable				: true,
								renameable			: false,
								deletable				: true,
								creatable				: false,
								draggable				: true,
								max_children		: -1,
								max_depth				: 1,
								valid_children	: ["draggy_file"]
						}
					},
					callback: {
						onmove: function(moved_node, reference_node, rel_position, the_tree, rollback_obj) {
							var url = 'index.php?module=projects&action=ajaxupdatefiletree';

							Copper.post(url, {treedata: the_tree.get('', 'json')}, function(data, status, xhr) { /* do a thing about how we finished posting */});
						},

						oninit: function(the_tree) {
							// get rid of jstrees pesky css.
							jQuery("head style[type=text/css]:contains('/* TREE LAYOUT */')").remove();
						}

						// note that we use the .live bind below instead of onchange this.
					}
				});

				// now we want to be sure that links to the right thing here, so bind another event to 'a's inside the tree.
				// note that this only works with a modification to the jstree code.
				// L392 L403 of 0.9.9a
				// commented with the line below:
				// Squareweave Edit. Cancel the return false so that we can have other .live() calls.
				$(tree_ref + ' a').live('click', function(e) {
					the_a = $(e.currentTarget);
					// okay, so only redirect if we have a proper link, and we don't have a lightbox prompt. if we have a lightbox, then it will redirect for us.
					if ((the_a.length > 0) && (the_a.attr('href') != '#') && (the_a.attr('href') != '') && ( ! the_a.hasClass('lbOn')))
					{
						// follow the link.
						document.location.href = the_a.attr('href');
					}

					return true;
				});

				// dragging
				jQuery.tree.drag_start = function(){
					jQuery('.tree-default li a').addClass('dragging');
					jQuery('#jstree-dragged').addClass('js_jstree');
				};
				jQuery.tree.drag_end = function(){
					jQuery('.tree-default li a').removeClass('dragging');
				};

				// hovering
				jQuery.tree.reference(tree_ref).container.find('em.content:not(.edit_mode)').mouseenter(function(){
					$(this).siblings('a').addClass('active');
					$(this).children('em').addClass('active');
					$(this).children('span').addClass('active');
				});
				jQuery.tree.reference(tree_ref).container.find('em.content').mouseleave(function(){
					$(this).siblings('a').removeClass('active');
					$(this).children('em').removeClass('active');
					$(this).children('span').removeClass('active');
				});

				// do stuff with the folders and files!
				jQuery.tree.reference(tree_ref).container.find("li[rel*='draggy_folder']").each(function(){
					$(this).children('a.draggy_handle').addClass("draggy_handle_folder");
					$(this).children('em.content').addClass("draggy_folder");
					$(this).children('em.content').click(function(){
						$(this).siblings('.expandWrap').slideToggle(300);
						$(this).siblings('.expandWrap').removeClass('js_hidden');
						$(this).toggleClass('tree_expanded');
						$(this).siblings('.draggy_handle').toggleClass('tree_expanded');
						$(this).parent().toggleClass('tree_expanded');
					});

				});


				jQuery.tree.reference(tree_ref).container.find("li[rel*='draggy_file'] em.content").each(function(){
					$(this).addClass("draggy_file");
				});


			}
		}
	}
});