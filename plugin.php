<?php

/**
 * @wordpress-plugin
 * Plugin Name:       Agreable Catfish Importer Plugin
 * Description:       A WordPress plugin to import Catfish content in to Croissant site.
 * Version:           1.0.0
 * Author:            Shortlist Media
 * Author URI:        http://shortlistmedia.co.uk/
 * License:           MIT
 */

if(file_exists(__DIR__ . '/vendor/getherbert/')){
  require_once __DIR__ . '/vendor/autoload.php';
} else {
  require_once __DIR__ . '/../../../../vendor/autoload.php';
}

if(file_exists(__DIR__ . '/../../../../vendor/getherbert/framework/bootstrap/autoload.php')){
  require_once __DIR__ . '/../../../../vendor/getherbert/framework/bootstrap/autoload.php';
} else {
  require_once __DIR__ . '/vendor/getherbert/framework/bootstrap/autoload.php';
}

// Load Mesh (non-autoloadable)
if(file_exists(__DIR__ . '/vendor/jonsherrard/mesh/')){
  require_once __DIR__ . '/vendor/jonsherrard/mesh/mesh.php';
} else {
  require_once __DIR__ . '/../../../../vendor/jonsherrard/mesh/mesh.php';
}
