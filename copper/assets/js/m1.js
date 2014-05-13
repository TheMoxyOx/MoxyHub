var m1 = {

	init: function() {
		
		// check if we're using the right menu version
		if ($(document.body).hasClassName('iconstyle-v1'))
		{

			// the main menu
			$$('#dash-nav li').each(function(item, index){
	
				item.observe('mouseenter', function(){
					this.down('p').addClassName('active');
					this.down('.top').addClassName('active');
					$('search-results').setOpacity(0.2);
				})
				.observe('mouseleave', function(){
					this.down('p').removeClassName('active');
					this.down('.top').removeClassName('active');
					$('search-results').setOpacity(1);				
				})
				.observe('click', function(){

				});
	
	
			});
	
		}//hasClassName


		// search fun
		$('fld_search').observe('keyup', function(item){
			quickSearch.activate();
		});
	
		$('fld_searchX').observe('click', function(item){
			quickSearch.deactivate();
		});		
	
		$('fld_search').observe('focus', function(item){
			quickSearch.setFocus(true);
			quickSearch.activate();
		});		

		$('fld_search').observe('blur', function(item){
			quickSearch.setFocus(false);
			if (!quickSearch.getHover()){
				quickSearch.deactivate();		
			}
		});				

		$('fld_searchX').observe('click', function(item){
			quickSearch.deactivate();
		});		


		$('search-results').observe('mouseover', function(){
			quickSearch.setHover(true);

		});
	
		$('search-results').observe('mouseout', function(){
			quickSearch.setHover(false);
			if (!quickSearch.getFocus()){
				quickSearch.deactivate();		
			}		
		});
	}

}

var dash = {
	redirectDashSearch: function() {
		var url = 'index.php?module=' + Copper.module 
											+ '&action=' + $('showTheseTasksFilter').options[$('showTheseTasksFilter').options.selectedIndex].value 
											+ '&show=' + $('periodToShowFilter').options[$('periodToShowFilter').options.selectedIndex].value;

		// non admins don't have this.
		if ($('userToShowFilter'))
		{
			url += '&userID=' + $('userToShowFilter').options[$('userToShowFilter').options.selectedIndex].value;
		}
		
		location.href = url + '#showDash';
	}
}

var quickSearch = {

	isHovering : false,
	hasFocus : false,	

	// hover checker/getters
	setHover: function(method){
		this.isHovering = method;
	},
	getHover: function(){
		return this.isHovering;
	},	
	
	// focus getter/setters	
	setFocus: function(method){
		this.hasFocus = method;
	},
	getFocus: function(){
		return this.hasFocus;
	},

	activate: function()
	{
		// the search
		if ($('fld_search').getValue() == 'Type to Search...') {
			$('fld_search').setValue('');
		} 
		$('fld_search').addClassName('active');
		$('fld_searchX').addClassName('active');			
		if ($('fld_search').getValue() == '') {
			$('search-results').removeClassName('active');
		}
	},

	passive: function()
	{
		if ($('fld_search').getValue() == '') {
			$('fld_searchX').removeClassName('active');
			$('fld_search').setValue('Type to Search...');	
			$('fld_searchX').removeClassName('active');
		}
	},
	
	deactivate: function()
	{
		$('fld_search').setValue('Type to Search...');	
		$('search-results').removeClassName('active');
		$('fld_searchX').removeClassName('active');					
	},

	init: function() {
		quickSearch.passive();
	}

};

document.observe("dom:loaded", function() {
	quickSearch.init();	
	m1.init();
});