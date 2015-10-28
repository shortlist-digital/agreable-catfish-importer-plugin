<?php
include __DIR__ . '/bootstrap.php';

// Load Mesh (non-autoloadable)
if(file_exists(__DIR__ . '/../../vendor/jarednova/mesh/')){
  require_once __DIR__ . '/../../vendor/jarednova/mesh/mesh.php';
} else {
  require_once __DIR__ . '/../../../../../../../vendor/jarednova/mesh/mesh.php';
}

use AgreableCatfishImporterPlugin\Services\Sitemap;
use AgreableCatfishImporterPlugin\Services\Article;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;
use \PHPUnit_Framework_Assert as Assert;

class FeatureContext extends BehatContext {
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
   * @Given /^the article "([^"]*)"$/
   */
  public function theArticle($url) {
    self::$article = Article::getArticleFromUrl($url);
  }

  /**
   * @Then /^I should have an object of the article$/
   */
  public function iShouldHaveAnObjectOfTheArticle() {
    Assert::assertInstanceOf('WP_Post', self::$article);

  }

  /**
   * @Given /^the article has the headline "([^"]*)"$/
   */
  public function theArticleHasTheHeadline($headline) {
    Assert::assertEquals($headline, self::$article->post_title);
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
  }
}
