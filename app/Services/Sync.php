<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use AgreableCatfishImporterPlugin\Services\Post;

class Sync {

  public static function getCategories() {
    $site_url = get_field('catfish_website_url', 'option');
    return Sitemap::getCategoriesFromIndex($site_url . 'sitemap-index.xml');
  }

  public static function importCategory($categorySitemap, $limit = 10, $mostRecent = true) {
    $postUrls = Sitemap::getPostsFromCategory($categorySitemap);
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
}
