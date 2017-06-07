<?php

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Sync;

define( 'MAX_FILE_SIZE', 600000001 );

/**
 * Generate a random key for the application.
 *
 * ## DESCRIPTION
 *
 * Creates the AES-256-CBC access key for use with the Laravel encrypter and queue library.
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Add all a specified post
 *     wp catfish generatekey
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish generatekey', function () {
	\WP_CLI::line( 'Add the following link to your .env:' );
	\WP_CLI::line( 'ILLUMINATE_ENCRYPTOR_KEY=' . substr( base64_encode( sha1( mt_rand() ) ), 0, 32 ) );
} );

/**
 * Throws an exception that should be tracked by BugSnag
 *
 * ## DESCRIPTION
 *
 * Throws a new exeption that can be used for testing error tracking within the cli.
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Add all a specified post
 *     wp catfish testexception
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish testexception', function () {
	// Show my errors
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	try {
		// The Exception handler should log with Bugsnag
		throw new Exception( "Bugsnag Test Exception" );
	} catch ( Exception $e ) {
		// Send handled error to BugSnag as well..
		$bugsnag = Bugsnag\Client::make( getenv( 'BUGSNAG_API_KEY' ) );
		$bugsnag->notifyException( $e );
		$bugsnag->notifyError( 'TestError', 'Something bad happened' );
	}
} );

/**
 * Add Items to Catfish Queue.
 *
 * ## DESCRIPTION
 *
 * Allows adding to the Catfish Importer queue from the command line.
 *
 * ## OPTIONS
 *
 * [<post-url>...]
 * : One or more post url to add to the import queue.
 *
 * [<on-exist-action>...]
 * : Optional action if the post exists in WordpressRedirects
 *
 * ## EXAMPLES
 *
 *     # Add all a specified post
 *     wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken update
 *
 *     # Import a category of posts
 *     wp catfish queue http://www.shortlist.com/sitemap/entertainment/48-hours-to.xml
 *
 *     # Import all posts
 *     wp catfish all
 *
 */

// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish queue', function ( array $args ) {

	// Catch incorrect useage of command which could lead to adding plain text to queue
	if ( in_array( $args[0], array( 'work', 'clear', 'purge' ) ) ) {
		\WP_CLI::error( 'Commands aren\'t nested. You should use "wp catfish ' . $args[0] . '" instead of "wp catfish queue ' . $args[0] . '".' );

		return;
	}

	if ( ! isset( $args[0] ) ) {
		\WP_CLI::error( "You must pass a post or sitemap url to the catfish queue command. eg. http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken" );

		return;
	}

	if ( isset( $args[1] ) && ! in_array( $args[1], array( 'update', 'delete-insert', 'skip' ) ) ) {
		\WP_CLI::error( "The on Exist Action must be either 'update', 'delete-insert', 'skip'" );

		return;
	}

	// Set the onExistAction
	if ( isset( $args[1] ) && in_array( $args[1], array( 'update', 'delete-insert', 'skip' ) ) ) {
		$onExistAction = $args[1];
	} else {
		$onExistAction = 'update';
	}

	\WP_CLI::line( 'onExistAction set to: ' . $onExistAction );

	if ( $args[0] == 'all' || strstr( $args[0], '.xml' ) ) {
		\WP_CLI::line( 'Queueing category.' );

		// Queue action is too long to run without being released back into the queue.
		// Instead run all large queue adds on the command line
		Sync::importCategory( '', array( 'url' => $args[0], 'onExistAction' => $onExistAction ), true );

		\WP_CLI::success( 'Queued: ' . $args[0] );
	} else {
		\WP_CLI::line( 'Queueing post.' );

		Sync::queueUrl( $args[0], $onExistAction );

		\WP_CLI::success( 'Queued: ' . $args[0] );
	}
} );

/**
 * Action one item in the Catfish Queue.
 *
 * ## DESCRIPTION
 *
 * Allows execution of one item Catfish Importer queue from the command line.
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Complete next queue action
 *     wp catfish work
 *
 */

// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish work', function ( array $args ) {
	// Let the queue run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	if ( ! getenv( 'ENVOYER_HEARTBEAT_URL_IMPORTER' ) || getenv( 'ENVOYER_HEARTBEAT_URL_IMPORTER' ) == '' ) {
		throw new Exception( "ENVOYER_HEARTBEAT_URL_IMPORTER is not set in your .env file" );

		return;
	}

	\WP_CLI::line( 'Working on queue...' );
	Sync::actionSingleQueueItem();
} );

/**
 * Action to clear all items from the Catfish Queue.
 *
 * ## DESCRIPTION
 *
 * Allows all items to be cleared from queue
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Delete all items from queue
 *     wp catfish purge
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish purge', function ( array $args ) {

	// Let the queue run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	\WP_CLI::confirm( "Are you sure you want to DELETE ALL ITEMS from the queue?", $args );
	Sync::purgeQueue( true );


} );

/**
 * Action to delete all automated_testing posts from
 *
 * ## DESCRIPTION
 *
 * Delete all posts with the automated_testing meta tag
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Delete all posts with the automated_testing meta tag
 *     wp catfish clearautomatedtesting
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish clearautomatedtesting', function ( array $args ) {
	// Let the queue run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	\WP_CLI::confirm( "Are you sure you want to DELETE ALL POSTS marked as automated_testing?", $args );

	\WP_CLI::line( 'Clearing automated_testing post from the queue...' );

	Post::deleteAllAutomatedTestingPosts( true );

} );

/**
 * Scan for updates in Clock CMS.
 *
 * ## DESCRIPTION
 *
 * Checks for changes in the Clock CMS and imports them automatically.
 * To be run every minute via a cron like so:
 * `* * * * * cd [DOCROOT]/pages/web/app/plugins/agreable-catfish-importer-plugin && wp catfish scanupdates > /dev/null 2>&1`
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Scan for updated posts in the Clock CMS and queue them
 *     wp catfish scanupdates
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish scanupdates', function () {
	// Let the scan run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	\WP_CLI::line( 'Scanning for new updates in Clock...' );
	Sync::updatedPostScan( true );
	\WP_CLI::success( 'Scan complete' );

} );

/**
 * Find missing posts from Import
 *
 * ## DESCRIPTION
 *
 * Checks sitemap and the WordpressRedirects database and finds posts that are missing
 * from the import to re import
 *
 * ## OPTIONS
 *
 * [--queuemissing=<queuemissing>]
 * : Whether or not to queue the missing items for import.
 * ---
 * default: false
 * options:
 *   - true
 *   - false
 * ---
 *
 * [--onexistaction=<onexistaction>]
 * : The method to handle posts that already exist in the database.
 * ---
 * default: false
 * options:
 *   - update
 *   - delete-insert
 *   - skip
 * ---
 *
 * ## EXAMPLES
 *
 *     wp catfish findmissing
 *
 */

// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish findmissing', function () {
	// Let the scan run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	\WP_CLI::line( 'Finding posts that exist in Clock but not in Pages...' );

	Sync::findMissing( true, 'update' );

	\WP_CLI::success( 'Scan complete' );
} );

/**
 * Find posts that are in Pages but not in Clock
 *
 * ## DESCRIPTION
 *
 * Checks sitemap and the WordpressRedirects database and finds posts which exists in
 * Pages but aren't in the Clock sitemaps
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     wp catfish findadditional
 *
 */


// Register command with \WP_CLI
\WP_CLI::add_command( 'catfish findadditional', function ( array $args ) {
	// Let the scan run FOREVER
	set_time_limit( 0 );
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
	error_reporting( E_ERROR | E_WARNING | E_PARSE );

	\WP_CLI::line( 'Finding posts that exist in Pages but not in Clock...' );

	try {

		Sync::findAdditional();

		\WP_CLI::success( 'Scan complete' );
	} catch ( Exception $e ) {
		\WP_CLI::error( $e->getMessage() );
	}
} );
