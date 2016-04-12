<?php

namespace AgreableCatfishImporterPlugin\Controllers;

use AgreableCatfishImporterPlugin\Services\Notification;
use AgreableCatfishImporterPlugin\Services\Post;

class SlackFeedbackController {

  function __construct() {
    $this->notifiy = new Notification;
  }

  public function retry() {
    // Have to use a query string, as wordpress tries to
    // parse the URL if it's in a /retry/{id} param
    $catfish_url = $_GET['url'];
    $check = Post::getPostFromUrl($catfish_url);
    if ($check) {
      $permalink = get_permalink($check->id);
      header("Location: $permalink");
    } else {
      echo "<h1>Failed to import</h1>";
      echo "<p>Consider trashing</p>";
    }
  }

  public function ignore() {
    $catfish_url = $_GET['url'];
  }


}

