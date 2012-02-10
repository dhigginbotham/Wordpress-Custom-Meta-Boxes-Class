/*
 *	MediaUpload script
 *
 *	This file contains all javascript
 *	for the custom image meta box.
 *
 *	Author: Magnus Hauge Bakke
 */

var mediaUpload = {

	useThisImg : function( attachment_id, target, custom_size ) {
		
		jQuery.post( ajaxurl, {
		
			action: 'theme-option-get-image',
			id: attachment_id, 
			custom_size: custom_size,
			cookie: encodeURIComponent( document.cookie )
		
		}, function( src ){
			
			if ( src == '0' ) {
				
				alert( 'Could not use this image. Try a different attachment.' );
				
			} else {
				
				jQuery( "#"+target, parent.document.body ).val( src );
				
				if( jQuery( "img."+target, parent.document.body  ).length ) {
					jQuery( "img."+target, parent.document.body  ).attr( 'src', src );
				}
				
				window.parent.tb_remove();
				
			}
		
		});
		
	}
	
}