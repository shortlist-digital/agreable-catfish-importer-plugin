<?php namespace AgreableCatfishImporterPlugin;

// Create route for the posts listing Retry button
$router->get([
  'as'   => 'retryPost',
  'uri'  => '/catfish-import/retry',
  'uses' => __NAMESPACE__ . '\Controllers\SyncController@retry'
]);
