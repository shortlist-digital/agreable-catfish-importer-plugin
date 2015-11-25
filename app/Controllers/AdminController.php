<?php
namespace AgreableCatfishImporterPlugin\Controllers;

use \WP_Query;
use \TimberPost;

class AdminController {
  /**
   * Main admin view
   */
  public function index() {
    $query = array(
      'post_type' => 'post',
      // 'meta_key'  => 'catfish-importer_imported',
      'meta_key'  => 'catfish-importer_date-updated',
      // 'meta_value'  => true,
      'posts_per_page' => 50,
      'orderby' => 'meta_value_num',
      'order' => 'DESC',
    );

    $query = new WP_Query($query);
    $timberPosts = [];

    foreach($query->get_posts() as $post) {
      $timberPosts[] = new TimberPost($post);
    }

    return view('@AgreableCatfishImporterPlugin/admin/index.twig', ['posts' => $timberPosts]);
  }

  public function sync() {
    return view('@AgreableCatfishImporterPlugin/admin/sync.twig', ['ajax_url' => admin_url('admin-ajax.php')]);
  }
}
