var Listeners = {

	_generic: {
		setup_dash_toggler: function() {
	  	$$('.dash-toggler').each(function(el){
				el.observe('click', function(e){
					e.stop();			
					toggleDash();
				});	

				if ($(el).hasClassName('showDashOnLoad')){
					toggleDash();
				}

		  });

			if ($(document).URL.endsWith('#showDash')){
				toggleDash();
			}
		}
	},
		
	global: {
		listen: function()
		{
			// put global ones here.
		}
	},

	projects: {
		listen: function(){
		}
	},

	m1: {
		listen: function(){
		}
	},
	
	calendar: {
		listen: function(){
			if (Copper.module == 'calendar') {
				Listeners._generic.setup_dash_toggler();
			}
		}
	},
	
	
	todos: {
		listen: function(){
		
			if (Copper.module == 'springboard') {
				Listeners._generic.setup_dash_toggler();
			}
		
			if ($('showTheseTasksFilter')) {
				$('showTheseTasksFilter').observe('change', dash.redirectDashSearch);
			}

			if ($('periodToShowFilter')) {
				$('periodToShowFilter').observe('change', dash.redirectDashSearch);
			}

			if ($('userToShowFilter')) {
				$('userToShowFilter').observe('change', dash.redirectDashSearch);
			}
			
			if ($('activityUserToShowFilter')) {
				$('activityUserToShowFilter').observe('change',  function() {
					location.href = 'index.php?module=' + Copper.module 
														+ '&action=activity'
														+ '&userID=' + this.options[this.options.selectedIndex].value
														+ '#showDash';
				})
			}
		}
	},
	
	contacts: {
		listen: function(){
			if ($(document).URL.endsWith('?module=contacts#newContact')){
				newContact();
			}
			
	
			if (($('m1NewContact')) && ($(document).URL.include('module=contact'))){	
				$('m1NewContact').observe('click', function(e){
					e.stop();			
					newContact();
				});	
			}			
		}
	},
		
	
	files: {
		listen: function(){
		
			// this does the stuff for adding files and folders
		
			if ($(document).URL.endsWith('?module=files#newFile')){
				newFile(0);
			}
		
			if ($(document).URL.endsWith('?module=files#newFolder')){
				newFolder(0);
			}		
			
			if (($('m1NewFile')) && ($(document).URL.include('module=files'))){
				$('m1NewFile').observe('click', function(e){
					e.stop();
					newFile(0);
				});
			}
		
			if (($('m1NewFolder')) && ($(document).URL.include('module=files'))){	
				$('m1NewFolder').observe('click', function(e){
					e.stop();
					newFolder(0);
				});
			}			
		
		}
	},	
	
	listen: function(){
		Listeners.projects.listen();
		Listeners.calendar.listen();
		Listeners.todos.listen();
		Listeners.contacts.listen();
		Listeners.files.listen();
		Listeners.global.listen();
	}

}



// This starts the whole listen tree
// which pays attention to all our onclicks and changes and stuff
document.observe("dom:loaded", function() {
	Listeners.listen();	
});