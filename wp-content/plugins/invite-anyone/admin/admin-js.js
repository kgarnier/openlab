jQuery(document).ready( function() {
	var j = jQuery;
	
	/* Cloudsponge */
	var cstoggle = j("input#cloudsponge-enabled");
	var cstable = j("div.cs-settings");
	if ( j(cstoggle).attr('checked') == false ) {
		j(cstable).hide();
	}
	
	j(cstoggle).click(function(){
		j(cstable).slideToggle(300);
	});	
	
	/* Access settings */
	var toggle = j("input#invite_anyone_toggle_email_limit");
	var offtoggle = j("input#invite_anyone_toggle_email_no_limit");	
	var submitbutton = j("#invite-anyone-settings-submit");
	
	if ( j(toggle).attr('checked') == false ) {
		j("div.invite-anyone-admin-limited input").attr('disabled', 'disabled');
		j("div.invite-anyone-admin-limited select").attr('disabled', 'disabled');
		j("div.invite-anyone-admin-limited").css('color', '#999');
		j("div.invite-anyone-admin-limited input").css('color', '#999');
	}	

	j(offtoggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").attr('disabled', 'disabled');
			j("div.invite-anyone-admin-limited select").attr('disabled', 'disabled');
			
			j("div.invite-anyone-admin-limited").css('color', '#999');
			j("div.invite-anyone-admin-limited input").css('color', '#999');
		}
	);

	j(toggle).click(
		function() {
			j("div.invite-anyone-admin-limited input").removeAttr('disabled');
			j("div.invite-anyone-admin-limited select").removeAttr('disabled');
			
			j("div.invite-anyone-admin-limited").css('color', '#000');	
			j("div.invite-anyone-admin-limited input").css('color', '#000');
		}
	);
	
	/* Undisables inputs and selects on form submit, so that WP saves the disabled options */
	j(submitbutton).click(
		function() {
			j("div.invite-anyone-admin-limited input").removeAttr('disabled');
			j("div.invite-anyone-admin-limited select").removeAttr('disabled');
		}
	);
	
});