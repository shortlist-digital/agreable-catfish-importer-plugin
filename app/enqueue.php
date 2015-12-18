<?php
namespace AgreableCatfishImporterPlugin;

/** @var \Herbert\Framework\Enqueue $enqueue */

$enqueue->admin([
  'as'  => 'catfishImporterAdminCSS',
  'src' => Helper::assetUrl('css/admin.css'),
], 'footer');

$enqueue->admin([
  'as' => 'catfishImporterAdminJS',
  'src' => Helper::assetUrl('javascripts/admin.js'),
], 'footer');
