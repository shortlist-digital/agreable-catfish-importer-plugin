<?php
namespace AgreableCatfishImporterPlugin\Services;

use \TimberPost;

class Notification {
  function __construct() {
  date_default_timezone_set('GMT');
  $slack_webhook_url = 'https://hooks.slack.com/services/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';
    $settings = array(
      'username' => 'Catfish Importer'
    );
    $this->site_name = get_bloginfo('name');
    $this->client = $client = new \Maknz\Slack\Client($slack_webhook_url, $settings);
  }

  public function post_import_complete($post_id = false) {
    if ($post_id) {
      $post = new TimberPost($post_id);
    }

    $current_time = gmdate(DATE_RFC2822);
    $this->client->attach([
      'fallback' =>  "Imported POST NAME from $this->site_name",
      'color' => '#4CD964',
      'text' => 'http://pages-local.stylist.co.uk/example-guid',
      'fields' => [
        [
          'title' => 'Site',
          'value' => $this->site_name,
          'short' => true
        ],
        [
          'title' => 'Post Name',
          'value' => 'Example Post Name',
          'short' => true
        ]
      ]
    ])->send("_New post imported at ".$current_time."_ ".date_default_timezone_get());
  }
}
