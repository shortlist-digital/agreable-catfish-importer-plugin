<?php
use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use \PHPUnit_Framework_Assert as Assert;

class SyncContext extends BehatContext {

  protected static $categorySitemap;

  /**
   * @Given /^I sync (\d+) most recent posts from the category sitemap "([^"]*)"$/
   */
  public function iSyncMostRecentPostsFromTheCategory($numberOfPosts, $categorySitemap) {
    self::$categorySitemap = $categorySitemap;
    $importResponse = Sync::importCategory($categorySitemap, $numberOfPosts);
    Assert::assertNotNull($importResponse);
    Assert::assertEquals($numberOfPosts, count($importResponse->posts));
    Assert::assertTrue(isset($importResponse->posts[0]->id));
  }

  /**
   * @Then /^I should have (\d+) imported "([^"]*)" posts$/
   */
  public function iShouldHaveImportedPosts($expectedNumberOfPosts, $categorySlug) {
    $query = array(
      'post_type' => 'post',
      'category_name' => $categorySlug,
      'meta_query' => array(
        array(
          'key' => 'automated_testing',
          'value' => true
        )
      )
    );

    $query = new WP_Query($query);
    $posts = $query->get_posts();
    Assert::assertEquals($expectedNumberOfPosts, count($posts));

    $post = new TimberPost($posts[0]);

    Assert::assertTrue($post->has_term($categorySlug, 'category'));
  }

  /**
   * @Given /^I should have the category import status of (\d+) out of (\d+) imported$/
   */
  public function iShouldHaveTheCategoryImportStatusOfOutOfImported($expectedNumberImported, $expectedNumberTotal) {
    $categoryImportStatus = Sync::getImportCategoryStatus(self::$categorySitemap);
    Assert::assertEquals($expectedNumberImported, $categoryImportStatus->importedCount);
    Assert::assertEquals($expectedNumberTotal, $categoryImportStatus->categoryTotal);
  }
}
