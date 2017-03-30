<?php

namespace AgreableCatfishImporterPlugin\Controllers;

use AgreableCatfishImporterPlugin\Services\Sync;
use AgreableCatfishImporterPlugin\Services\Post;

class SyncController {

  public function retry() {
    // Have to use a query string, as wordpress tries to
    // parse the URL if it's in a /retry/{id} param
    $catfish_url = $_GET['url'];

    try {
      // Re queue this post for import
      Sync::queueUrl($catfish_url);

      // Return a redirect to the previous page (the post lists page)
      header('Location: ' . $_SERVER['HTTP_REFERER']);
    } catch (Exception $e) {
      throw new Exception("Error queueing post. ".$e->getMessage());
    }

  }

}
