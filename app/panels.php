<?php namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Panel $panel */

$panel->add([
  'type'   => 'panel',
  'as'     => 'mainPanel',
  'title'  => 'Catfish Importer',
  'slug'   => 'catfish-importer-index',
  'icon'   => 'dashicons-download',
  'uses'   => __NAMESPACE__ . '\Controllers\AdminController@index'
]);
