<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Query;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Queue;
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
     if(is_string(Queue::push('ImportCategory', array('url' => $categorySitemap, 'onExistAction' => $onExistAction)))) {
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
     if(is_string(Queue::push('ImportPost', array('url' => $url, 'onExistAction' => $onExistAction)))) {
       // Return post url
       return $url;
     }
     // If it failed return false
     return false;
   }

  /**
   * Import Functions (Consumed by queue worker)
   */

   /**
    * Take import category queue item and split into ImportPost Queue items
    */
   public static function importCategory($categorySitemap, $onExistAction = 'update', $limit = 10, $mostRecent = true) {

     if($categorySitemap == 'all') {
       // Handle adding all to queue
       $postUrls = array();
       foreach (Sitemap::getCategoriesFromIndex() as $categorySitemap) {
         $postUrls = array_merge($postUrls, Sitemap::getPostUrlsFromCategory($categorySitemap));
       }

     } else {
       $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);
     }

     // Limit posts based on argument
     if ($limit !== -1) {
       $postUrls = array_slice($postUrls, 0, $limit);
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
  public static function importUrl($url, $onExistAction = 'update') {
    $response = new stdClass();
    $response->success = false;
    if ($post = Post::getPostFromUrl($url)) {
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
