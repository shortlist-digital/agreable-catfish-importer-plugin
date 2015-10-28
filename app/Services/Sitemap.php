<?php
namespace AgreableCatfishImporterPlugin\Services;
use Sunra\PhpSimple\HtmlDomParser;

class Sitemap {
  public static function getSectionsFromIndex($sitemapIndex) {
    return self::getUrlsFromSitemap($sitemapIndex);
  }

  public static function getArticlesFromSection($sectionSitemap) {
    return self::getUrlsFromSitemap($sectionSitemap);
  }

  protected static function getUrlsFromSitemap($sitemapLocation) {
    $sitemap = HtmlDomParser::file_get_html($sitemapLocation);
    $urls = [];
    foreach($sitemap->find('loc') as $loc) {
      $urls[] = $loc->innertext;
    }
    return $urls;
  }
}
