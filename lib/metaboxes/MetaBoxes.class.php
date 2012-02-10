<?php
/*
	Description:	Class for creating custom metaboxes quickly.
					Supply an array with fields. Example below.
	
	Created:		12.01.2012
	Edited:			27.01.2012
	Author:			Magnus Hauge Bakke
*/

class CustomMetaBox {
	
	/* Declare variables
	------------------------------------------------*/
	
	private $title;
	private $postType;
	private $prefix;
	private $customMetaFields;
	
	
	/*------------------------------------------------
		Construct, add metabox, save meta and
		show metabox
	------------------------------------------------*/
	
	
	/* Construct function
	------------------------------------------------*/
	
	function __construct( $title = 'Custom meta box', $postType = 'post', $prefix = 'custom_', $context = 'normal', $priority = 'high', $customMetaFields = array() ) {
		
		// Set properties
		
		$this->title 			= $title;
		$this->prefix			= $prefix;
		$this->id				= $this->prefix . strtolower( str_replace( ' ', '-', $this->title ) );
		$this->postType 		= $postType;
		$this->context			= $context;
		$this->priority			= $priority;
		$this->customMetaFields = $customMetaFields;
		
		// Add metaboxes, if array is not empty
		
		if( !empty( $customMetaFields ) ) {
			
			add_action( 'add_meta_boxes', array( $this, 'addCustomMetaBox' ) );
			add_action( 'save_post', array( $this, 'saveCustomMeta' ) );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'imageMetaboxScripts' ) );
			add_action( 'wp_ajax_theme-option-get-image', array( $this, 'getImageActionCallback' ) );
			
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_datepicker' ) );
			
