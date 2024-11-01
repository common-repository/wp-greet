/*
 * wp-greet javascript for using smilies without tinymce
 */
function smile(fname) {
	var tarea;
	fname = ' :' + fname + ': ';
	tarea = document.getElementById( 'message' );

	if (document.selection) {
		tarea.focus();
		sel      = document.selection.createRange();
		sel.text = fname;
		tarea.focus();
	} else if (tarea.selectionStart || tarea.selectionStart == '0') {
		var startPos  = tarea.selectionStart;
		var endPos    = tarea.selectionEnd;
		var cursorPos = endPos;
		tarea.value   = tarea.value.substring( 0, startPos ) + fname
				+ tarea.value.substring( endPos, tarea.value.length );
		cursorPos    += fname.length;
		tarea.focus();
		tarea.selectionStart = cursorPos;
		tarea.selectionEnd   = cursorPos;
	} else {
		tarea.value += fname;
		tarea.focus();
	}
}
