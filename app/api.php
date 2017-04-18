<?php namespace AgreableCatfishImporterPlugin;

// die('API loaded');

use AgreableCatfishImporterPlugin\Services\Sync;
use \Exception;

set_time_limit(0);

/**
 * Sync category or all posts
 */
add_action('wp_ajax_catfishimporter_start_sync-category', function() {
  $response = Sync::queueCategory(
    $_POST['catfishimporter_category_sitemap'],
    $_POST['catfishimporter_onExistAction']
  );

  catfishimporter_api_response($response);
});

/**
 * Sync specified post
 */
add_action('wp_ajax_catfishimporter_start_sync-url', function() {
  $response = Sync::queueUrl($_POST['catfishimporter_url'], $_POST['catfishimporter_onExistAction']);
  catfishimporter_api_response($response);
});

/**
 * Return list of categories for admin interface
 */
add_action('wp_ajax_catfishimporter_list_categories', function() {
  $response = Sync::getCategories();
  catfishimporter_api_response($response);
});

/**
 * Return total imported posts
 */
add_action('wp_ajax_catfishimporter_get_status', function() {
  die(1);
  $response = Sync::getImportStatus();
  catfishimporter_api_response($response);
});

/**
 * Return total imported posts from specific category
 */
add_action('wp_ajax_catfishimporter_get_category_status', function() {
  if (!isset($_GET['sitemapUrl']) || !$_GET['sitemapUrl']) {
    throw new Exception('sitemapUrl is missing from query');
  }

  $response = Sync::getImportCategoryStatus($_GET['sitemapUrl']);
  catfishimporter_api_response($response);
});

/**
 * Return all responses as JSON for admin.js to deal with.
 */
function catfishimporter_api_response($response) {
  header('Content-type: Application/json');
  echo json_encode($response); exit;
}
