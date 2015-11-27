<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use AgreableCatfishImporterPlugin\Services\Post;

class Sync {
  public static function getCategories() {
    return Sitemap::getCategoriesFromIndex('http://www.stylist.co.uk/sitemap-index.xml');
  }

  public static function importCategory($categorySitemap, $limit = 10, $mostRecent = true) {
    $postUrls = Sitemap::getPostsFromCategory($categorySitemap);
    $response = new stdClass();
    $response->posts = [];
    if ($mostRecent) {
      $postUrls = array_reverse($postUrls);
    }
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
}
