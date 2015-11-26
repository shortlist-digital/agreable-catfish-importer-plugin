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
  private static $categorys;
  private static $categoryPosts;
  private static $post;

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
  }

  /**
   * @Given /^I sync (\d+) most recent posts from the category "([^"]*)"$/
   */
  public function iSyncMostRecentPostsFromTheCategory($numberOfPosts, $categorySlug) {
    throw new PendingException();
  }

  /**
   * @Then /^I should have (\d+) imported "([^"]*)" posts$/
   */
  public function iShouldHaveImportedPosts($arg1, $arg2) {
    throw new PendingException();
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
  }
}
