<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Query;
use AgreableCatfishImporterPlugin\Services\Post;

class Sync {

  public static function getCategories() {
    $site_url = get_field('catfish_website_url', 'option');
    return Sitemap::getCategoriesFromIndex($site_url . 'sitemap-index.xml');
  }

  /**
   * Import multiple posts from category
   */
  public static function importCategory($categorySitemap, $limit = 10, $mostRecent = true, $speedtest = false) {
    $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);
    $response = new stdClass();
    $response->posts = [];
    if ($limit !== -1) {
      $postUrls = array_slice($postUrls, 0, $limit);
    }

    foreach($postUrls as $postUrl) {
      if ($post = Post::getPostFromUrl($postUrl, $speedtest)) {
        $postResponse = new stdClass();
        $postResponse->id = $post->ID;
        $postResponse->url = $postUrl;
        // If running speedtest then return the entire post object including the speedtest data
        if($speedtest) {
          $postResponse->post_type = $post->post_type;
          $postResponse->speedtest = $post->speedtest;
        }
        $response->posts[] = $postResponse;
      }
    }

    return $response;
  }

  public static function importUrl($url) {
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
   * Return stats on total posts imported
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

  /**
   * Runs a speedtest on a subset of imports
   */
  // Surfaced two errors:
  // Undefined index: file in mesh-image
  // Widget recursion bug in Html
  // Widget DOM not pased as an object
  // $image_info['file'] not set
  public static function runSpeedtest() {

    // Set variables for speedtest
    $categoriesToTest = 5;
    $postsToTest = 5;
    $randomiseTest = true;
    $clockPostTotal = 23245;
    // The follow variables repeat the Widget recursion bug:
    // $categoriesToTest = 2;
    // $postsToTest = 2;
    // $randomiseTest = false;

    // Setup response object
    $speedTest = new stdClass();
    $speedTest->data = [];
    $speedTest->total = 0;

    $speedAverage = array();
    $speedTypeAverage = array();

    // Get list of categories
    $categories = self::getCategories();

    // limit categories
    if($randomiseTest) {
      shuffle($categories);
    }
    $categories = array_slice($categories, 0, $categoriesToTest);

    foreach($categories as $categoryUrl) {

      // Import a subset of posts using importCategory enabling speedtest logging
      // -1 to get all categories
      $categoryPosts = self::importCategory($categoryUrl, $postsToTest, false, true);

      // Check if category has posts associated with it.
      if(!empty($categoryPosts->posts)) {
        // Loop through each article
        foreach ($categoryPosts->posts as $post) {
          // Foreach article speed to array for overall average and array for average by post type
          $speedAverage[] = $post->speedtest['time'];
          $speedTypeAverage[$post->post_type][] = $post->speedtest['time'];

          // Add to the post count
          $speedTest->total = $speedTest->total + 1;
        }
      }

    }

    // die(var_dump($speedAverage, $speedTypeAverage));
    $speedTest->data['speedData'] = $speedAverage;
    $speedTest->data['speedAverageData'] = $speedTypeAverage;

    $speedTest->totalLoadTime = array_sum($speedAverage);
    $speedTest->averageLoad = array_sum($speedAverage) / count($speedAverage);

    $speedTest->estimatedImportTime = $speedTest->averageLoad * $clockPostTotal;

    foreach ($speedTypeAverage as $type => $testData) {
      $speedTest->averageLoadByType[$type] = array_sum($testData) / count($testData);
    }

    return $speedTest;
  }
}
