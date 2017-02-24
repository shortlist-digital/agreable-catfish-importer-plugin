<?php
namespace AgreableCatfishImporterPlugin\Services;
use Sunra\PhpSimple\HtmlDomParser;

class Sitemap {
  public static function getCategoriesFromIndex($sitemapIndex) {
    return self::getUrlsFromSitemap($sitemapIndex);
  }

  public static function getPostUrlsFromCategory($categorySitemap) {
    return self::getUrlsFromSitemap($categorySitemap);
  }

  protected static function getUrlsFromSitemap($sitemapLocation) {
    // Catch if sub sitemap doesn't exist - Clock strangeness
    $siteMapHeaders = get_headers($sitemapLocation, 1);
    if ( strstr($siteMapHeaders[0], '200') == false ) {
      return [];
    }

    $sitemap = HtmlDomParser::file_get_html($sitemapLocation);
    $urls = [];
    // Only process if object is returned
    if (is_object($sitemap)) {
      foreach($sitemap->find('loc') as $loc) {
        $urls[] = $loc->innertext;
      }
      return $urls;
    }
    return null;
  }
}
