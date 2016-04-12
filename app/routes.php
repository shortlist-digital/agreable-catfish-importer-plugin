<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Router $router */

$router->get([
  'as'   => 'retryPost',
  'uri'  => '/catfish-import/retry',
  'uses' => __NAMESPACE__ . '\Controllers\SlackFeedbackController@retry'
]);

$router->get([
  'as'   => 'ignorePost',
  'uri'  => '/catfish-import/ignore',
  'uses' => __NAMESPACE__ . '\Controllers\SlackFeedbackController@ignore'
]);

$router->get([
  'as'   => 'testNotification',
  'uri'  => '/catfish-import/test-notification',
  'uses' => __NAMESPACE__ . '\Controllers\CronController@test'
]);

$router->get([
  'as'   => 'cronNotification',
  'uri'  => '/catfish-import/run',
  'uses' => __NAMESPACE__ . '\Controllers\CronController@tick'
]);

