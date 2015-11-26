<?php
include __DIR__ . '/bootstrap.php';

use Behat\Behat\Context\BehatContext;

class FeatureContext extends BehatContext {
  public function __construct(array $parameters) {
    $this->useContext('subcontext_sitemap', new SitemapContext());
    $this->useContext('subcontext_article', new ArticleContext());
    $this->useContext('subcontext_sync', new SyncContext());
  }
}