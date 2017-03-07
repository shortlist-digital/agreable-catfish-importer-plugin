<?php

use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Queue;

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
  if(!isset($args[0])) {
    WP_CLI::error("You must pass a post or sitemap url to the catfish queue command. eg. http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken");
  }

  if($args[0] == 'all' || strstr($args[0], '.xml')) {
    WP_CLI::line('Queueing category.');

    Sync::queueCategory($args[0]); // TODO: Handle onExistAction

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
 * Action Items in the Catfish Queue.
 *
 * ## DESCRIPTION
 *
 * Allows execution of the Catfish Importer queue from the command line.
 *
 * ## OPTIONS
 *
 * ## EXAMPLES
 *
 *     # Listen and action queue
 *     wp catfish listen
 *
 */
function actionQueue(array $args) {
  // Let the queue run FOREVER
  set_time_limit(0);

  WP_CLI::line('Listening to queue...');

  Sync::actionQueue(true);
}

// Register command with WP_CLI
WP_CLI::add_command('catfish listen', 'actionQueue');

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

  WP_CLI::line('Working on queue...');

  Sync::actionSingleQueueItem(true);
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
  WP_CLI::confirm( "Are you sure you want to DELETE ALL ITEMS from the queue?", $args );

  WP_CLI::line('Purging the queue...');

  Sync::purgeQueue(true);
}

// Register command with WP_CLI
WP_CLI::add_command('catfish purge', 'purgeQueue');
