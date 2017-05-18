<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Query;
use \WP_CLI;

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Queue;

use Aws\Sqs\SqsClient;

use \Bugsnag\Client;

new Queue; // Setup Queue connection

use Exception;

class Sync {

  /**
   * Queue Functions (Called from admin)
   */

  /**
   * Queue Category / All
   */
  // NOTE this takes too long to run and is released back into the queue and duplicated
  // for that reason all large imports should be run from the command line.

  /**
   * Queue Single URL
   */
  public static function queueUrl($url, $onExistAction = 'update') {
    try {
      // Push item into Queue
      // New method using direct sdk so we can play nicely with S3 Offload
      $response = Queue::push(array('job' => 'importUrl', 'data' => array('url' => $url, 'onExistAction' => $onExistAction)));

      return $response->MessageId;

    } catch (Exception $e) {
      // Catch errors for easy debugging in BugSnag
      if($cli) {
        WP_CLI::error("Error in queueUrl adding importUrl to queue. " . $e->getMessage());
      }

      // Send handled error to BugSnag otherwise
      // TODO consider moving this to extension of the Exception class
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);
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

    // Pass cli status to worker class
    if($cli) {
      Queue::$cli = true;
    }

    try {

      // Pop and execute item from queue
      $response = Queue::pop();

      // Queue item ran successfully, ping the Envoyer heartbeat URL to stay we're still alive
      file_get_contents(getenv('ENVOYER_HEARTBEAT_URL_IMPORTER'));

      return $return;

    } catch (Exception $e) {
      if($cli) {
        WP_CLI::error("Error in the Worker library while actioning single queue item. Queue item may have exceeded maxTries. " . $e->getMessage());
      }
      // Send handled error to BugSnag as well..
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);
    }

  }

  /**
   * Consume queue items by worker
   */
  public static function purgeQueue($cli = false) {

    if($cli) {
      WP_CLI::line('Purging the queue.');
    }

    try {

      // Super clean purge function with native aws api
      Queue::purge();

      if($cli) {
        WP_CLI::success('Ethan Hawke is complete.');
      }

    } catch (Exception $e) {

      if($cli) {
        WP_CLI::error("Error purging the queue. " . $e->getMessage());
      }
      // Send handled error to BugSnag as well..
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);
    }

  }

  /**
   * Import Functions (Consumed by queue worker)
   */

  /**
   * Take import category queue item and split into ImportPost Queue items
   */
  public static function importCategory($data, $payload, $cli = false) {

    try {

      // Extract attributes from payload.
      $categorySitemap = $payload['url'];
      $onExistAction = $payload['onExistAction'];
      // Optional since param.
      $since = ( isset($payload['since']) ) ? $payload['since'] : false ;

      if($cli) {
        WP_CLI::line('Splitting category to separate queue items: ' . $categorySitemap);
      }

      if($categorySitemap == 'all') {

        // Handle adding all to queue
        $postUrls = array();

        // Get all sitemaps
        $site_url = getenv('CATFISH_IMPORTER_TARGET_URL');
        $allSitemaps = Sitemap::getUrlsFromSitemap($site_url . 'sitemap-index.xml', false, $cli);

        foreach ($allSitemaps as $categorySitemap) {
          $urlsToMerge = Sitemap::getUrlsFromSitemap($categorySitemap, $since, $cli);

          if(is_array($urlsToMerge)) {
            $postUrls = array_merge($postUrls, $urlsToMerge);
          }
        }

      } else {
        $postUrls = Sitemap::getUrlsFromSitemap($categorySitemap);
      }

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
      }
      // Send handled error to BugSnag as well..
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);

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

      // Get Slug to prefix log messages for tracking the journey of a post
      $log_identifier = parse_url($url);
      $log_identifier = $log_identifier['path'];
      $log_identifier = "\033[45m ".$log_identifier." \033[0m "; // Add colours like a pro

      if($cli) {
        WP_CLI::line($log_identifier.'Starting to import url');
      }

      $post = Post::getPostFromUrl($url, $onExistAction, true, $log_identifier);

      if($cli) {
        WP_CLI::line($log_identifier.'Finished importing url');
      }

      // Catch uncaught failure in the Post class
      if(!is_object($post)) {
        if($cli) {
          WP_CLI::error($log_identifier."Error, post returned is not an object. " . $post);
        }
        throw new Exception($log_identifier."Error, post returned is not an object. " . $post);
      }

      // Pass the log_identifier forward
      $post->log_identifier = $log_identifier;

      // Return the post object if successfull
      return $post;

    } catch (Exception $e) {

      if($cli) {
        WP_CLI::error($log_identifier."Error importing post from url using Posts class. " . $e->getMessage());
      }

      // Send handled error to BugSnag as well..
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->setReleaseStage(WP_ENV);
      // Pass the post that is seeing this error
      $bugsnag->setMetaData([
          'log_identifier' => $log_identifier // Pass the error with
        ]);
      $bugsnag->notifyException($e);

      // Could delete partial post if a post has been created here...

    }
  }

  /**
   * Admin Option/Status Functions
   */

  /**
   * Return a list of categories to import in admin
   */
   public static function getCategories($forFrontEnd = true) {
     $site_url = getenv('CATFISH_IMPORTER_TARGET_URL');
     if($forFrontEnd) {
       // If this is called for the admin user interface
       $categories = array_merge(array('all'), Sitemap::getUrlsFromSitemap($site_url . 'sitemap-index.xml'));
     } else {
       $categories = Sitemap::getUrlsFromSitemap($site_url . 'sitemap-index.xml');
     }

     // Add and option for all categories
     return $categories;
   }

  /**
   * Get % progress of posts imported from selected category
   *
   * $categorySitemap  The sitemap of the category to count
   * $wordpressPostTotalScope  Defines whether the function should return the total of all posts in the database or the total only from that category passed as $categorySitemap
   */
  public static function getImportCategoryStatus($categorySitemap) {

    // If categorySitemap is set to all then count all using getImportStatus and exit
    if($categorySitemap == 'all') {
      return self::getImportStatus();
    }

    $postUrls = Sitemap::getUrlsFromSitemap($categorySitemap);

    // http://www.stylist.co.uk/sitemap/life.xml > life
    $categorySlug = substr($categorySitemap, strrpos($categorySitemap, '/') + 1);
    $categorySlug = str_replace('.xml', '', $categorySlug);

    $query = array(
      'post_type' => 'post',
      // These two fields speed up a count only query massively by only returning the id
      'fields' => 'ids',
      // Return all posts at once.
      'posts_per_page' => -1,
      'post_status' => array('publish'),
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

    // Set up response object
    $totalStatus = new stdClass();
    $totalStatus->importedCount = 0;
    $totalStatus->total = 0;

    // Get a list of all category sitemaps
    $categories = self::getCategories(false);

    // Get a total posts listed in Clocks sitemap.xml files by counting through each one!
    foreach($categories as $categoryUrl) {
      $categoryStatus = self::getImportCategoryStatus($categoryUrl);

      $totalStatus->total += $categoryStatus->categoryTotal;
    }

    // Get the total imported posts across all categories...
    $query = array(
      'post_type' => 'post',
      // These two fields speed up a count only query massively by only returning the id
      'fields' => 'ids',
      // Return all posts at once.
      'posts_per_page' => -1,
      'post_status' => array('publish'),
      'meta_query' => array(
        array(
          'key' => 'catfish_importer_imported',
          'value' => true
        )
      )
    );

    $status = new stdClass();

    $query = new WP_Query($query);

    $totalStatus->importedCount = $query->post_count;

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

    if($cli) {
      WP_CLI::line('Importing posts since last post on '.$since);
    }

    // Format as unix time
    $since = strtotime($since);

    // The following gitignored file let's you check when the last scan ran.
    if(WP_ENV !== 'producton') {
      $test_run_file = fopen("/tmp/cron_last_run.tmp", "w") or die("Unable to open file!");
      fwrite($test_run_file, "Last run: ".time());
      fclose($test_run_file);
    }

    // Simulate SQS payload
    $payload = array(
      'url' => all,
      'onExistAction' => 'update',
      'since' => $since
    );
    $data = $payload;

    try {
      // Queue up posts from each category since the last successfull import
      // Import from all categories since...
      $return = self::importCategory($data, $payload, $cli);

      // Queue item ran successfully, ping the Envoyer heartbeat URL to stay we're still alive
      file_get_contents(getenv('ENVOYER_HEARTBEAT_URL_UPDATED_POSTS_SCANNER'));

      return $return;

    } catch (Exception $e) {
      // Show error to cli users
      if($cli) {
        WP_CLI::line("Error scanning and importing new posts. " . $e->getMessage());
      }
      // Catch errors for easy debugging in BugSnag
      // Send handled error to BugSnag as well..
      $this->get('bugsnag')->setReleaseStage(WP_ENV);
      $bugsnag = \Bugsnag\Client::make(getenv('BUGSNAG_API_KEY'));
      $bugsnag->notifyException($e);
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
      'meta_key'  => 'catfish_importer_post_date',
      // 'meta_value'  => true,
      'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
      'posts_per_page' => 1, // Return just 1 post.
      'orderby' => 'date',
      'order' => 'DESC'
    ]);

    if ( $query->have_posts() ) {
      // You have imported posts with Catfish before so return the most recent import time
      $posts = $query->get_posts();
      $meta = get_post_meta($posts[0]->ID);

      return $meta['catfish_importer_post_date'][0];
    } else {
      // You are on a fresh install with 0 Catfish imported posts!
      // Return a date far in the past so we always import all content in this case.
      return strtotime('-5 years');
    }

  }

  /**
   * Escape API Url Paths
   *
   * Handle special characters in post import urls
   * (e.g. http://www.shortlist.com/entertainment/netflix picks.json)
   */
  public static function escapeAPIUrlPaths($originalJsonUrl) {

    $urlToEscape = parse_url($originalJsonUrl);

    // Create a url encoded path
    $escapedUrlPath = explode('/', $urlToEscape['path']);
    foreach ($escapedUrlPath as &$url_element) {
      $url_element = rawurlencode($url_element);
    }
    $escapedUrlPath = implode('/', $escapedUrlPath);

    // Find and replace the old version of the path with the next escaped version
    return str_replace($urlToEscape['path'], $escapedUrlPath, $originalJsonUrl);

  }

  /**
   * Find missing
   */
  public static function findMissing($queueMissing = false, $onExistAction = 'update') {

    // Collect all $postUrls in one array to check against the WP database
    $postUrls = array();
    $missingPostUrls = array();

    // Get a list of all category sitemaps
    $categories = self::getCategories(false);

    // Go through all of Clocks sitemap.xml files to get all of the post urls
    foreach($categories as $categoryUrl) {

      WP_CLI::line('Checking: '.$categoryUrl);

      $posts = Sitemap::getUrlsFromSitemap($categoryUrl);

      if(is_array($posts)) {
        $postUrls = array_merge($postUrls, $posts);
      }
    }

    foreach ($postUrls as $url) {

      // Check if each post exists
      $query = array(
        // Return all posts at once.
        'posts_per_page' => 1,
        'meta_query' => array(
          array(
            'key' => 'catfish_importer_url',
            'value' => $url
          )
        )
      );

      $output = new WP_Query($query);

      if( $output->post_count == 0 ) {
        WP_CLI::line("Missing post: ".$url);
        $missingPostUrls[] = $url;

        if($queueMissing) {
          WP_CLI::line("Queing for import");
          self::queueUrl($url, $onExistAction);
        }
      }

    }

    WP_CLI::line("Total missing posts ". count($missingPostUrls));

  }

  /**
   * Find additional
   */
  public static function findAdditional() {

    // Collect all $postUrls in one array to check against the WP database
    $postUrls = array();
    $additionalPostUrls = array();

    // Get a list of all category sitemaps
    $categories = self::getCategories(false);

    // Go through all of Clocks sitemap.xml files to get all of the post urls
    foreach($categories as $categoryUrl) {

      WP_CLI::line('Checking: '.$categoryUrl);

      $posts = Sitemap::getUrlsFromSitemap($categoryUrl);

      if(is_array($posts)) {
        $postUrls = array_merge($postUrls, $posts);
      }
    }


    WP_CLI::line('Getting list of importer posts in Pages.');

    // Get the total imported posts across all categories...
    $query = array(
      'post_type' => 'post',
      // These two fields speed up a count only query massively by only returning the id
      'fields' => 'ids,catfish_importer_imported',
      // Return all posts at once.
      'posts_per_page' => -1,
      'post_status' => array('publish'),
      'meta_query' => array(
        array(
          'key' => 'catfish_importer_imported',
          'value' => true
        )
      )
    );

    $query = new WP_Query($query);
    $additionalPosts = [];

    foreach($query->have_posts() as $post) {
      $meta = get_post_meta($post->ID);

      WP_CLI::line('Checking: '.$meta['catfish_importer_url'][0]);

      if(!in_array($meta['catfish_importer_url'][0], $postUrls)) {
        $additionalPosts[] = $meta['catfish_importer_url'][0];
      }

    }

    WP_CLI::line(count($additionalPosts).' posts exist in Pages but not Clock:');

    foreach($additionalPosts as $url) {
      WP_CLI::line($url);
    }
  }
}
