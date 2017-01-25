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

  public static function importCategory($categorySitemap, $limit = 10, $mostRecent = true) {
    $postUrls = Sitemap::getPostUrlsFromCategory($categorySitemap);
    $response = new stdClass();
    $response->posts = [];
    if ($limit !== -1) {
      $postUrls = array_slice($postUrls, 0, $limit);
    }

    foreach($postUrls as $postUrl) {
      if ($post = Post::getPostFromUrl($postUrl)) {
        $postResponse = new stdClass();
        $postResponse->id = $post->ID;
        $postResponse->url = $postUrl;
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
