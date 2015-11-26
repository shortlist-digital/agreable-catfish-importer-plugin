<?php
namespace AgreableCatfishImporterPlugin\Services;

class Sync {
  public static function getCategories() {
    return Sitemap::getCategoriesFromIndex('http://www.stylist.co.uk/sitemap-index.xml');
  }

  public static function importCategory($categorySitemap, $limit = 10, $mostRecent = true) {
    $postUrls = Sitemap::getPostsFromCategory($categorySitemap);
    if ($mostRecent) {
      $postUrls = array_reverse($postUrls);
    }
    if ($limit !== -1) {
      $postUrls = array_slice($postUrls, 0, $limit);
    }

    foreach($postUrls as $postUrl) {
      $post = Post::getPostFromUrl($postUrl);
    }
  }
}
