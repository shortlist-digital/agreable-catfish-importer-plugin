<?php

/**
 * Catfish Queue Command.
 *
 * ## DESCRIPTION
 *
 * Allows management and execution of the Catfish Importer queue from the command line.
 *
 * ## OPTIONS
 *
 * [<post-url>...]
 * : One or more post url to add to the import queue.
 *
 * [--lossy]
 * : Use lossy image compression.
 *
 * ## EXAMPLES
 *
 *     # Add all posts to the import queue
 *     wp catfish
 *
 *     # Add post with id
 *     wp catfish 1337
 *
 *     # Krake images using lossy compression
 *     wp catfish --lossy
 *
 */
function testCommand( array $args, array $assoc_args ) {
  // var_dump('fooc command rung
  WP_CLI::line( 'foo' );
  WP_CLI::success( $args[0] );
}

// Register command with WP_CLI
WP_CLI::add_command( 'catfish test', 'testCommand' );

/**
 * Catfish Queue Command.
 *
 * ## DESCRIPTION
 *
 * Allows management and execution of the Catfish Importer queue from the command line.
 *
 * ## OPTIONS
 *
 * [<post-url>...]
 * : One or more post url to add to the import queue.
 *
 * [--lossy]
 * : Use lossy image compression.
 *
 * ## EXAMPLES
 *
 *     # Add all posts to the import queue
 *     wp catfish
 *
 *     # Add post with id
 *     wp catfish 1337
 *
 *     # Krake images using lossy compression
 *     wp catfish --lossy
 *
 */
function addToQueue( array $args, array $assoc_args ) {
  // var_dump('fooc command rung
  WP_CLI::line( 'foo' );
  WP_CLI::success( $args[0] );
}

// Register command with WP_CLI
WP_CLI::add_command( 'catfish add', 'addToQueue' );
