<?php
include __DIR__ . '/bootstrap.php';

use AgreableCatfishImporterPlugin\Services\Sitemap;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\FeatureEvent;
use \PHPUnit_Framework_Assert as Assert;

class PostContext extends BehatContext {
  private static $post;

  /**
   * NOTE: The FeatureContext clears all posts with the automated_testing data
   * from the database on BeforeFeature and AfterFeature events.
   */

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
  public function theWidgetPropertyIsAtIndex($widgetName, $property, $value, $index, $stringSearch = false) {
    $widget = Widget::getPostWidgetsFiltered(self::$post, $widgetName, $index);
    Assert::assertNotNull($widget);
    Assert::assertTrue(isset($widget[$property]));

    if ($stringSearch) {
      Assert::assertContains($value, $widget[$property]);
    } else {
      Assert::assertEquals($value, $widget[$property]);
    }
  }

  /**
   * @Given /^the "([^"]*)" "([^"]*)" contains "([^"]*)" at index (\d+)$/
   */
  public function theWidgetPropertyStringSearchIsAtIndex($widgetName, $property, $value, $index) {
    $this->theWidgetPropertyIsAtIndex($widgetName, $property, $value, $index, true);
  }

  /**
   * @Given /^the "([^"]*)" "([^"]*)" at index (\d+) is:$/
   */
  public function theWidgetPropertyMultilineIsAtIndex($widgetName, $property, $index, PyStringNode $value) {
    $this->theWidgetPropertyIsAtIndex($widgetName, $property, $value, $index);
  }

  /**
   * @Given /^the number of hero images is (\d+)$/
   */
  public function theNumberOfHeroImagesIs($expectedHeroImageNumber) {
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
   * @Given /^gallery item (\d+) has title "([^"]*)"$/
   */
  public function galleryItemHasTitle($gallery_index, $title) {
    $widget = $this->getGalleryWidgetFromPost(self::$post);

    Assert::assertNotNull($widget);

    $gallery_item = $widget['gallery_items'][$gallery_index -1];
    Assert::assertEquals($title, $gallery_item['title']);
  }

  /**
   * @Given /^gallery item (\d+) has caption:$/
   */
  public function galleryItemHasCaption($gallery_index, PyStringNode $caption) {
    $widget = $this->getGalleryWidgetFromPost(self::$post);

    Assert::assertNotNull($widget);

    $gallery_item = $widget['gallery_items'][$gallery_index -1];
    Assert::assertEquals((string)$caption, $gallery_item['caption']);
  }

  protected function getGalleryWidgetFromPost($post) {
    $widgets = self::$post->get_field('widgets');
    foreach($widgets as $widget) {
      if ($widget['acf_fc_layout'] === 'gallery') {
        return $widget;
      }
    }
  }

  /**
   * @Given /^the post has (\d+) "([^"]*)" widgets$/
   */
  public function thePostHasWidgets($count, $widget_type) {
    $widgets = self::$post->get_field('widgets');
    $image_count = 0;
    foreach ($widgets as $key => $widget) {
      if ($widget['acf_fc_layout'] == $widget_type) {
        $image_count = $image_count + 1;
      }
    }
    Assert::assertEquals($count, $image_count);
  }

}
