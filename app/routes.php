<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Router $router */

$router->get([
  'as'   => 'cronNotification',
  'uri'  => '/catfish-import',
  'uses' => __NAMESPACE__ . '\Controllers\CronController@tick'
]);
