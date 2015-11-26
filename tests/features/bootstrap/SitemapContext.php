<?php
use AgreableCatfishImporterPlugin\Services\Sitemap;
use AgreableCatfishImporterPlugin\Services\Article;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;
use \PHPUnit_Framework_Assert as Assert;

class SitemapContext extends BehatContext {
  private static $sections;
  private static $sectionArticles;
  private static $article;

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
  }

  /**
   * @Given /^the sitemap index "([^"]*)"$/
   */
  public function theSitemapIndex($sitemapIndex) {
    self::$sections = Sitemap::getSectionsFromIndex($sitemapIndex);
  }

  /**
   * @Then /^I should have a list of sections$/
   */
  public function iShouldHaveAListOfSections() {
    Assert::assertGreaterThan(0, count(self::$sections));
  }

  /**
   * @Given /^the section sitemap "([^"]*)"$/
   */
  public function theSectionSitemap($sectionSitemap) {
    self::$sectionArticles = Sitemap::getArticlesFromSection($sectionSitemap);
  }

  /**
   * @Then /^I should have a list of articles$/
   */
  public function iShouldHaveAListOfArticles() {
    Assert::assertGreaterThan(0, count(self::$sectionArticles));
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
  }
}
