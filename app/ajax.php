<?php namespace AgreableCatfishImporterPlugin;

// die('API loaded');

use AgreableCatfishImporterPlugin\Services\Sync;
use Exception;

function increase_server_resources() {
	define( 'MAX_FILE_SIZE', 600000001 );
	set_time_limit( 0 );
	ini_set( 'memory_limit', '1600M' );
}

/**
 * Sync category or all posts
 */
add_action( 'wp_ajax_catfishimporter_start_sync-category', function () {
	increase_server_resources();
	$response = Sync::queueCategory(
		$_POST['catfishimporter_category_sitemap'],
		$_POST['catfishimporter_onExistAction']
	);

	catfishimporter_api_response( $response );
} );

/**
 * Sync specified post
 */
add_action( 'wp_ajax_catfishimporter_start_sync-url', function () {
	increase_server_resources();
	if ( is_numeric( $_POST['catfishimporter_url'] ) ) {
		$_POST['catfishimporter_url'] = get_post_meta( $_POST['catfishimporter_url'], 'catfish_importer_url', true );
	}
	$response = Sync::queueUrl( $_POST['catfishimporter_url'], isset( $_POST['catfishimporter_onExistAction'] ) ? $_POST['catfishimporter_onExistAction'] : 'update' );
	catfishimporter_api_response( $response );
} );

/**
 * Return list of categories for admin interface
 */
add_action( 'wp_ajax_catfishimporter_list_categories', function () {
	increase_server_resources();

	$response = array_merge( [ 'all' ], Sync::getCategories() );
	catfishimporter_api_response( $response );
} );

/**
 * Return total imported posts
 */
add_action( 'wp_ajax_catfishimporter_get_status', function () {
	increase_server_resources();
	$response = Sync::getImportStatus();
	catfishimporter_api_response( $response );
} );

/**
 * Return total imported posts from specific category
 */
add_action( 'wp_ajax_catfishimporter_get_category_status', function () {
	increase_server_resources();
	if ( ! isset( $_GET['sitemapUrl'] ) || ! $_GET['sitemapUrl'] ) {
		throw new Exception( 'sitemapUrl is missing from query' );
	}

	$response = Sync::getImportCategoryStatus( $_GET['sitemapUrl'] );
	catfishimporter_api_response( $response );
} );

/**
 * Return all responses as JSON for admin.js to deal with.
 */
function catfishimporter_api_response( $response ) {
	header( 'Content-type: Application/json' );
	echo json_encode( $response );
	exit;
}
