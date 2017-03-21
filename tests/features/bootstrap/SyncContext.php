<?php
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;

use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Queue;
use AgreableCatfishImporterPlugin\Services\Worker;

use Illuminate\Queue\Jobs\SqsJob as Job;
// use WP_CLI;

use Behat\Behat\Context\BehatContext,
Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
  Behat\Gherkin\Node\TableNode;
use \PHPUnit_Framework_Assert as Assert;

// Ignore Notices
// Runtime Notice: Declaration of AgreableCatfishImporterPlugin\Services\Worker::process() should be compatible with Illuminate\Queue\Worker::process($connection, Illuminate\Contracts\Queue\Job $job, $maxTries = 0, $delay = 0) in app/Services/Worker.php line 10
define('BEHAT_ERROR_REPORTING', E_ERROR | E_WARNING | E_PARSE);

class SyncContext extends BehatContext {

  // Protected variables for the tests to use
  protected static $queueID;
  protected static $queueItem;
  protected static $queueActionResponse;
  protected static $categorySitemap;
  protected static $statusData;

  /**
   * @Given /^I purge the queue$/
   */
  public function iPurgeTheQueue() {
    Sync::purgeQueue();
  }

  /**
   * @Given /^I delete all automated_testing posts$/
   */
  public function iDeleteAllAutomatedTestingPosts() {
    Post::deleteAllAutomatedTestingPosts();
  }

  /**
   * @Then /^I should have no automated_testing posts$/
   */
  public function iShouldHaveNoAutomatedTestingPosts() {
    $query = array(
      'post_type' => 'post',
      'meta_key' => 'automated_testing',
      'meta_value'  => true
    );

    $query = new WP_Query($query);
    $posts = $query->get_posts();

    Assert::assertEquals(0, count($posts));
  }

  /**
   * @Given /^I push the post "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushThePostWithTheUpdateMethodAsToTheQueue($url, $onExistAction) {
      self::$queueID = false;
      self::$queueID = Sync::queueUrl($url, $onExistAction);
  }

  /**
   * @Then /^I should have a valid queue ID$/
   */
  public function iShouldHaveAValidQueueId() {
    // print_r(var_dump(self::$queueID));
    Assert::assertRegExp('/[a-z0-9-]+/', self::$queueID);
  }

  /**
   * @Given /^I pull an item from the queue and run it$/
   */
  public function iPullAnItemFromTheQueueAndRunIt() {
    self::$queueActionResponse = Sync::actionSingleQueueItem();
  }

  /**
   * @Then /^I should have imported the "([^"]*)" post$/
   */
  public function iShouldHaveImportedThePost($slug) {
    $posts = Post::getPostsWithSlug($slug);

    Assert::assertGreaterThan(0, count($posts));
  }

  /**
   * @Given /^I push all posts from the category sitemap "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushAllPostsFromTheCategorySitemapWithTheUpdateMethodAsToTheQueue($url, $onExistAction) {
    self::$queueID = false;
    self::$queueID = Sync::queueUrl($url, $onExistAction);
  }

  /**
   * @Given /^I process the queue action json \'([^\']*)\'$/
   */
  public function iProcessTheQueueActionJson($payload) {
    // From Worker
    $data = json_decode($payload);
    $function = $data->job;
    $payload = (array) $data->data;

    // Call the queued function in the Sync Class
    self::$queueActionResponse = false;
    self::$queueActionResponse = Sync::$function($data, $payload);
    // self::$queueActionResponse = Sync::$function($data, $payload);
  }

  /**
   * @Then /^I should have a list of urls$/
   */
  public function iShouldHaveAListOfUrls() {
    Assert::assertTrue(is_array(self::$queueActionResponse));
  }

  /**
   * @Then /^I should have an array of queue IDs$/
   */
  public function iShouldHaveAnArrayOfQueueIds() {
    // print_r(var_dump(self::$queueActionResponse));
    Assert::assertTrue(is_array(self::$queueActionResponse));
    // Check all ids are valid
    foreach (self::$queueActionResponse as $item) {
      Assert::assertRegExp('/[a-z0-9-]+/', $item);
    }
  }

  /**
   * @Given /^I should have \'([^\']*)\' the post in the last (\d+) seconds$/
   */
  // Function that checks for the catfish_importer_date_created or catfish_importer_date_updated
  // values to check importer function.

  // Parameters:
  // $timeType  created/updated
  // $seconds  maximum time window for the post to have been updated or created

  public function iShouldHaveThePostInTheLastSeconds($timeType, $seconds) {
    // When updated an existing post you should
    if($timeType !== 'created' && $timeType !== 'updated') {
      throw new Exception("You must specify either created or updated time to check.", 30);
    }

    $postTime = self::$queueActionResponse->custom["catfish_importer_date_$timeType"];
    // Seconds since modified
    $timeDifference = time() - $postTime;

    // Assert modified in the last 10 seconds
    Assert::assertLessThan($seconds, $timeDifference);
  }

  /**
   * @Given /^I should have not updated the post created or updated time$/
   */
  public function iShouldHaveNotUpdatedThePostCreatedOrUpdatedTime() {
    // When skipping posts that exists you should have and insert and update
    // time over a day old.

    // TODO: use above to build assertations for these tests.
    $postCreatedTime = strtotime(self::$queueActionResponse->post_created);
    // Seconds since created
    $secondsSinceCreation = time() - $postCreatedTime;

    $postModifiedTime = strtotime(self::$queueActionResponse->post_created);
    // Seconds since modified
    $secondsSinceCreation = time() - $postModifiedTime;

    // Assert created or modified over 1 day ago
    Assert::assertGreaterThan(14 * 60 * 60, $secondsSinceCreation);
    Assert::assertGreaterThan(14 * 60 * 60, $secondsSinceCreation);
  }

  /**
   * @Given /^I retrieve the full import status$/
   */
  public function iRetrieveTheFullImportStatus() {
    self::$statusData = Sync::getImportStatus();
  }

  /**
   * @Then /^I should see (\d+) imported out of at least (\d+)$/
   */
  public function iShouldSeeImportedOutOfAtLeast($importedTarget, $targetTotal) {
    Assert::assertGreaterThan($importedTarget, self::$statusData->importedCount);
    if(isset(self::$statusData->categoryTotal)) {
      Assert::assertGreaterThan($targetTotal, self::$statusData->categoryTotal);
    } else {
      Assert::assertGreaterThan($targetTotal, self::$statusData->total);
    }
  }

  /**
   * @Given /^I retrieve the "([^"]*)" category import status$/
   */
  public function iRetrieveTheCategoryImportStatus($categorySitemap) {
    self::$statusData = Sync::getImportCategoryStatus($categorySitemap);
  }

}
