<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Query;
use \WP_CLI;

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Queue;
use AgreableCatfishImporterPlugin\Services\Worker;

new Queue; // Setup Queue connection

use Exception;

class Sync {

  /**
   * Queue Functions (Called from admin)
   */

  /**
   * Queue Category / All
   */
  public static function queueCategory($categorySitemap = 'all', $onExistAction = 'update') {
    try {
      // Push item into Queue
      return Queue::push('importCategory', array('url' => $categorySitemap, 'onExistAction' => $onExistAction));
    } catch (Exception $e) {
      // Catch errors for easy debugging in BugSnag
      throw new Exception("Error in queueUrl adding inportCategory to queue. " . $e->getMessage());
    }
  }

  /**
   * Queue Single URL
   */
  public static function queueUrl($url, $onExistAction = 'update') {
    try {
      // Push item into Queue
      return Queue::push('importUrl', array('url' => $url, 'onExistAction' => $onExistAction));
    } catch (Exception $e) {
      // Catch errors for easy debugging in BugSnag
      throw new Exception("Error in queueUrl adding inportUrl to queue. " . $e->getMessage());
    }
  }

  /**
   * Worker Functions (Run by worker to consume queue items)
   */

  /**
   * Consume single queue item
   */
  public static function actionSingleQueueItem($cli = false) {

    if($cli) {
      WP_CLI::line('Poping item from queue.');
    }

    // Get queue object
    $queue = new Queue;

    // Connect to the AWS SQS Queue
    $worker = new Worker($queue->getQueueManager());

    // Pass cli status to worker class
    if($cli) {
      $worker->cli = true;
    }

    try {
      // Parameters:
      // 'default' - connection name
      // getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE') - queue name
      // delay
      // time before retries
      // max number of tries

      $worker->pop('default', getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE'), 0, 3, 0);
    } catch (Exception $e) {
      throw new Exception("Unhandled error in the Worker library while actioning single queue item. Queue item may have exceeded maxTries " . $e->getMessage());
    }

  }

  /**
   * Consume queue items by worker
   */
  public static function purgeQueue($cli = false) {

    if($cli) {
      WP_CLI::line('Purging the queue.');
    }

    // Get queue object
    $queue = new Queue;

    // Connect to the AWS SQS Queue
    $worker = new Worker($queue->getQueueManager());

    try {
      // Parameters:
      // 'default' - connection name
      // getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE') - queue name

      $worker->purge('default', getenv('AWS_SQS_CATFISH_IMPORTER_QUEUE'), $cli);
    } catch (Exception $e) {
      if($cli) {
        WP_CLI::error("Error processing next queue item.");
      }
      throw new Exception("Error processing next queue item.");
    }

    if($cli) {
      WP_CLI::success('Ethan Hawke is complete.');
    }
  }

  /**
   * Import Functions (Consumed by queue worker)
   */

  /**
   * Take import category queue item and split into ImportPost Queue items
   */
  public static function importCategory($data, $payload, $cli = false, $since = false) {

    try {

      // Extract attributes from payload.
      $categorySitemap = $payload['url'];
      $onExistAction = $payload['onExistAction'];

      if($cli) {
        WP_CLI::line('Splitting category to separate queue items: ' . $categorySitemap);
      }

      if($categorySitemap == 'all') {

        // Handle adding all to queue
        $postUrls = array();

        // Get all sitemaps
        $site_url = get_field('catfish_website_url', 'option');
        $allSitemaps = Sitemap::getPostUrlsFromCategory($site_url . 'sitemap-index.xml');

        foreach ($allSitemaps as $categorySitemap) {
          $urlsToMerge = Sitemap::getPostUrlsFromCategory($categorySitemap, $since);

          if(is_array($urlsToMerge)) {
            $postUrls = array_merge($postUrls, $urlsToMerge);
          }
        }

      } else {
        $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);
      }

      // die(var_dump('Choosing to import these urls: ', $postUrls));

      $queueIDs = array();

      // Tell the command line user how many posts your are about to queue
      if($cli) {
        $startTime = microtime(true);
        $totalToQueue = count($postUrls);
        $currentPost = 0;
        WP_CLI::line("Pushing " . $totalToQueue . " individual importPost items into the queue.");
      }

      // Queue all posts
      foreach($postUrls as $postUrl) {
        $queueIDs[] = self::queueUrl($postUrl, $onExistAction);

        // Show progress to the command line
        if($cli) {
          $currentPost++;
          WP_CLI::line("Pushed " . $currentPost . " out of " . $totalToQueue . " into the queue.");
        }
      }

      // Show how long it took to process the category to queue
      if($cli) {
        $endTime = microtime(true);
        $time = $endTime - $startTime;
        WP_CLI::line("Category queue took " . $time . " seconds.");
      }

      // Return list of post Urls
      return $queueIDs;

    } catch (Exception $e) {

      if($cli) {
        WP_CLI::error("Error adding multiple importQueue queue items based on the category sitemap. " . $e->getMessage());
        var_dump(debug_backtrace()); // Show a backtrace here
      }
      throw new Exception("Error adding multiple importQueue queue items based on the category sitemap. " . $e->getMessage());

    }

  }

