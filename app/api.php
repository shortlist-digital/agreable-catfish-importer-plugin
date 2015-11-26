<?php namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Sync;

add_action('wp_ajax_catfishimporter_start_sync', function() {

});

add_action('wp_ajax_catfishimporter_list_categories', function() {
  $categorys = Sync::getCategories();
  header('Content-type: Application/json');
  echo json_encode($categorys); exit;
});
