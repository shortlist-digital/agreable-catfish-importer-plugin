<?php
use AgreableCatfishImporterPlugin\Services\Sitemap;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\FeatureEvent;
use \PHPUnit_Framework_Assert as Assert;

class SitemapContext extends BehatContext {
  private static $categorys;
  private static $categoryPosts;
  private static $post;

  /**
   * @Given /^the sitemap index "([^"]*)"$/
   */
  public function theSitemapIndex($sitemapIndex) {
    self::$categorys = Sitemap::getUrlsFromSitemap($sitemapIndex);
  }

  /**
   * @Then /^I should have a list of categories$/
   */
  public function iShouldHaveAListOfCategories() {
    Assert::assertGreaterThan(0, count(self::$categorys));
  }

  /**
   * @Given /^the category sitemap "([^"]*)"$/
   */
  public function theCategorySitemap($categorySitemap) {
    self::$categoryPosts = Sitemap::getUrlsFromSitemap($categorySitemap);
  }

  /**
   * @Then /^I should have a list of posts$/
   */
  public function iShouldHaveAListOfPosts() {
    Assert::assertGreaterThan(0, count(self::$categoryPosts));
  }
}
