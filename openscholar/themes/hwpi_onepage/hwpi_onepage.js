jQuery(document).ready(function($) {
  //number of pixels before modifying styles
	var num = jQuery('#menu-bar').offset().top;

	// Size of the un-fixed menu.
	var menu_size = jQuery('#menu-bar').height();

	jQuery(window).bind('scroll', function() {
		if (jQuery(window).scrollTop() > num) {
			jQuery('#page-wrapper').css('marginTop', menu_size + 'px');
			jQuery('#menu-bar').addClass('fixed');
		} else {
			jQuery('#menu-bar').removeClass('fixed');
      jQuery('#page-wrapper').css('marginTop', '0px');
		}
	});

	jQuery('.front .block-boxes-os_sv_list_box').each(function() {
		var $this = $(this);
		var count = jQuery('.node', $this).length;

		jQuery($this).addClass('lopz-' + count);
	});

});
