<?php
namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Enqueue $enqueue */

$enqueue->admin([
  'as'  => 'adminCSS',
  'src' => Helper::assetUrl('css/admin.css'),
], 'footer');

$enqueue->admin([
  'as' => 'adminJS',
  'src' => Helper::assetUrl('javascripts/admin.js'),
], 'footer');