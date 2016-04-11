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

  public function test() {
    return $this->sitemap->get_all_posts_in_order();
  }

  public function tick() {
    $posts_array = $this->sitemap->get_all_posts_in_order();
    foreach($posts_array as $post) {
      $slug = $this->return_slug($post);
      $post_object = get_page_by_path($slug, 'OBJECT', 'post');
      if (!$post_object) {
        try {
          $check = Sync::importUrl($post);
        } catch (Exception $e) {
          $error_message = $e->getMessage();
          return $this->notify->error($error_message);
        }
        if ($check->success) {
          $this->notify->post_import_complete($check->post->id);
          print_r($check);
        } else {
          print_r($check);
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
