<?php
/**
 * Plugin Name: Simple Alert
 * Description: When user will open any page, post or custom post type post and if it is selected from admin side then alert box should be opened. Alert box should contain admin side added text from settings page.
 * Version: 1.1
 * Author: Otto Uys
 * Author URI: https://github.com/ottouys
 */

 /**
  * Enqueue styles & scripts
  */
function add_custom_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    
    // Get the Simple Alert status
    $simple_alert_status = ( get_option('simple_alert_status') ) ? get_option('simple_alert_status') : 0;

    // check the status and show if active
    if ( $simple_alert_status ) {
        // Default styles
        wp_register_style('simple-alert',  $plugin_url . 'styles.css');  
        wp_enqueue_script( 'simple-alert', plugins_url( 'scripts.js', __FILE__ ), array(), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'add_custom_css' );

/**
 * Create Admin Page
 */
// Add the Admin page under Settings
add_action( 'admin_menu', 'simple_alert_options_page' );

function simple_alert_options_page() {

	add_options_page(
		'Custom Simple Alert Settings', // page <title>Title</title>
		'Custom Simple Alert', // menu link text
		'manage_options', // sapability to access the page
		'custom-simple-alert', // page URL slug
		'simple_alert_page_content', // sallback function with content
		2 // priority
	);

}
// Admin page HTML
function simple_alert_page_content(){
	echo '<div class="wrap">
	<h1>Simple Alert Settings</h1>
	<form method="post" action="options.php">';			
		settings_fields( 'simple_alert_settings' ); 
		do_settings_sections( 'custom-simple-alert' );
		submit_button();
	echo '</form></div>';

  /**
   * Debugging
   */
  $post_types_args = array(
    'public' => true,    
  );

  $post_types = get_post_types($post_types_args);

  foreach ($post_types as $key => $post_type) {
    $simple_alert_status = ( get_option('simple_alert_status'. $post_type) ) ? get_option('simple_alert_status') : 0;
    echo $post_type . "<br/>";
  }
}

/* Register a setting and create a field */
add_action( 'admin_init',  'simple_alert_register_setting' );
function simple_alert_register_setting(){

  // Add all the settings
	register_setting(
		'simple_alert_settings', // settings group name
		'simple_alert_status', // option name
		'sanitize_text_field' // sanitization function
	); 

  register_setting(
		'simple_alert_settings', // settings group name
		'simple_alert_description', // option name
		'sanitize_text_field' // sanitization function
	); 
  

  /* Add the section*/
	add_settings_section(
		'simple_alert_settings_section_id', // section ID
		'', // title (if needed)
		'', // sallback function (if needed)
		'custom-simple-alert' // page slug
	);

  // Add all the fields
	add_settings_field(
		'simple_alert_status',
		'Enable Alert',
		'simple_alert_status_html', // function which prints the field
		'custom-simple-alert', // page slug
		'simple_alert_settings_section_id', // section ID
		array( 
			'label_for' => 'simple_alert_status',
			'class' => 'sa-status', // for <tr> element
		)
	);

  add_settings_field(
		'simple_alert_description',
		'Description',
		'simple_alert_description_html', // function which prints the field
		'custom-simple-alert', // page slug
		'simple_alert_settings_section_id', // section ID
		array( 
			'label_for' => 'simple_alert_description',
			'class' => 'sa-description', // for <tr> element
		)
	);    

  $post_types_args = array(
    'public' => true,    
  );

  /**
   * Post Types - Add Field Per post type
   */
  // Get Post Types
  $post_types = get_post_types($post_types_args);
 
  // Loop through post types
  foreach ($post_types as $key => $post_type) {    

    // Field Handle for unique checkboxes
    $handle = "simple_alert_status_post_type_" . $post_type;

    // Args per field
    $args = array(
        'label_for' => $handle,   
        'type'      => $post_type,
        'class' => 'sa-post-type-checkbox'   
    );  
    
    // Register setting
    register_setting(
      'simple_alert_settings', // settings group name
      $handle, // option name
      'sanitize_text_field' // sanitization function
    );     
  
    // Add Setting Field Information
    add_settings_field(
        $handle,
        'Enable for Post type: '. $post_type,
        'simple_alert_post_type_html',
        'custom-simple-alert',
        'simple_alert_settings_section_id',
        $args
    );
  }

}

// HTML for all the fields
function simple_alert_status_html(){
	$text = ( get_option( 'simple_alert_status' ) == 1 ) ? 'checked' : '';
	printf(
		'<input type="checkbox" id="simple_alert_status" name="simple_alert_status" value="1" %s>',
		esc_attr( $text )
	);
}

function simple_alert_description_html(){
	$text = get_option( 'simple_alert_description' );
	printf(
		'<textarea id="simple_alert_description" name="simple_alert_description" rows="5">%s</textarea>',
		esc_attr( $text )
	);
}

function simple_alert_post_type_html( array $args ){
	$type = $args['type'];
  $handle = "simple_alert_status_post_type_" . $type;

  // Check if post type checked
  $post_type_checked = ( get_option( $handle ) == 1 ) ? 'checked' : '';

	printf(
		'<input type="checkbox" id="'.$handle.'" name="'.$handle.'" value="1" %s>',
		esc_attr( $post_type_checked )
	);
}

 /**
  * Content
  * Add the content in the footer
  */
add_action('wp_footer', 'simple_alert_content'); 
function simple_alert_content() {

    $content = '';   
    $simple_alert_status = ( get_option('simple_alert_status') ) ? get_option('simple_alert_status') : 0;
    $simple_alert_description = ( get_option('simple_alert_description') ) ? get_option('simple_alert_description') : '';    
    
    // check the status and show if active
    if ( $simple_alert_status ) {
      wp_enqueue_style('simple-alert');            
      wp_enqueue_script('simple-alert');           
      
      $enabled = false;

      $post_type = get_post_type();
      $handle = "simple_alert_status_post_type_" . $post_type;
      $post_type_checked = ( get_option( $handle ) == 1 ) ? $enabled = true : $enabled = false;

      if($enabled){
        // Simple Alert Notice
        $content .= '<div id="simple-alert--notice">';        
        $content .= '<div class="simple-alert-description">'.$simple_alert_description.'</div>';
        $content .= '<div class="sa-actions"><button class="consent-btn" onclick="simple_alert_notice_hide()">Close notice</button></div>';
        $content .= '</div>';         
      }

      if($content) {
        echo $content;
      }      
    }    
}