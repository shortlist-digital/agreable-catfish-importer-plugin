<?php

/** @var  \Herbert\Framework\Application $container */
/** @var  \Herbert\Framework\Http $http */
/** @var  \Herbert\Framework\Router $router */
/** @var  \Herbert\Framework\Enqueue $enqueue */
/** @var  \Herbert\Framework\Panel $panel */
/** @var  \Herbert\Framework\Shortcode $shortcode */
/** @var  \Herbert\Framework\Widget $widget */

flush_rewrite_rules();

// Create commands?

/**
 * My awesome closure command
 *
 * <message>
 * : An awesome message to display
 *
 * @when before_wp_load
 */
$foo = function( $args ) {
    WP_CLI::success( $args[0] );
};
WP_CLI::add_command( 'foo', $foo );
