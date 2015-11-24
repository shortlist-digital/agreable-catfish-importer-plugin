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
      // 'meta_key'  => 'article_catfish-importer_is-imported',
      // 'meta_value'  => true,
      'posts_per_page' => 50,
    );

    $query = new WP_Query($query);
    $timberPosts = [];

    foreach($query->get_posts() as $post) {
      $timberPosts[] = new TimberPost($post);
    }

    return view('@AgreableCatfishImporterPlugin/admin/index.twig', ['posts' => $timberPosts]);
  }
}
