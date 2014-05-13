var CopperUI = {

	tiny_tips: {
	
		tip_text : '',
	
		init : function(){
	
			jQuery('.js_tiny_tip').live('mouseenter', function(e){
				CopperUI.tiny_tips.show_tip(e.originalTarget);
			});
	
			jQuery('.js_tiny_tip').live('mouseleave', function(e){
				CopperUI.tiny_tips.hide_tip(e.originalTarget);
			});
	
		},
	
		show_tip : function(el){
	
			jQuery(document.body).append(jQuery("<div/>", {
				id : "the_tiny_tip",
				'class' : "the_tiny_tip",
				css : {
					left : jQuery(el).offset().left,
					top : jQuery(el).offset().top
				},
				text : jQuery(el).attr('title')
			}));
			
			CopperUI.tiny_tips.tip_text = jQuery(el).attr('title');
			jQuery(el).attr('title', '');
	
			jQuery('#the_tiny_tip').fadeIn(100);
		},
	
		hide_tip : function(el){
			jQuery(el).attr('title', CopperUI.tiny_tips.tip_text);
			jQuery('#the_tiny_tip').remove();
		}
		
	},
		
	profile: {

		init: function() {
 	
			jQuery('.js_profile_name').click(function(e){
				e.preventDefault();
				if (jQuery('.js_profile').hasClass('profile_inactive'))
				{
					CopperUI.profile.open();
				} else
				{
					CopperUI.profile.close();
				}
			});
		},
	
		open: function(){
			Copper.timer.update_estimate();
			jQuery('.js_profile').removeClass('profile_inactive');
			jQuery('.js_profile').addClass('profile_active');
		},
	
		close: function(){
			jQuery('.js_profile').addClass('profile_inactive');
			jQuery('.js_profile').removeClass('profile_active');
		}	
	
	},
		
	init : function(){
		CopperUI.tiny_tips.init();
		CopperUI.profile.init();
	}
};

