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

class SyncContext extends BehatContext {

  // Protected variables for the tests to use
  protected static $queueID;
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
   * @Then /^I should have a valid queue ID$/
   */
  public function iShouldHaveAValidQueueId()
  {
      throw new PendingException();
      Assert::assertStringMatchesFormat('([a-z,0-9,-])+', self::$queueID);
  }

  /**
   * @Given /^I pull an item from the queue$/
   */
  public function iPullAnItemFromTheQueue()
  {
      throw new PendingException();
  }

  /**
   * @Then /^I should run the queue function without Exception$/
   */
  public function iShouldRunTheQueueFunctionWithoutException()
  {
      throw new PendingException();
  }

  /**
   * @Given /^I push the post "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushThePostWithTheUpdateMethodAsToTheQueue($arg1, $arg2)
  {
      self::$queueID = false;
      self::$queueID = Sync::queueUrl($arg1, $arg2);
  }

  /**
   * @Given /^I push all posts from the category sitemap "([^"]*)" with the update method as "([^"]*)" to the queue$/
   */
  public function iPushAllPostsFromTheCategorySitemapWithTheUpdateMethodAsToTheQueue($arg1, $arg2)
  {
      throw new PendingException();
  }

  /**
   * @Given /^I push all posts from the category sitemap "([^"]*)" to the queue$/
   */
  public function iPushAllPostsFromTheCategorySitemapToTheQueue($arg1)
  {
      throw new PendingException();
  }

  /**
   * @Given /^I have the queue action json$/
   */
  public function iHaveTheQueueActionJson(PyStringNode $string)
  {
      throw new PendingException();
  }

  /**
   * @Then /^I should have imported the "([^"]*)" post$/
   */
  public function iShouldHaveImportedThePost($arg1)
  {
      throw new PendingException();
  }

  /**
   * @Then /^I should have a list of urls$/
   */
  public function iShouldHaveAListOfUrls()
  {
      throw new PendingException();
  }

  /**
   * @Given /^I retrive the full import status$/
   */
  public function iRetriveTheFullImportStatus() {
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
   * @Given /^I retrive the "([^"]*)" category import status$/
   */
  public function iRetriveTheCategoryImportStatus($arg1)
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
