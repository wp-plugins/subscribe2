jQuery(document).ready(function(){
	jQuery('input[@name=checkall]').click(function(){
		var checked_status = this.checked;
		jQuery('input[@class=check_me]').each(function(){
			this.checked = checked_status;
		});
	});
});
