<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Agreable Catfish Importer Plugin
 * Description:       A WordPress plugin to import Catfish content in to Croissant site.
 * Version:           3.0.0
 * Author:            Shortlist Media
 * Author URI:        http://shortlistmedia.co.uk/
 * License:           MIT
 */

include __DIR__ . '/app/acf.php';
include __DIR__ . '/app/cli.php';


if ( is_admin() ) {

	include __DIR__ . '/app/hooks.php';
	include __DIR__ . '/app/ajax.php';
	include __DIR__ . '/app/panels.php';

	add_action( 'admin_enqueue_scripts', function () {
		wp_enqueue_style( 'catfish-styles', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css' );
		wp_register_script( 'catfish-js', plugin_dir_url( __FILE__ ) . 'assets/js/admin.js', false, '1.0.0' );
		wp_enqueue_script( 'catfish-js' );
	} );
}