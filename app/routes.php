<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Router $router */

$router->get([
  'as'   => 'testNotification',
  'uri'  => '/catfish-import-test',
  'uses' => __NAMESPACE__ . '\Controllers\CronController@test'
]);

$router->get([
  'as'   => 'cronNotification',
  'uri'  => '/catfish-import',
  'uses' => __NAMESPACE__ . '\Controllers\CronController@tick'
]);

