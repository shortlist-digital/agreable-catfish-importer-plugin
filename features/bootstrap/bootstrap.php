<?php
$rootDir = __DIR__ . '/../../../../../../';

require_once( $rootDir . '/web/wp/wp-load.php' );

require_once( __DIR__ . '/FeatureContext.php' );
require_once( __DIR__ . '/PostContext.php' );
require_once( __DIR__ . '/SitemapContext.php' );
require_once( __DIR__ . '/SyncContext.php' );

/**
 * Sync requires to increase memory
 */
ini_set( 'memory_limit', '512M' );

\Symfony\Component\Debug\Debug::enable();