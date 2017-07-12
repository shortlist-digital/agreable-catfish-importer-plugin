<?php

namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Fetch;
use AgreableCatfishImporterPlugin\Services\Post;
use Croissant\DI\Interfaces\CatfishLogger;

class Api {
	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	public function __construct( CatfishLogger $logger ) {
		$this->_logger = $logger;
	}

	public function getSitemaps() {
		return array_keys( $this->getPostsFromSitemap( getenv( 'CATFISH_IMPORTER_TARGET_URL' ) . 'sitemap-index.xml' ) );
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

	public function importPost( $postUrl ) {

		return Post::getPostFromUrl( $postUrl, 'update' );
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