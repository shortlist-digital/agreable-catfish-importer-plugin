<?php
namespace AgreableCatfishImporterPlugin\Services;
use Sunra\PhpSimple\HtmlDomParser;

class Sitemap {

  /**
   * Get post urls from category sitemap
   *
   * $sitemapLocation  array  Url to sitemap.xml
   * $since  timestamp   Filter returned posts since dates
   */
  public static function getPostUrlsFromCategory($categorySitemap, $since = false) {
    return self::getUrlsFromSitemap($categorySitemap, $since);
  }

  /**
   * Get and sort
   *
   * $sitemapLocation  array  Url to sitemap.xml
   * $since  timestamp   Filter returned posts since dates
   */
  protected static function getUrlsFromSitemap($sitemapLocation, $since = false) {
    // Catch if sub sitemap doesn't exist - Clock strangeness
    $siteMapHeaders = get_headers($sitemapLocation, 1);
    if ( strstr($siteMapHeaders[0], '200') == false ) {
      return [];
    }

    echo "Starting ". $sitemapLocation."\n";

    $sitemap = HtmlDomParser::file_get_html($sitemapLocation);
    $urls = [];
    // Only process if object is returned
    if (is_object($sitemap)) {

      // Top level and secondary level sitemaps are structured differently
      // Deal with secondary level sitemaps which contain a lastmod tag
      if($sitemap->find('lastmod')) {

        foreach($sitemap->find('url') as $url) {

          // TODO Deal with recent posts...

          // Get lastmod time from lasmod tag.
          $lastmod = array_pop(explode('<lastmod>', $url->innertext));
          $lastmod = array_shift(explode('</lastmod>', $lastmod));
          $lastmod = strtotime($lastmod);

          $innertext = array_pop(explode('<loc>', $url->innertext));
          $innertext = array_shift(explode('</loc>', $innertext));

          // If date filter is passed then only show more recent posts
          if($since) {
            // Only add posts after since date to string
            if($since < $lastmod) {
              $urls[] = $innertext;

              echo "Adding ". $innertext. " because ". $since ." < ". $lastmod . "\n";
            }
          } else {
            $urls[] = $url->find('loc')->innertext;

            echo "Adding ". $url->find('loc')->innertext. " because there's no timestamp here??\n";
          }
        }

      // Deal with top level sitemaps which don't contain url or lastmod tags
      } else {

        foreach($sitemap->find('loc') as $loc) {
          $urls[] = $loc->innertext;

          echo "Adding ". $loc->innertext. " because it's supposed to be a top level??\n";
        }
      }

      return $urls;
    }
    return null;
  }
}
