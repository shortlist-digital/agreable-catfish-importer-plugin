<?php namespace AgreableCatfishImporterPlugin;

use Croissant\App;

/**
 * Adds resync button
 */
add_action( 'wp_ajax_catfish_reimport', function () {

	if ( is_numeric( $_POST['url'] ) ) {
		$_POST['url'] = get_post_meta( $_POST['url'], 'catfish_importer_url', true );
	}
	/**
	 * @var $api Api
	 */
	$api  = App::get( Api::class );
	$post = $api->importPost( $_POST['catfishimporter_url'] );
	header( 'Content-type: Application/json' );
	echo json_encode( [] );
	exit;
} );

add_filter( 'post_row_actions', function ( $actions, $post ) {
	$actions['re_import'] = "<a title='Re-import from Catfish' class='reicport js-catfish-reimport' href='#' data-id='{$post->ID}'>Reimport</a>";

	return $actions;
}, 10, 2 );


add_action( 'admin_init', function () {
	$user = wp_get_current_user();
	if ( in_array( 'purgatory', (array) $user->roles ) ) {
		wp_redirect( home_url() );
		exit;
	}
}, 100 );

register_activation_hook( __FILE__, function () {
	add_role( 'purgatory', 'Purgatory', [] );
} );