			// Check for custom image upload
			if( isset( $_POST['custom_img_upload'] ) || isset( $_GET['custom_img_upload'] ) ) {
							
				add_action( 'admin_init', array( $this, 'customImageUpload' ) );
			
			}
			
		}
		
	}
	
	
	/* Add custom metabox function
	------------------------------------------------*/
	
	function addCustomMetaBox() {
		
		add_meta_box(  
        	$this->id, // $id  
        	$this->title, // $title  
        	array( $this, 'showCustomMetaBox' ), // $callback  
        	$this->postType, // $page  
        	$this->context, // $context  
        	$this->priority // $priority 
        ); 
		
	}
	
	
	/* 
	------------------------------------------------*/
	
	function showCustomMetabox() {
		
		global $post;
		
		echo '<form id="' . $this->id . '" action="" method="post">';
		
			wp_nonce_field( basename(__FILE__), $this->id );
			
			echo '<table class="form-table">';
				
				foreach ( $this->customMetaFields as $field ) {
					
					// Add prefix
					$field['id'] = $this->prefix . $field['id'];
						
					$meta = get_post_meta( $post->ID, $field['id'], true );
					
					echo '<tr>
							<th><label for="' . $field['id'] . '">' . $field['label'] . '</label></th>
								<td>';
								
								switch( $field['type'] ) {
									
									case 'text':
										$this->textInput( $field, $meta );
										break;
									
									case 'textarea':
										$this->textarea( $field, $meta );
										break;
										
									case 'tinyMCETextarea':
										$this->tinyMCETextarea( $field, $meta );
										break;
									
									case 'datePicker':
										$this->datePicker( $field, $meta );
										break;
									
									case 'imageUpload':
										$this->imageInput( $field, $meta );
										break;
										
									case 'sidebarImageUpload':
										$this->sidebarImageInput( $field, $meta );
										break;
									
									default:
										break;
									
								}
					
					echo '</td></tr>';
					
				} // End foreach
				
			echo '</table>';
		
	}
	
	
	/* Save meta from custom metabox
	------------------------------------------------*/
	
	function saveCustomMeta( $post_id ) {
		
		// Check for autosave (which will leave fields empty)
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		
		// Check nonce
		if( !wp_verify_nonce( $_POST[$this->id], basename(__FILE__) ) )
			return $post_id;
		
		if( $_POST['post_type'] != $this->postType )
			return $post_id;
		
		foreach ( $this->customMetaFields as $field ) {
		
			// Add prefix
			$field['id'] = $this->prefix . $field['id'];
			
			$old = get_post_meta( $post_id, $field['id'], true );
			$new = $_POST[$field['id']];
			
			if( $new && $new != $old ) {
				
				update_post_meta($post_id, $field['id'], $new);
				
			} else if( $new == '' && $old ) {
				
				delete_post_meta($post_id, $field['id'], $old);
				
			}
			
		}
		
	}
	
	
	/* Save meta from custom metabox
	------------------------------------------------*/
	
	function enqueue_datepicker() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}
	
	
	/*------------------------------------------------
		Metabox fields
	------------------------------------------------*/
	
	
	/* Text input field HTML
	------------------------------------------------*/
	
	function textInput( $field = array(), $meta ) {
		
		echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" />';
		echo '<br /><span class="description">' . $field['desc'] . '</span>';
		
	}
	
	
	/* Textarea HTML
	------------------------------------------------*/
	
	function textarea( $field = array(), $meta = '' ) {
		
		echo '<textarea name="' . $field['id'] . '" id="' . $field['id'] . '" cols="60" rows="4">' . $meta . '</textarea>';
		echo '<br /><span class="description">' . $field['desc'] . '</span>';
		
	}
	
	
	/* TinyMCE textarea HTML
	------------------------------------------------*/
	
	function tinyMCETextarea( $field = array(), $meta = '' ) {
		
		global $post;
		
		do_action( 'media_buttons' );
		
		echo '
			
			<div class="wp-editor-container"><textarea name="' . $field['id'] . '" id="' . $field['id'] . '" class="wp-editor-area" cols="60" rows="20">' . $meta . '</textarea></div>
		';
		
		echo '<br /><span class="description">' . $field['desc'] . '</span>';
		
		echo '
			<script type="text/javascript">
			
				jQuery( document ).ready( function($) {
				
					if ( typeof tinyMCE != "undefined" ) {
						
						document.getElementById("' . $field['id'] . '").value = switchEditors.wpautop(document.getElementById("' . $field['id'] . '").value);
						
						var ed = new tinyMCE.Editor("' . $field['id'] . '", tinyMCEPreInit.mceInit["content"]); ed.render();
																		
					}
						
				});
				
			</script>
		';
		
	}
	
	
	/* Date picker
	------------------------------------------------*/
	
	function datePicker( $field = array(), $meta ) {
		
		echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" />';
		echo '<br /><span class="description">' . $field['desc'] . '</span>';
		
		echo '<script type="text/javascript">jQuery(function() { jQuery( "#' . $field['id'] . '" ).datepicker({ dateFormat: "yy-mm-dd" }); });</script>';
		
	}
	
	
	/* Custom image upload field
	------------------------------------------------*/
	
	function imageInput( $field = array(), $meta ) {
		
		global $post;
		
		echo '<input type="text" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" size="50" /><a class="thickbox button" href="media-upload.php?&post_id=' . $post->ID . '&target=' . $field['id'] . '&custom_size=' . $field['size'] . '&custom_img_upload=1&type=image&TB_iframe=1&width=640&height=520">' . __( 'Add an Image' ) . '</a>';
		echo '<br /><span class="description">' . $field['desc'] . '</span>';
		
	}
	
	
	/* Custom image upload field in sidebar
	  (Featured image 2) Requires context = side
	------------------------------------------------*/
	
	function sidebarImageInput( $field = array(), $meta ) {
		
		global $post;
		
		echo '<input type="hidden" name="' . $field['id'] . '" id="' . $field['id'] . '" value="' . $meta . '" /><a class="thickbox button" href="media-upload.php?&post_id=' . $post->ID . '&target=' . $field['id'] . '&custom_size=' . $field['size'] . '&custom_img_upload=1&type=image&TB_iframe=1&width=640&height=520">' . __( 'Set featured image' ) . '</a>';
		echo '<tr><td colspan="2">';
			echo '<span class="description">' . $field['desc'] . '</span>';
			echo '<img src="' . $meta . '" alt="" style="max-width: 258px; height: auto; width: auto\9;" class="' . $field['id'] . '" />';
		echo '</td></tr>';
		
	}
	
	
	/*------------------------------------------------
		Custom image upload scripts
	------------------------------------------------*/
	
	/* Javascript for custom image upload
	------------------------------------------------*/
	
	function imageMetaboxScripts() {
		
		wp_register_script( 'mediaUpload', get_bloginfo( 'template_url' ) . '/lib/metaboxes/mediaUpload.js', array( 'jquery' ) );
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'mediaUpload' );
		
	}
	
	
	/* Remove type url and gallery tabs
	------------------------------------------------*/
	
	function imageUploadTabs( $tabs ) {
		
		unset( $tabs['type_url'], $tabs['gallery'] );
		return $tabs;
		
	}
	
	
	/* Disable flash uploader
	------------------------------------------------*/
	
	function disableFlashUploader( $flash ) {
		return false;
	}
	
	
	/* Disable Plupload uploader
	------------------------------------------------*/
	
	function disablePluploadUploader( $plupload_init ) {
		return false;
	}
	
	
	/* Also edit attachment fields when uploading img
	------------------------------------------------*/
	
	function imageFormUrl($form_action_url, $type) {
		
		$form_action_url = $form_action_url . '&custom_img_upload=1&target=' . $_GET['target'] . '&custom_size=' . $_GET['custom_size'];
		return $form_action_url;
		
	}
	
	
	/* Add / remove attachment fields to edit
	------------------------------------------------*/
	
	function imageFieldsEdit( $form_fields, $post ) {
		
		unset($form_fields);
		$filename = basename( $post->guid );
		$attachment_id = $post->ID;
		
		if ( current_user_can( 'delete_post', $attachment_id ) ) {
			
			if ( !EMPTY_TRASH_DAYS ) {
				
				$delete = "<a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Delete Permanently' ) . '</a>';
				
			} elseif ( !MEDIA_TRASH ) {
				
				$delete = "<a href='#' class='del-link' onclick=\"document.getElementById('del_attachment_$attachment_id').style.display='block';return false;\">" . __( 'Delete' ) . "</a>
				 <div id='del_attachment_$attachment_id' class='del-attachment' style='display:none;'>" . sprintf( __( 'You are about to delete <strong>%s</strong>.' ), $filename ) . "
				 <a href='" . wp_nonce_url( "post.php?action=delete&amp;post=$attachment_id", 'delete-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='button'>" . __( 'Continue' ) . "</a>
				 <a href='#' class='button' onclick=\"this.parentNode.style.display='none';return false;\">" . __( 'Cancel' ) . "</a>
				 </div>";
				 
			} else {
				
				$delete = "<a href='" . wp_nonce_url( "post.php?action=trash&amp;post=$attachment_id", 'trash-attachment_' . $attachment_id ) . "' id='del[$attachment_id]' class='delete'>" . __( 'Move to Trash' ) . "</a>
				<a href='" . wp_nonce_url( "post.php?action=untrash&amp;post=$attachment_id", 'untrash-attachment_' . $attachment_id ) . "' id='undo[$attachment_id]' class='undo hidden'>" . __( 'Undo' ) . "</a>";
				
			}
			
		} else {
			
			$delete = '';
			
		}
		
		$form_fields['buttons'] = array( 
			'tr' => "\t\t<tr><td></td><td><input type='button' class='button' id='useThisImg' value='" . __( 'Insert' ) . "' onclick='mediaUpload.useThisImg(".$post->ID.",\"". $_GET['target']."\", \"". $_GET['custom_size']."\")' /> $delete</td></tr>\n"
		);
		
		return $form_fields;
		
	}
	
	
	/* Add filters
	------------------------------------------------*/
	
	function customImageUpload() {
		
		add_filter( 'media_upload_tabs', array( $this, 'imageUploadTabs' ) );
		add_filter( 'flash_uploader', array( $this, 'disableFlashUploader' ) );
		add_filter( 'plupload_init', array( $this, 'disablePluploadUploader' ) );
		add_filter( 'attachment_fields_to_edit', array( $this, 'imageFieldsEdit' ), 10, 2 );
		add_filter( 'media_upload_form_url', array( $this, 'imageFormUrl' ), 10, 2 );
		
	}
	
	
	/* Add an AJAX callback to admin-ajax.php,
	   to fetch image and insert
	------------------------------------------------*/
	
	function getImageActionCallback() {
		
		$original = wp_get_attachment_image_src( $_POST['id'], $_POST['custom_size'] );
		
		if ( !empty( $original ) ) {
			echo $original[0];
		} else {
			die(0);
		}
		
		exit();
	
	}
	
}

?>