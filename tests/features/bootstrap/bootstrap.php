<?php
$rootDir =  dirname( __FILE__ ) . '/../../../../../../..';

$_SERVER["SERVER_PROTOCOL"] = 'HTTP/1.1';


$_SERVER["REQUEST_METHOD"] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

/** Loads the WordPress Environment and Template **/
require_once($rootDir . '/web/wp/wp-load.php');

// Load Mesh (non-autoloadable)
if(file_exists(__DIR__ . '/../../vendor/jarednova/mesh/')){
  require_once __DIR__ . '/../../vendor/jarednova/mesh/mesh.php';
} else {
  require_once __DIR__ . '/../../../../../../../vendor/jarednova/mesh/mesh.php';
}

$_SERVER["HTTP_HOST"] = str_replace('http://', '', $_SERVER['WP_HOME']);