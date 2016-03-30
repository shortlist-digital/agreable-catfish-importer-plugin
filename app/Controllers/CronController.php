<?php
namespace AgreableCatfishImporterPlugin\Controllers;

use AgreableCatfishImporterPlugin\Services\Notification;
use AgreableCatfishImporterPlugin\Services\SitemapParser;
use AgreableCatfishImporterPlugin\Services\Sync;
use TimberPost;

class CronController {

  function __construct() {
    $this->notify = new Notification;
    $this->sitemap = new SitemapParser;
  }

  public function tick() {
    $posts_array = $this->sitemap->get_all_posts();
    foreach($posts_array as $post) {
      $slug = $this->return_slug($post);
      $post_object = get_page_by_path($slug, 'OBJECT', 'post');
      if (!$post_object) {
        $check = Sync::importUrl($post);
        if ($check->success) {
          $this->notify->post_import_complete($check->post->id);
          print_r($check);
          exit;
        } else {
          print_r($check);exit;
        }
      }
    }
  }

  public function test_cron() {
    $this->notify->post_import_complete();
  }

  public function return_slug($url) {
    $pos = strrpos($url, '/');
    $slug = $pos === false ? $url : substr($url, $pos + 1);
    return $slug;
  }
}
