<?php
$rootDir =  dirname( __FILE__ ) . '/../../../../../../..';

$_SERVER["SERVER_PROTOCOL"] = 'HTTP/1.1';


$_SERVER["REQUEST_METHOD"] = 'GET';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Sets a bit of meta data during creating an article
$_SERVER['is-automated-testing'] = true;

// Loads the WordPress Environment and Template
require_once($rootDir . '/web/wp/wp-load.php');

$_SERVER["HTTP_HOST"] = str_replace('http://', '', $_SERVER['WP_HOME']);
