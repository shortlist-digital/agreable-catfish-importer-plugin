<?php

namespace AgreableCatfishImporterPlugin\Services;

use AgreableCatfishImporterPlugin\Services\Context\Output;
use Sunra\PhpSimple\HtmlDomParser;

class SiteMap {

	/**
	 * Get urls from sitemap url
	 *
	 * @var $sitemapLocation
	 * $since  timestamp
	 */
	/**
	 * @param [] $siteMapLocation Url to sitemap.xml
	 * @param bool|string $since Filter returned posts since dates
	 *
	 * @return array|null
	 */
	public static function getUrlsFromSitemap( $siteMapLocation, $since = false ) {
		// Catch if sub sitemap doesn't exist - Clock strangeness

		$sitemap = HtmlDomParser::file_get_html( $siteMapLocation );

		$urls = [];
		// Only process if object is returned
		if ( is_object( $sitemap ) ) {

			// Top level and secondary level sitemaps are structured differently
			// Deal with secondary level sitemaps which contain a lastmod tag
			if ( $sitemap->find( 'lastmod' ) ) {

				foreach ( $sitemap->find( 'url' ) as $url ) {

					// Get lastmod time from lasmod tag.
					/**
					 * this is stupid array. It's used to keep errors away when not passing by reference
					 */
					$swap      = explode( '<lastmod>', $url->innertext );
					$lastmod   = array_pop( $swap );
					$swap      = explode( '</lastmod>', $lastmod );
					$lastmod   = array_shift( $swap );
					$lastmod   = strtotime( $lastmod );
					$swap      = explode( '<loc>', $url->innertext );
					$innertext = array_pop( $swap );
					$swap      = explode( '</loc>', $innertext );
					$innertext = array_shift( $swap );

					// If date filter is passed then only show more recent posts
					if ( $since ) {
						// Only add posts after since date to string
						if ( $since < $lastmod ) {

							$urls[] = $innertext;
							Output::cliStatic( "Scanning the sitemap: " . $innertext . " because " . $since . " < " . $lastmod );

						}

					} else {

						$urls[] = $innertext;
						Output::cliStatic( "Scanning the sitemap: " . $innertext );

					}
				}

				// Deal with top level sitemaps which don't contain url or lastmod tags
			} else {
				/**
				 * @var $loc
				 */
				foreach ( $sitemap->find( 'loc' ) as $loc ) {
					$urls[] = $loc->innertext;
				}
			}

			return $urls;
		}

		return [];
	}
}