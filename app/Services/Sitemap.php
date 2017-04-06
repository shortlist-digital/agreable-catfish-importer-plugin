<?php
namespace AgreableCatfishImporterPlugin\Services;

use \WP_CLI;
use Sunra\PhpSimple\HtmlDomParser;

class Sitemap {

  /**
   * Get urls from sitemap url
   *
   * $sitemapLocation  array  Url to sitemap.xml
   * $since  timestamp   Filter returned posts since dates
   */
  public static function getUrlsFromSitemap($sitemapLocation, $since = false, $cli = false) {
    // Catch if sub sitemap doesn't exist - Clock strangeness
    $siteMapHeaders = get_headers($sitemapLocation, 1);
    if ( strstr($siteMapHeaders[0], '200') == false ) {
      return [];
    }

    $sitemap = HtmlDomParser::file_get_html($sitemapLocation);
    $urls = [];
    // Only process if object is returned
    if (is_object($sitemap)) {

      // Top level and secondary level sitemaps are structured differently
      // Deal with secondary level sitemaps which contain a lastmod tag
      if($sitemap->find('lastmod')) {

        foreach($sitemap->find('url') as $url) {

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

              if($cli) {
                WP_CLI::line("Scanning the sitemap: ". $innertext. " because ". $since ." < ". $lastmod);
              }
            }

          } else {
            $urls[] = $innertext;

            if($cli) {
              WP_CLI::line("Scanning the sitemap: ". $innertext);
            }
          }
        }

      // Deal with top level sitemaps which don't contain url or lastmod tags
      } else {

        foreach($sitemap->find('loc') as $loc) {
          $urls[] = $loc->innertext;
        }
      }

      return $urls;
    }
    return null;
  }
}
