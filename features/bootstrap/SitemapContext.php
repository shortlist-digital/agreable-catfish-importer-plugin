<?php

require_once( __DIR__ . '/../../../../../wp/wp-load.php' );

use AgreableCatfishImporterPlugin\Api;
use Behat\Behat\Context\Context;
use PHPUnit_Framework_Assert as Assert;

class SitemapContext implements Context {
	public $categorys;
	public $categoryPosts;
	/**
	 * @var Api
	 */
	public $api;

	public function __construct() {
		$this->api = \Croissant\App::get( Api::class );
	}

	/**
	 * @Given /^the sitemap index "([^"]*)"$/
	 * @param $sitemapIndex
	 */
	public function theSitemapIndex( $sitemapIndex ) {
		$this->categorys = $this->api->getSitemaps();
	}

	/**
	 * @Then /^I should have a list of categories$/
	 */
	public function iShouldHaveAListOfCategories() {
		Assert::assertGreaterThan( 0, count( $this->categorys ) );
	}

	/**
	 * @Given /^the category sitemap "([^"]*)"$/
	 * @param $categorySitemap
	 */
	public function theCategorySitemap( $categorySitemap ) {
		$this->categoryPosts = $this->api->getPostsFromSitemap( $categorySitemap );
	}

	/**
	 * @Then /^I should have a list of posts$/
	 */
	public function iShouldHaveAListOfPosts() {
		Assert::assertGreaterThan( 0, count( $this->categoryPosts ) );
	}
}
