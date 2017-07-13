<?php

namespace AgreableCatfishImporterPlugin;

use AgreableCatfishImporterPlugin\Services\Fetch;
use AgreableCatfishImporterPlugin\Services\Post;
use Croissant\DI\Interfaces\CatfishLogger;

/**
 * Class Api
 *
 * @package AgreableCatfishImporterPlugin
 */
class Api {
	/**
	 * @var CatfishLogger
	 */
	private $_logger;

	/**
	 * Api constructor.
	 *
	 * @param CatfishLogger $logger
	 */
	public function __construct( CatfishLogger $logger ) {
		$this->_logger = $logger;
	}

	/**
	 * @return array array of urls
	 */
	public function getSitemaps() {
		$sitemap = Fetch::xml( getenv( 'CATFISH_IMPORTER_TARGET_URL' ) . 'sitemap-index.xml' );

		return array_map( function ( $loc ) {
			return $loc->innertext;
		}, $sitemap->find( 'loc' ) );

	}

	/**
	 * @param $url
	 *
	 * @return array associative array $url=>$timestamp
	 */
	public function getPostsFromSitemap( $url ) {
		$timezone = date_default_timezone_get();
		date_default_timezone_set( 'Europe/Dublin' );


		$sitemap = Fetch::xml( $url );

		$urls = array_combine( array_map( function ( $loc ) {
			return $loc->innertext;
		}, $sitemap->find( 'loc' ) ), array_map( function ( $mod ) {
			return strtotime( $mod->innertext );
		}, $sitemap->find( 'lastmod' ) ) );

		date_default_timezone_set( $timezone );

		return $urls;

	}

	/**
	 * @param $postUrl
	 *
	 * @return \TimberPost
	 */
	public function importPost( $postUrl ) {

		return Post::getPostFromUrl( $postUrl, 'update' );
	}


	/**
	 * @return array
	 */
	public function getAllPosts() {
		return array_keys( $this->getAllPostsData() );
	}

	/**
	 * @return array
	 */
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