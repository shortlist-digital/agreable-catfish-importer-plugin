<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Panel $panel */

$panel->add([
  'type'   => 'panel',
  'as'     => 'mainPanel',
  'title'  => 'Catfish Importer',
  'rename' => 'Recent imports',
  'slug'   => 'catfish-importer-index',
  'icon'   => 'dashicons-download',
  'uses'   => __NAMESPACE__ . '\Controllers\AdminController@index'
]);

$panel->add([
  'type'   => 'sub-panel',
  'parent' => 'mainPanel',
  'as'     => 'syncPanel',
  'title'  => 'Sync',
  'slug'   => 'sync',
  'uses'   => __NAMESPACE__ . '\Controllers\AdminController@sync'
]);
