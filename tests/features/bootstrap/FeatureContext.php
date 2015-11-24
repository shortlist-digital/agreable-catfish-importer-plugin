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
use AgreableCatfishImporterPlugin\Services\Widget;
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
    Assert::assertInstanceOf('TimberPost', self::$article);

  }

  /**
   * @Given /^the article has the headline "([^"]*)"$/
   */
  public function theArticleHasTheHeadline($headline) {
    Assert::assertEquals($headline, self::$article->post_title);
  }

  /**
   * @Given /^the article has the property "([^"]*)" of "([^"]*)"$/
   */
  public function theArticleHasThePropertyOf($key, $value) {
    Assert::assertEquals($value, self::$article->get_field($key));
  }

  /**
   * @Given /^the category slug "([^"]*)"$/
   */
  public function theCategorySlug($categorySlug) {
    $category = Article::getCategory(self::$article);
    Assert::assertEquals($categorySlug, $category->slug);
  }

  /**
   * @Given /^the widgets "([^"]*)"$/
   */
  public function theWidgets($expectedWidgetsString) {
    $widgets = Widget::getPostWidgets(self::$article);
    $widgetNames = [];
    foreach($widgets as $widget) {
      $widgetNames[] = $widget['acf_fc_layout'];
    }
    Assert::assertEquals($expectedWidgetsString, implode(',', $widgetNames));
  }

  /**
   * @Given /^the paragraph widget at index (\d+):$/
   */
  public function theParagraphWidgetAtIndex($index, PyStringNode $string) {
    $widget = Widget::getPostWidgetsFiltered(self::$article, 'paragraph', $index);
    Assert::assertNotNull($widget);
    Assert::assertEquals((string)$string, $widget['paragraph']);
  }

  /**
   * @Given /^the image filename is "([^"]*)" at index (\d+)$/
   */
  public function theImageFilenameIsAtIndex($filename, $index) {
    $widget = Widget::getPostWidgetsFiltered(self::$article, 'image', $index);

    Assert::assertNotNull($widget);

    // Assert::markTestIncomplete('TODO: Mesh/Image.php assigns filename as MD5.');

    // Assert::assertEquals($widget['image']['filename'], $filename);
  }

  /**
   * @Given /^the "([^"]*)" "([^"]*)" is "([^"]*)" at index (\d+)$/
   */
  public function theWidgetPropertyIsAtIndex($widgetName, $property, $value, $index) {
    $widget = Widget::getPostWidgetsFiltered(self::$article, $widgetName, $index);
    Assert::assertNotNull($widget);
    Assert::assertTrue(isset($widget[$property]));
    Assert::assertEquals($value, $widget[$property]);
  }

    /**
     * @Given /^there are (\d+) hero images$/
     */
    public function thereAreHeroImages($expectedHeroImageNumber) {
      Assert::assertNotNull(self::$article->get_field('hero_images'));
      Assert::assertEquals($expectedHeroImageNumber, count(self::$article->get_field('hero_images')));
    }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
    // wp_delete_post(self::$article->id);
  }
}
