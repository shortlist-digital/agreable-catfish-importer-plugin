<?php
$rootDir = __DIR__ . '/../../../../../../';

require_once( $rootDir . '/web/wp/wp-load.php' );

require_once( __DIR__ . '/FeatureContext.php' );
require_once( __DIR__ . '/PostContext.php' );
require_once( __DIR__ . '/SitemapContext.php' );
require_once( __DIR__ . '/SyncContext.php' );

\Symfony\Component\Debug\Debug::enable();