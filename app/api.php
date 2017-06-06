<?php namespace AgreableCatfishImporterPlugin;

// die('API loaded');

use AgreableCatfishImporterPlugin\Services\Sync;
use Exception;
use Symfony\Component\Debug\Debug;

set_time_limit( 0 );

/**
 * Sync category or all posts
 */
add_action( 'wp_ajax_catfishimporter_start_sync-category', function () {
	Debug::enable();
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
	Debug::enable();
	$response = Sync::queueUrl( $_POST['catfishimporter_url'], $_POST['catfishimporter_onExistAction'] );
	catfishimporter_api_response( $response );
} );

/**
 * Return list of categories for admin interface
 */
add_action( 'wp_ajax_catfishimporter_list_categories', function () {
	Debug::enable();

	$response = array_merge( [ 'all' ], Sync::getCategories() );
	catfishimporter_api_response( $response );
} );

/**
 * Return total imported posts
 */
add_action( 'wp_ajax_catfishimporter_get_status', function () {
	Debug::enable();
	$response = Sync::getImportStatus();
	catfishimporter_api_response( $response );
} );

/**
 * Return total imported posts from specific category
 */
add_action( 'wp_ajax_catfishimporter_get_category_status', function () {
	Debug::enable();
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
