<?php
include __DIR__ . '/bootstrap.php';

use Behat\Behat\Context\BehatContext;
use Behat\Behat\Event\FeatureEvent;

class FeatureContext extends BehatContext {

  /**
   * @BeforeFeature
   */
  public static function prepare(FeatureEvent $scope) {
    self::deleteAllTestArticles();
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
