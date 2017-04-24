<?php

namespace AgreableCatfishImporterPlugin\Hooks;

use add_filter;
use TimberPost;

// Show the Reimport option in the posts listings
class ImporterHooks {
  function __construct() {
    add_filter('post_row_actions', array($this, 'add_retry_button'), 10, 2);
  }

  public function add_retry_button($actions, $post) {
    $post = new TimberPost($post);
    if ($post->catfish_importer_url) {
      $actions['re_import'] = $this->get_html($post);
    }
    return $actions;
  }

  public function get_html($post) {
    $link = $this->get_link($post);
    // TODO only return if post has the meta catfish_importer_url
    return "<a title='Re-import from Catfish' class='reimport' href='$link'>Reimport</a>";
  }

  public function get_link($post) {
    $catfish_url = $post->catfish_importer_url;
    return get_bloginfo('url')."/catfish-import/retry?url=$catfish_url";
  }

}

new ImporterHooks;
