<?php

namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\SiteMap;
use Croissant\DI\Interfaces\CatfishLogger;
use Croissant\DI\Interfaces\Queue;

class Api {
	/**
	 * @var Queue
	 */
	private $_queue;
	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	public function __construct( Queue $queue, CatfishLogger $logger ) {
		$this->_queue  = $queue;
		$this->_logger = $logger;
	}

	public function getSitemaps() {
		return SiteMap::getUrlsFromSitemap( getenv( 'CATFISH_IMPORTER_TARGET_URL' ) . 'sitemap-index.xml' );
	}

	public function getPostsFromSitemap( $sitemapUrl ) {

		return SiteMap::getUrlsFromSitemap( $sitemapUrl );

	}

	public function getPost( $postUrl, $onExist = 'update' ) {
		return Post::getPostFromUrl( $postUrl, $onExist );
	}

	public function getAll() {
		global $wpdb;
		$links     = $this->getSitemaps();
		$mapCount  = 0;
		$postCount = 0;

		wp_defer_term_counting( true );

		$this->_logger->info( "Success while downloading sitemaps" );
		foreach ( $links as $link ) {

			$posts = [];
			try {
				$mapCount ++;
				$posts = $this->getPostsFromSitemap( $link );
				$this->_logger->info( "Success $link ($mapCount)" );
			} catch ( \Error $e ) {
				$this->_logger->error( "Error while processing sitemap $link ($mapCount)", [ (string) $e ] );
			}
			foreach ( $posts as $post ) {
				$postCount ++;

				$this->_logger->info( "Processing " . $postCount );
				//sleep( 3 );
				try {
					$this->getPost( $post );
				} catch ( \Error $e ) {
					$this->_logger->error( "Error while processing post $post ($postCount)", [ (string) $e ] );
				}
			}
		}

		wp_defer_term_counting( false );

	}

}