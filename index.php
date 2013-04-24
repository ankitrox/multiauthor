<?php
/*
  Plugin Name: MultiAuthor
  Plugin URI: http://sharethingz.com
  Description: This plugin is for associating multiple authors for a single post.
  Version: 1.0
  Author: Ankit Gade
  Author URI: http://sharethingz.com
  Text domain: multiauthor
 */

/* Define plugin directory path */
if ( ! defined( 'MULTIAUTHOR_PATH' ) )
	define( 'MULTIAUTHOR_PATH', plugin_dir_path( __FILE__ ) );

/* Define plugins directory url */
if ( ! defined( 'MULTIAUTHOR_URL' ) )
	define( 'MULTIAUTHOR_URL', plugin_dir_url( __FILE__ ) );

/* Define assets url */
if ( ! defined( 'MULTIAUTHOR_ASSETS' ) )
    define( 'MULTIAUTHOR_ASSETS', plugin_dir_url( __FILE__ ).'app/assets' );

/* Define plugins javascript directory path */
if ( ! defined( 'MULTIAUTHOR_JS_URL' ) )
	define( 'MULTIAUTHOR_JS_URL', MULTIAUTHOR_ASSETS.'/js' );

/* Define plugins css directory path */
if ( ! defined( 'MULTIAUTHOR_CSS_URL' ) )
	define( 'MULTIAUTHOR_CSS_URL', MULTIAUTHOR_ASSETS.'/css' );

/* Define plugin img directory url */
if ( ! defined( 'MULTIAUTHOR_IMG' ) )
	define( 'MULTIAUTHOR_IMG', MULTIAUTHOR_ASSETS.'/img' );

function multiathor_autoloader( $class_name ) {

	$multiauthorlibpath = array(

		'app/helper/' . $class_name . '.php',
		'app/admin/' . $class_name . '.php',
		'app/main/' . $class_name . '.php'

	);

	foreach ( $multiauthorlibpath as $i => $path ) {

		$path = MULTIAUTHOR_PATH . $path;

		if ( file_exists( $path ) ) {
			include $path;
			break;
		}
	}
}
spl_autoload_register( 'multiathor_autoloader' );

global $multiauthor, $multiauthorAdmin;

$multiauthor = new multiauthor();

$multiauthorAdmin = new multiauthorAdmin();

register_activation_hook( __FILE__, array( $multiauthor, 'activator' ) );