  /**
   * Import a single post from given URL
   */
  public static function importUrl($data, $payload, $cli = false) {

    try {

      // Extract attributes from payload.
      $url = $payload['url'];
      $onExistAction = $payload['onExistAction'];

      if($cli) {
        WP_CLI::line('Importing url: ' . $url);
      }

      $post = Post::getPostFromUrl($url, $onExistAction);

      // Catch uncaught failure in the Post class
      if(!is_object($post)) {
        if($cli) {
          WP_CLI::error("Post returned is not an object. " . $post);
        }
        throw new Exception("Post returned is not an object. " . $post);
      }

      // Return the post object if successfull
      return $post;

    } catch (Exception $e) {

      if($cli) {
        WP_CLI::error("Error importing post from url using Posts class. " . $e->getMessage());
      }
      throw new Exception("Error importing post from url using Posts class. " . $e->getMessage());

    }
  }

  /**
   * Admin Option/Status Functions
   */

  /**
   * Return a list of categories to import in admin
   */
   public static function getCategories($forFrontEnd = true) {
     $site_url = get_field('catfish_website_url', 'option');
     if($forFrontEnd) {
       // If this is called for the admin user interface
       $categories = array_merge(array('all'), Sitemap::getPostUrlsFromCategory($site_url . 'sitemap-index.xml'));
     } else {
       $categories = Sitemap::getPostUrlsFromCategory($site_url . 'sitemap-index.xml');
     }

     // Add and option for all categories
     return $categories;
   }

  /**
   * Get % progress of posts imported from selected category
   */
  public static function getImportCategoryStatus($categorySitemap) {
    $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);

    // http://www.stylist.co.uk/sitemap/life.xml > life
    $categorySlug = substr($categorySitemap, strrpos($categorySitemap, '/') + 1);
    $categorySlug = str_replace('.xml', '', $categorySlug);

    $query = array(
      'post_type' => 'post',
      'category_name' => $categorySlug,
      'meta_query' => array(
        array(
          'key' => 'catfish_importer_imported',
          'value' => true
        )
      )
    );

    $status = new stdClass();

    $query = new WP_Query($query);
    $status->importedCount = $query->post_count;
    $status->categoryTotal = count($postUrls);

    return $status;
  }

  /**
   * Get total progress if import
   */
  public static function getImportStatus() {

    $totalStatus = new stdClass();
    $totalStatus->importedCount = 0;
    $totalStatus->total = 0;

    $categories = self::getCategories(false);
    foreach($categories as $categoryUrl) {
      $categoryStatus = self::getImportCategoryStatus($categoryUrl);

      $totalStatus->importedCount += $categoryStatus->importedCount;
      $totalStatus->total += $categoryStatus->categoryTotal;
    }

    return $totalStatus;
  }

  /**
   * Updated Post functions
   */

  /**
   * Updated Post Scan
   *
   * Checks Clock for any posts that have been updated since the function was last run and imports them
   */
  public static function updatedPostScan($cli = false) {

    $since = self::getLastUpdatedRunDate();

    // If you want to test if the cron is running regularaly then you can uncomment the following.
    // $test_run_file = fopen("cron_run_test.txt", "w") or die("Unable to open file!");
    // fwrite($test_run_file, "Last run: ".time());
    // fclose($test_run_file);

    // Simulate SQS payload
    $payload = array(
      'url' => all,
      'onExistAction' => 'update'
    );
    $data = $payload;

    try {
      // Queue up posts from each category since the last successfull import
      return self::importCategory($data, $payload, $cli, $since);
    } catch (Exception $e) {
      // Catch errors for easy debugging in BugSnag
      throw new Exception("Error scanning and importing new posts. " . $e->getMessage());
    }
  }

  /**
   * Get the last time the updater was run
   *
   * catfish_importer_date_updated
   */
  public function getLastUpdatedRunDate() {
    $query = new WP_Query([
      'post_type' => 'post',
      'meta_key'  => 'catfish_importer_date_updated',
      // 'meta_value'  => true,
      'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
      'posts_per_page' => 1, // Return just 1 post.
      'orderby' => 'modified'
    ]);

    if ( $query->have_posts() ) {
      // You have imported posts with Catfish before so return the most recent import time
      $posts = $query->get_posts();
      $meta = get_post_meta($posts[0]->ID);

      return $meta['catfish_importer_date_updated'][0];
    } else {
      // You are on a fresh install with 0 Catfish imported posts!
      // Return a date far in the past so we always import all content in this case.
      return strtotime('-5 years');
    }

  }

}
