// version 1.0 - original version
jQuery(document).ready(function() {
	var version = jQuery.fn.jquery.split('.');
	if (parseFloat(version[1]) < 7) {
		// use .live as we are on jQuery prior to 1.7
		jQuery('.colorpickerField').live('click', function() {
			if ( jQuery(this).attr('id').search("__i__") === -1 ) {
				var picker;
				var field = jQuery(this).attr('id').substr(0,20);
				jQuery('.s2_colorpicker').hide();
				jQuery('.s2_colorpicker').each(function(){
					if ( jQuery(this).attr('id').search(field) !== -1) {
						picker = jQuery(this).attr('id');
					}
				});
				jQuery.farbtastic('#' + picker).linkTo(this);
				jQuery('#' + picker).slideDown();
			}
		});
	} else {
		// use .on as we are using jQuery 1.7 and up where .live is deprecated
		jQuery(document).on('mousemove', function(event){
			jQuery('.colorpickerField').each(function(){
				if ( jQuery(this).attr('id').search("__i__") === -1 ) {
					var picker;
					var field = jQuery(this).attr('id').substr(0,20);
					jQuery('.s2_colorpicker').each(function() {
						if ( jQuery(this).attr('id').search(field) !== -1) {
							picker = jQuery(this).attr('id');

						}
					});
					jQuery(this).on('click', function(event) {
						jQuery('.s2_colorpicker').hide();
						jQuery.farbtastic('#' + picker).linkTo(this);
						jQuery('#' + picker).slideDown();
					});
				}
			});
		});
	}
	jQuery('.colorpickerField').focusout(function() {
		jQuery('.s2_colorpicker').slideUp();
	});
});
