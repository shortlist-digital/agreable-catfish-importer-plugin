<?php
include __DIR__ . '/bootstrap.php';

// Load Mesh (non-autoloadable)
if(file_exists(__DIR__ . '/../../vendor/jarednova/mesh/')){
  require_once __DIR__ . '/../../vendor/jarednova/mesh/mesh.php';
} else {
  require_once __DIR__ . '/../../../../../../vendor/jarednova/mesh/mesh.php';
}

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Behat\Behat\Context\BehatContext,
  Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent;

class FeatureContext extends BehatContext {
  private static $webDriver;
  private static $testPostPromo;

  /**
   * @BeforeSuite
   */
  public static function prepare(SuiteEvent $scope) {
    // $promoTitle = 'Test (system) - promo';
    // $postTitle = 'Test (system) - promo post';

    // $promo = new \Mesh\Post($promoTitle, 'promo');
    // $promo->set('data_to_capture', 'a:1:{i:0;s:8:"fullName";}');
    // $promo->set('collect_optins', 1);
    // $promo->set('third_party_optins_0_optin_name', 'Croissant');
    // $promo->set('third_party_optins_0_optin_label', 'Would you like to hear from Croissant?');
    // $promo->set('third_party_optins', 1);
    // $promo->set('terms_and_conditions_label', 'I accept the terms and conditions');
    // $promo->set('terms_and_conditions', 'Test terms and conditions');
    // $promo->set('start_time', 1441065600);
    // $promo->set('end_time', 1916697600);
    // $promo->set('selected_passport', '{"id":"test_promotion_main-subscribe","shared_secret":"6a9962bdd2359e32a4090951829247b1db507fc3b66b25c34e02e257c3ef9e48"}');
    // $promo->set('promotion_passport', '{"id":"test_promotion_main-subscribe","shared_secret":"6a9962bdd2359e32a4090951829247b1db507fc3b66b25c34e02e257c3ef9e48"}');

    // self::$testPostPromo = new Mesh\Post($postTitle, 'post');
    // self::$testPostPromo->set('short_headline', 'Test (system) - promo');
    // self::$testPostPromo->set('sell', 'This is an automated test');
    // self::$testPostPromo->set('article_widgets_0_promo_post', $promo->id);
    // self::$testPostPromo->set('article_widgets', 'a:1:{i:0;s:12:"promo_plugin";}');
    // self::$testPostPromo->set('post_status', 'publish');
  }

  /**
   * @AfterSuite
   */
  public static function after(SuiteEvent $scope) {
  }
}
