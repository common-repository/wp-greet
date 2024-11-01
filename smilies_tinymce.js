/*
 * wp-greet javascript for using smilies with tinymce
 */
function smile(smile) {
	var tedit = null;
	var itext = "<img class='wpg_smile' alt='' src='" + smile + "' />";

	if (typeof tinyMCE != "undefined") {
		tedit = tinyMCE.get( 'message' );
	}

	if (tedit == null || tedit.isHidden() == true) {
		tarea = document.getElementById( textid );
		insert_text( itext, tarea );
	} else if ((tedit.isHidden() == false) && window.tinyMCE) {
				var tmce_ver = window.tinyMCE.majorVersion;
		if (tmce_ver == "4") {
			window.tinyMCE.execCommand( 'mceInsertContent', false, itext );
		} else {
			window.tinyMCE.execInstanceCommand( 'message', 'mceInsertContent',false, itext );
		}
	}
}
