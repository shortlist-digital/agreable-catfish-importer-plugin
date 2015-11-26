<?php
use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Article;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;
use \PHPUnit_Framework_Assert as Assert;

class SyncContext extends BehatContext {
  private static $sections;
  private static $sectionArticles;
  private static $article;

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
  }
}
