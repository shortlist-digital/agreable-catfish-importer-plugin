<?php
include __DIR__ . '/bootstrap.php';

use AgreableCatfishImporterPlugin\Services\Post;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\FeatureEvent;

class FeatureContext extends BehatContext {

  /**
   * @BeforeFeature
   */
  public static function prepare(FeatureEvent $scope) {
    Post::deleteAllAutomatedTestingPosts();
  }

  public function __construct(array $parameters) {
    $this->useContext('subcontext_sitemap', new SitemapContext());
    $this->useContext('subcontext_post', new PostContext());
    $this->useContext('subcontext_sync', new SyncContext());
  }

  /**
   * @AfterFeature
   */
  public static function after(FeatureEvent $scope) {
    Post::deleteAllAutomatedTestingPosts();
  }

}
