<?php
use AgreableCatfishImporterPlugin\Services\SiteMap;
use Behat\Behat\Context\BehatContext;
use PHPUnit_Framework_Assert as Assert;

class SitemapContext extends BehatContext {
	private static $categorys;
	private static $categoryPosts;


	/**
	 * @Given /^the sitemap index "([^"]*)"$/
	 */
	public function theSitemapIndex( $sitemapIndex ) {
		self::$categorys = SiteMap::getUrlsFromSitemap( $sitemapIndex );
	}

	/**
	 * @Then /^I should have a list of categories$/
	 */
	public function iShouldHaveAListOfCategories() {
		Assert::assertGreaterThan( 0, count( self::$categorys ) );
	}

	/**
	 * @Given /^the category sitemap "([^"]*)"$/
	 */
	public function theCategorySitemap( $categorySitemap ) {
		self::$categoryPosts = SiteMap::getUrlsFromSitemap( $categorySitemap );
	}

	/**
	 * @Then /^I should have a list of posts$/
	 */
	public function iShouldHaveAListOfPosts() {
		Assert::assertGreaterThan( 0, count( self::$categoryPosts ) );
	}

	public static function clearVariables() {
		self::$categorys     = null;
		self::$categoryPosts = null;
	}
}
