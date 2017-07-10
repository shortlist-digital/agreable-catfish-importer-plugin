<?php

namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Fetch;
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
		define( 'MAX_FILE_SIZE', 6000000 );
		$this->_queue  = $queue;
		$this->_logger = $logger;
	}

	public function getSitemaps() {
		return SiteMap::getUrlsFromSitemap( getenv( 'CATFISH_IMPORTER_TARGET_URL' ) . 'sitemap-index.xml' );
	}

	public function getPostsFromSitemap( $url ) {
		$timezone = date_default_timezone_get();
		date_default_timezone_set( 'Europe/Dublin' );


		$sitemap = Fetch::xml( $url );
		$urls    = [];

		foreach ( $sitemap->find( 'url' ) as $url ) {


			/**
			 * this is stupid array. It's used to keep errors away when not passing by reference
			 */
			$swap               = explode( '<lastmod>', $url->innertext );
			$lastmod            = array_pop( $swap );
			$swap               = explode( '</lastmod>', $lastmod );
			$lastmod            = array_shift( $swap );
			$lastmod            = strtotime( $lastmod );
			$swap               = explode( '<loc>', $url->innertext );
			$innertext          = array_pop( $swap );
			$swap               = explode( '</loc>', $innertext );
			$innertext          = array_shift( $swap );
			$urls[ $innertext ] = $lastmod;

		}

		date_default_timezone_set( $timezone );

		return $urls;

	}

	public function getPost( $postUrl, $onExist = 'update' ) {

		return Post::getPostFromUrl( $postUrl, $onExist );
	}

	/**
	 * Makes sure that exception will be \Exception and not error
	 */
	public function registerSilencer() {
		set_error_handler(
			function ( $errno, $errstr, $errfile, $errline ) {
				throw new \ErrorException( $errstr, $errno, 0, $errfile, $errline );
			}
		);
	}

	public function getAllPosts() {
		return array_keys( $this->getAllPostsData() );
	}

	public function getAllPostsData() {

		$this->_logger->info( "Fetching sitemaps" );
		$links = $this->getSitemaps();

		$all_posts = [];
		$this->_logger->info( "Success while downloading sitemaps" );
		foreach ( $links as $link ) {
			try {
				$posts     = $this->getPostsFromSitemap( $link );
				$all_posts = array_merge( $all_posts, $posts );
				$this->_logger->info( "Success $link" );
			} catch ( \Exception $e ) {

				$this->_logger->error( "Error while processing sitemap $link", [ (string) $e ] );
			}

		}

		return $all_posts;
	}

}