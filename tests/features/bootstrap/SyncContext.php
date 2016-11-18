<?php
use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;
use \PHPUnit_Framework_Assert as Assert;

class SyncContext extends BehatContext {

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
    self::deleteAllTestArticles();
  }

  /**
   * @Given /^I sync (\d+) most recent posts from the category sitemap "([^"]*)"$/
   */
  public function iSyncMostRecentPostsFromTheCategory($numberOfPosts, $categorySitemap) {
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
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
    self::deleteAllTestArticles();
  }

  protected static function deleteAllTestArticles() {
    $query = [
      'post_type' => 'post',
      'meta_key'  => 'automated_testing',
      'meta_value'  => true,
    ];

    $query = new WP_Query($query);
    $posts = $query->get_posts();
    foreach($posts as $post) {
      wp_delete_post($post->ID, true);
    }
  }
}
