/*
	javascript function for reschedule dialog
*/
function submit_this(mode){
	if (mode == "reschedule") {
		jQuery.post( "../wp-content/plugins/wp-greet/wpg-admin-reschedule.php", {startreschedule: 1}, function(data){jQuery( "#message" ).html( data );} );
	}
	return false;
}
