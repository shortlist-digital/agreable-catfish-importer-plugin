<?php namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Sync;

set_time_limit(0);

add_action('wp_ajax_catfishimporter_start_sync-category', function() {
  $response = Sync::importCategory(
    $_POST['catfishimporter_category_sitemap'],
    $_POST['catfishimporter_limit']
  );

  catfishimporter_api_response($response);
});

add_action('wp_ajax_catfishimporter_start_sync-url', function() {
  $response = Sync::importUrl($_POST['catfishimporter_url']);
  catfishimporter_api_response($response);
});

add_action('wp_ajax_catfishimporter_list_categories', function() {
  $response = Sync::getCategories();
  catfishimporter_api_response($response);
});

add_action('wp_ajax_catfishimporter_get_category_status', function() {
  $response = Sync::getCategories();
  catfishimporter_api_response($response);
});

function catfishimporter_api_response($response) {
  header('Content-type: Application/json');
  echo json_encode($response); exit;
}
