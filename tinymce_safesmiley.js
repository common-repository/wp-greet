(function() {
	tinymce.create(
		'tinymce.plugins.wpg_safesmiley',
		{
			init : function(ed, url) {
				ed.onMouseDown.add(
					function(ed, e) {
						/* ed.on('MouseDown', function(e){ this line is for tinymce api 4 */
						var body = ed.getBody();
						if (jQuery( e.target ).hasClass( 'wpg_smile' )) {
								jQuery( body ).attr( {'contenteditable': false} );
						} else {
							jQuery( body ).attr( {'contenteditable': true} );
						}
					}
				);
			},
			createControl : function(n, cm) { return null; },
		}
	);
	tinymce.PluginManager.add( 'wpg_safesmiley', tinymce.plugins.wpg_safesmiley );
})();
