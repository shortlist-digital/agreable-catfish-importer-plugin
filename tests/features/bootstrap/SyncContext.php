<?php
use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;

use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Queue;
use AgreableCatfishImporterPlugin\Services\Worker;

use Illuminate\Queue\Jobs\SqsJob as Job;

// new Queue; // Setup Queue connection

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
  protected static $totalStatus;

  /**
   * @Given /^I purge the queue$/
   */
  public function iPurgeTheQueue()
  {
      Sync::purgeQueue();
  }

  /**
   * @Given /^I push the post "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushThePostWithTheUpdateMethodAsToTheQueue($url, $onExistAction)
  {
      self::$queueID = false;
      self::$queueID = Sync::queueUrl($url, $onExistAction);
  }

  /**
   * @Then /^I should have a valid queue ID$/
   */
  public function iShouldHaveAValidQueueId()
  {
    Assert::assertRegExp('/[a-z0-9-]+/', self::$queueID);
  }

  /**
   * @Given /^I pull an item from the queue and run it$/
   */
  public function iPullAnItemFromTheQueueAndRunIt()
  {
      self::$queueActionResponse = Sync::actionSingleQueueItem();
  }


  // TODO: Need a clean environment to truly run
  /**
   * @Then /^I should have imported the "([^"]*)" post$/
   */
  public function iShouldHaveImportedThePost($slug)
  {
    $query = array(
      'post_type' => 'post',
      'pagename' => $slug.'-2', // TODO: clear all automated_testing posts before testing
      'meta_query' => array(
        array(
          'key' => 'automated_testing',
          'value' => true
        )
      )
    );

    $query = new WP_Query($query);
    $posts = $query->get_posts();
    var_export($posts);
    Assert::assertEquals(1, count($posts));

    // $post = new TimberPost($posts[0]);
    // Assert::assertTrue($post->has_term($categorySlug, 'category'));
  }

  /**
   * @Given /^I push all posts from the category sitemap "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushAllPostsFromTheCategorySitemapWithTheUpdateMethodAsToTheQueue($url, $onExistAction)
  {
    self::$queueID = false;
    self::$queueID = Sync::queueUrl($url, $onExistAction);
  }

  // XXX Up to here......

  /**
   * @Given /^I process the queue action json \'([^\']*)\'$/
   */
  public function iProcessTheQueueActionJson($payload)
  {
    // From Worker
    $data = json_decode($payload);
    $function = $data->job;
    $payload = (array) $data->data;

    // Call the queued function in the Sync Class
    self::$queueActionResponse = Sync::$function($data, $payload);
  }


  /**
   * @Then /^I should have a list of urls$/
   */
  public function iShouldHaveAListOfUrls()
  {
    Assert::assertTrue(is_array(self::$queueActionResponse));
  }

  /**
   * @Given /^I retrieve the full import status$/
   */
  public function iRetrieveTheFullImportStatus() {
    self::$totalStatus = Sync::getImportStatus();
  }

  /**
   * @Then /^I should see a couple of imported out of at least (\d+)$/
   */
  public function iShouldSeeACoupleOfImportedOutOfAtLeast($arg) {
    // Assert::assertGreaterThan(0, self::$totalStatus->importedCount);
    Assert::assertGreaterThan(10000, self::$totalStatus->total);
  }

  /**
   * @Given /^I retrieve the "([^"]*)" category import status$/
   */
  public function iRetrieveTheCategoryImportStatus($arg1)
  {
    throw new PendingException();
  }

  /**
   * @Then /^I should see a several of imported out of at least (\d+)$/
   */
  public function iShouldSeeASeveralOfImportedOutOfAtLeast($arg1)
  {
    throw new PendingException();
  }

}
