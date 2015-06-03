<?php
/**
 * Main PHP file used to for initial calls to advanced-comment-control classes and functions.
 *
 * @package advanced-comment-control
 * @since 1.0.0
 */
 
/*
Plugin Name: Advanced Comment Control
Plugin URI: http://lewayotte.com/
Description: This plugin allows you to manage who can comment and when they can comment on your content.
Author: layotte
Version: 1.1.0
Author URI: http://lewayotte.com/
Tags: advanced, comments, disable, spam, security
Text Domain: advanced-comment-control
Domain Path: /i18n
Minifiers:
https://github.com/google/closure-compiler
http://www.minifycss.com/css-compressor/
*/

//Define global variables...
define( 'ADVANCED_COMMENT_CONTROL_VERSION' , '1.1.0' );
define( 'ADVANCED_COMMENT_CONTROL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVANCED_COMMENT_CONTROL_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADVANCED_COMMENT_CONTROL_REL_DIR', dirname( plugin_basename( __FILE__ ) ) );

/**
 * Instantiate Advanced Comment Control class, require helper files
 *
 * @since 1.0.0
 */
function advanced_comment_control_plugins_loaded() {

	require_once( ADVANCED_COMMENT_CONTROL_PLUGIN_PATH . '/class.php' );

	// Instantiate the Pigeon Pack class
	if ( class_exists( 'AdvancedCommentControl' ) ) {
		
		global $advanced_comment_control_plugin;
		
		$advanced_comment_control_plugin = new AdvancedCommentControl();
		
		require_once( ADVANCED_COMMENT_CONTROL_PLUGIN_PATH . '/functions.php' );
			
		//Internationalization
		load_plugin_textdomain( 'advanced-comment-control', false, ADVANCED_COMMENT_CONTROL_REL_DIR . '/i18n/' );
			
	}

}
add_action( 'plugins_loaded', 'advanced_comment_control_plugins_loaded', 4815162342 ); //wait for the plugins to be loaded before init
