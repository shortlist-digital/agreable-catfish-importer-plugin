<?php

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Queue;

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
function generateRandomKey() {
  WP_CLI::line('Add the following link to your .env:');
  WP_CLI::line('ILLUMINATE_ENCRYPTOR_KEY=' . substr(base64_encode(sha1(mt_rand())), 0, 32) );
}

// Register command with WP_CLI
WP_CLI::add_command('catfish generatekey', 'generateRandomKey');

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
function testException() {
  // Show my errors
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);

  try {
    // The Exception handler should log with Bugsnag
    throw new Exception("Bugsnag Test Exception");
  } catch (Exception $e) {
    // Send handled error to BugSnag as well..
    $bugsnag = Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
    $bugsnag->notifyException($e);
    $bugsnag->notifyError('TestError', 'Something bad happened');
  }
}

// Register command with WP_CLI
WP_CLI::add_command('catfish testexception', 'testException');

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
 * ## EXAMPLES
 *
 *     # Add all a specified post
 *     wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken
 *
 *     # Import a category of posts
 *     wp catfish queue http://www.shortlist.com/sitemap/entertainment/48-hours-to.xml
 *
 *     # Import all posts
 *     wp catfish all
 *
 */
function addToQueue(array $args) {

  // Catch incorrect useage of command which could lead to adding plain text to queue
  if(in_array($args[0], array('work', 'clear', 'purge'))) {
    WP_CLI::error('Commands aren\'t nested. You should use "wp catfish '.$args[0].'" instead of "wp catfish queue '.$args[0].'".');
    return;
  }

  if(!isset($args[0])) {
    WP_CLI::error("You must pass a post or sitemap url to the catfish queue command. eg. http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken");
    return;
  }

  if($args[0] == 'all' || strstr($args[0], '.xml')) {
    WP_CLI::line('Queueing category.');

    // Queue action is too long to run without being released back into the queue.
    // Instead run all large queue adds on the command line
    Sync::importCategory('', array('url' => $args[0], 'onExistAction' => 'update'), true);

    WP_CLI::success('Queued: ' . $args[0]);
  } else {
    WP_CLI::line('Queueing post.');

    Sync::queueUrl($args[0]); // TODO: Handle onExistAction

    WP_CLI::success('Queued: ' . $args[0]);
  }
}

// Register command with WP_CLI
WP_CLI::add_command('catfish queue', 'addToQueue');

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
function actionSingleQueueItem(array $args) {
  // Let the queue run FOREVER
  set_time_limit(0);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);

  if(!getenv('ENVOYER_HEARTBEAT_URL_IMPORTER') || getenv('ENVOYER_HEARTBEAT_URL_IMPORTER') == '') {
    throw new Exception("ENVOYER_HEARTBEAT_URL_IMPORTER is not set in your .env file");
    return;
  }

  WP_CLI::line('Working on queue...');

  try {
    Sync::actionSingleQueueItem(true);
  } catch (\Exception $e) {
    WP_CLI::error(var_dump($e));
  }
}

// Register command with WP_CLI
WP_CLI::add_command('catfish work', 'actionSingleQueueItem');

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
function purgeQueue(array $args) {

  // Let the queue run FOREVER
  set_time_limit(0);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);

  WP_CLI::confirm( "Are you sure you want to DELETE ALL ITEMS from the queue?", $args );

  try {
    Sync::purgeQueue(true);
  } catch (\Exception $e) {
    WP_CLI::error(var_dump($e));
  }

}

// Register command with WP_CLI
WP_CLI::add_command('catfish purge', 'purgeQueue');

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
function deleteAllAutomatedTestingPosts(array $args) {
  // Let the queue run FOREVER
  set_time_limit(0);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);

  // WP_CLI::confirm( "Are you sure you want to DELETE ALL POSTS marked as automated_testing?", $args );

  WP_CLI::line('Clearing automated_testing post from the queue...');
  try {
    Post::deleteAllAutomatedTestingPosts(true);
  } catch (Exception $e) {
    WP_CLI::error(var_dump($e));
  }

}

// Register command with WP_CLI
WP_CLI::add_command('catfish clearautomatedtesting', 'deleteAllAutomatedTestingPosts');

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
 *     # Listen and action queue
 *     wp catfish scanupdates
 *
 */
function callUpdatedPostScan(array $args) {
  // Let the scan run FOREVER
  set_time_limit(0);
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ERROR | E_WARNING | E_PARSE);

  WP_CLI::line('Scanning for new updates in Clock...');

  try {
    Sync::updatedPostScan(true);

    WP_CLI::success('Scan complete');
  } catch (Exception $e) {
    WP_CLI::error($e->getMessage());
  }
}

// Register command with WP_CLI
WP_CLI::add_command('catfish scanupdates', 'callUpdatedPostScan');
