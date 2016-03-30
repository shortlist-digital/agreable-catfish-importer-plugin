<?php
namespace AgreableCatfishImporterPlugin\Controllers;

use AgreableCatfishImporterPlugin\Services\Notification;
use AgreableCatfishImporterPlugin\Services\SitemapParser;

class CronController {

  function __construct() {
    $this->notify = new Notification;
    $this->sitemap = new SitemapParser;
  }

  public function tick() {
    echo "<pre>";
    print_r($this->sitemap->get_all_posts());
  }

  public function test_cron() {
    $this->notify->post_import_complete();
  }
}
