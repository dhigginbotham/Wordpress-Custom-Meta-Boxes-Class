# Wordpress Custom Meta Boxes Class

This class will allow you to easily create custom meta boxes for Wordpress posts. Just initialize an object of the class with your parameters and an array containing information about the meta boxes (see example below).


# Example

Put "lib" in your theme folder, with all its contents.

<pre>
include( 'lib/metaboxes/MetaBoxes.class.php' );

$myMetaboxes = array(
	array(
		'type'	=> 'text',
		'id'	=> 'myText',
		'label'	=> 'My text input: ',
		'desc'	=> 'A regular textbox.'
	),
	array(
		'type'	=> 'textarea',
		'id'	=> 'myTextarea',
		'label'	=> 'My textarea: ',
		'desc'	=> 'A regular textarea.'
	),
	array(
		'type'	=> 'tinyMCETextarea',
		'id'	=> 'myTinyMCETextarea',
		'label'	=> 'My tinyMCETextarea: ',
		'desc'	=> 'A text area with rich text editor'
	),
	array(
		'type'	=> 'datePicker',
		'id'	=> 'myDatepicker',
		'label'	=> 'My datepicker',
		'desc'	=> 'Text input, using the jQuery UI datepicker.'
	),
	array(
		'type'	=> 'imageUpload',
		'id'	=> 'myImageUpload',
		'label'	=> 'Image upload: ',
		'desc'	=> 'Text input with upload button for image. Also has stripped image dialog.'
	),
	array(
		'type'	=> 'sidebarImageUpload',
		'id'	=> 'mySidebarImageUpload',
		'label'	=> 'Sidebar image upload: ',
		'desc'	=> 'Same as above, but custom made for sidebar box. Just like featured image. Also displays uploaded image.'
	)
);

$myMetaboxesObject = new CustomMetaBox( 'My metabox title', 'post', 'myprefix_', 'normal', 'high', $myMetaboxes );
</pre>


# Requirements

This class requires PHP5+ and Wordpress 3.1+.