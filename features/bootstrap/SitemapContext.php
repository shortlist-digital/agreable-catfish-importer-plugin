<?php

require_once( __DIR__ . '/../../../../../wp/wp-load.php' );

use AgreableCatfishImporterPlugin\Api;
use Behat\Behat\Context\Context;
use PHPUnit_Framework_Assert as Assert;

/**
 * Class SitemapContext
 */
class SitemapContext implements Context {
	public $categories;
	public $categoryPosts;
	/**
	 * @var Api
	 */
	public $api;

	/**
	 * SitemapContext constructor.
	 */
	public function __construct() {
		$this->api = \Croissant\App::get( Api::class );
	}

	/**
	 * @Given /^the sitemap index "([^"]*)"$/
	 * @param $sitemapIndex
	 */
	public function theSitemapIndex( $sitemapIndex ) {
		$categories = $this->api->getSitemaps();

		$this->categories = $categories;
	}

	/**
	 * @Then /^I should have a list of categories$/
	 */
	public function iShouldHaveAListOfCategories() {
		Assert::assertGreaterThan( 0, count( $this->categories ) );
	}

	/**
	 * @Given /^the category sitemap "([^"]*)"$/
	 * @param $categorySitemap
	 */
	public function theCategorySitemap( $categorySitemap ) {
		$this->categoryPosts = $this->api->getPostsFromSitemap( $categorySitemap );
	}

	/**
	 * @Then /^I should have a list of posts with their timestamps$/
	 */
	public function iShouldHaveAListOfPostsWithTheirDates() {

		Assert::assertGreaterThan( 0, count( $this->categoryPosts ) );
		/**
		 * Checks if all the keys are strings and all the timestamps are numeric
		 */
		Assert::assertEquals( count( array_filter( array_keys( $this->categoryPosts ), function ( $i ) {
			return is_string( $i );
		} ) ), count( array_filter( array_values( $this->categoryPosts ), function ( $i ) {
			return is_numeric( $i );
		} ) ) );

	}
}
