// prototype
document.observe('dom:loaded', function(event) {
	/* do prototype things */
});

// jQuery
jQuery(document).ready( function($){
	/* do jquery things */
	if('#wblg-content') {
		jQuery('.postbox h3, .postbox .handlediv').click( function() {
	   		jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
			//save_postboxes_state(page);
		});
	}
});