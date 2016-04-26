<?php
include __DIR__ . '/bootstrap.php';

use AgreableCatfishImporterPlugin\Services\Sitemap;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;
use \PHPUnit_Framework_Assert as Assert;

class PostContext extends BehatContext {
  private static $post;

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
  }

  /**
   * @Given /^the post "([^"]*)"$/
   */
  public function thePost($url) {
    self::$post = Post::getPostFromUrl($url);
  }

  /**
   * @Then /^I should have an object of the post$/
   */
  public function iShouldHaveAnObjectOfThePost() {
    Assert::assertInstanceOf('TimberPost', self::$post);

  }

  /**
   * @Given /^the post has the headline "([^"]*)"$/
   */
  public function thePostHasTheHeadline($headline) {
    Assert::assertEquals($headline, self::$post->post_title);
  }

  /**
   * @Given /^the post slug is "([^"]*)"$/
   */
  public function thePostSlugIs($slug) {
    Assert::assertEquals(self::$post->post_name, $slug);
  }

  /**
   * @Given /^the post has the property "([^"]*)" of "([^"]*)"$/
   */
  public function thePostHasThePropertyOf($key, $value) {
    Assert::assertEquals($value, self::$post->get_field($key));
  }

  /**
   * @Given /^the category slug "([^"]*)"$/
   */
  public function theCategorySlug($categorySlug) {
    $category = Post::getCategory(self::$post);
    Assert::assertEquals($categorySlug, $category->slug);
  }

  /**
   * @Given /^the widgets "([^"]*)"$/
   */
  public function theWidgets($expectedWidgetsString) {
    $widgets = Widget::getPostWidgets(self::$post);
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
    $widget = Widget::getPostWidgetsFiltered(self::$post, 'paragraph', $index);
    Assert::assertNotNull($widget);
    Assert::assertEquals((string)$string, $widget['paragraph']);
  }

  /**
   * @Given /^the image filename is "([^"]*)" at index (\d+)$/
   */
  public function theImageFilenameIsAtIndex($filename, $index) {
    $widget = Widget::getPostWidgetsFiltered(self::$post, 'image', $index);

    Assert::assertNotNull($widget);

    // Assert::markTestIncomplete('TODO: Mesh/Image.php assigns filename as MD5.');

    // Assert::assertEquals($widget['image']['filename'], $filename);
  }

  /**
   * @Given /^the "([^"]*)" "([^"]*)" is "([^"]*)" at index (\d+)$/
   */
  public function theWidgetPropertyIsAtIndex($widgetName, $property, $value, $index) {
    $widget = Widget::getPostWidgetsFiltered(self::$post, $widgetName, $index);
    Assert::assertNotNull($widget);
    Assert::assertTrue(isset($widget[$property]));
    Assert::assertEquals($value, $widget[$property]);
  }

  /**
   * @Given /^the number of hero widgets is (\d+)$/
   */
  public function theNumberOfHeroWidgetsIs($expectedHeroImageNumber) {
    Assert::assertNotNull(self::$post->get_field('hero_images'));
    Assert::assertEquals($expectedHeroImageNumber, count(self::$post->get_field('hero_images')));
  }

  /**
   * @Given /^there are (\d+) gallery images$/
   */
  public function thereAreGalleryImages($expectedNumberOfGalleryImages) {
    $widgets = self::$post->get_field('widgets');
    foreach($widgets as $widget) {
      if ($widget['acf_fc_layout'] === 'gallery') {
        Assert::assertEquals($expectedNumberOfGalleryImages, count($widget['gallery_items']));
      }
    }
  }

  /**
   * @Given /^the post has import metadata$/
   */
  public function thePostHasImportMetadata() {
    Assert::assertEquals(true, self::$post->get_field('catfish_importer_imported'));
    Assert::assertNotNull(self::$post->get_field('catfish_importer_date_updated'));
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
    // wp_delete_post(self::$post->id);
  }
}
