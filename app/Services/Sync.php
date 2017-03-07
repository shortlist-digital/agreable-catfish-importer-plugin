<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Query;
use \WP_CLI;

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Queue;
use AgreableCatfishImporterPlugin\Services\Worker;

new Queue; // Setup Queue connection

class Sync {

  /**
   * Queue Functions (Called from admin)
   */

  /**
   * Queue Category / All
   */
  public static function queueCategory($categorySitemap = 'all', $onExistAction = 'update') {
   // Push item into Queue
   if(is_string(Queue::push('importCategory', array('url' => $categorySitemap, 'onExistAction' => $onExistAction)))) {
     // Return post url
     return $categorySitemap;
   }
   // If it failed return false
   return false;
  }

  /**
   * Queue Single URL
   */
  public static function queueUrl($url, $onExistAction = 'update') {
   // Push item into Queue
   if(is_string(Queue::push('importUrl', array('url' => $url, 'onExistAction' => $onExistAction)))) {
     // Return post url
     return $url;
   }
   // If it failed return false
   return false;
  }

  /**
   * Push a Test Item to Queue
   */
  public static function pushTestItemToQueue($data, $payload) {
    // Push a new test post into queue
    Queue::push('queueTest', array('url' => 'http://example.com', 'onExistAction' => 'update'));
  }

  /**
   * Worker Functions (Run by worker to consume queue items)
   */

  /**
   * Function for testing queue
   */
  public static function queueTest($data, $payload) {
    var_dump('queueTest ran successfully', $payload);
  }

  /**
   * Consume single queue item
   */
  public static function actionSingleQueueItem($cli = false) {

    if($cli) {
      WP_CLI::line('actionSingleQueueItem');
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
      // getenv('AWS_SQS_QUEUE') - queue name
      // delay
      // time before retries
      // max number of tries

      $worker->pop('default', getenv('AWS_SQS_QUEUE'), 0, 3, 0);
    } catch (Exception $e) {
      throw new Exception("Error processing single queue item.", 1); // $e
    }

  }

  /**
   * Consume queue items by worker
   */
  public static function actionQueue($cli = false) {

    if($cli) {
      WP_CLI::line('actionQueue');
    }

    // Get queue object
    $queue = new Queue;

    // Connect to the AWS SQS Queue
    $worker = new Worker($queue->getQueueManager());

    // Pass cli status to worker class
    if($cli) {
      $worker->cli = true;
    }

    // Run indefinitely
    while (true) {

      try {
        // Parameters:
        // 'default' - connection name
        // getenv('AWS_SQS_QUEUE') - queue name
        // delay
        // time before retries
        // max number of tries

        $worker->pop('default', getenv('AWS_SQS_QUEUE'), 0, 3, 0);
      } catch (Exception $e) {
        if($cli) {
          WP_CLI::error('Error processing next queue item.');
        }
        throw new Exception("Error processing next queue item.", 1); // $e
      }

      // Flush to show output
      flush();
    }
  }

  /**
   * Consume queue items by worker
   */
  public static function purgeQueue($cli = false) {

    if($cli) {
      WP_CLI::line('purgeQueue');
    }

    // Get queue object
    $queue = new Queue;

    // Connect to the AWS SQS Queue
    $worker = new Worker($queue->getQueueManager());

    try {
      // Parameters:
      // 'default' - connection name
      // getenv('AWS_SQS_QUEUE') - queue name

      $worker->purge('default', getenv('AWS_SQS_QUEUE'));
    } catch (Exception $e) {
      if($cli) {
        WP_CLI::error('Error processing next queue item.');
      }
      throw new Exception("Error processing next queue item.", 1); // $e
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
  public static function importCategory($data, $payload) {

    // Extract attributes from payload.
    $categorySitemap = $payload['url'];
    $onExistAction = $payload['onExistAction'];

    if($categorySitemap == 'all') {
      // Handle adding all to queue
      $postUrls = array();
      foreach (Sitemap::getCategoriesFromIndex() as $categorySitemap) {
        $postUrls = array_merge($postUrls, Sitemap::getPostUrlsFromCategory($categorySitemap));
      }

    } else {
      $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);
    }

    // Queue all posts
    foreach($postUrls as $postUrl) {
      self::queueUrl($postUrl, $onExistAction);
    }

    // Return list of post Urls
    return $postUrls;
  }

  /**
   * Import a single post from given URL
   */
  public static function importUrl($data, $payload) {

    // Extract attributes from payload.
    $url = $payload['url'];
    $onExistAction = $payload['onExistAction'];

    $response = new stdClass();
    $response->success = false;

    $post = Post::getPostFromUrl($url);

    if ($post) {
      $postResponse = new stdClass();
      $postResponse->id = $post->ID;
      $postResponse->url = $url;
      $response->post = $postResponse;
      $response->success = true;
    }

    return $response;
  }

  /**
   * Admin Option/Status Functions
   */

  /**
   * Return a list of categories to import in admin
   */
   public static function getCategories() {
     $site_url = get_field('catfish_website_url', 'option');
     // Add and option for all categories
     return array_merge(array('all'), Sitemap::getCategoriesFromIndex($site_url . 'sitemap-index.xml'));
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

    $categories = self::getCategories();
    foreach($categories as $categoryUrl) {
      $categoryStatus = self::getImportCategoryStatus($categoryUrl);

      $totalStatus->importedCount += $categoryStatus->importedCount;
      $totalStatus->total += $categoryStatus->categoryTotal;
    }

    return $totalStatus;
  }

}
