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

  public function error($message) {
    $this->client->attach([
      'fallback' =>  $message,
      'color' => '#FF3B30',
      'text' => $message
    ])->send('Something went wrong');
  }

  public function post_import_complete($post_id = false) {

    $post = new TimberPost($post_id);
    $current_time = gmdate(DATE_RFC2822);
    $this->client->attach([
      'fallback' =>  "Imported $post->post_title from $this->site_name",
      'color' => '#4CD964',
      'text' => "*".$post->post_title."*",
      'fields' => [
        [
          'title' => 'Site',
          'value' => $this->site_name,
          'short' => true
        ],
        [
          'title' => 'Type',
          'value' => $post->article_type,
          'short' => true
        ],
        [
          'title' => 'Imported from:',
          'value' => $post->catfish_importer_url
        ],
        [
          'title' => 'Imported to:',
          'value' => get_permalink($post_id)
        ]

      ]
    ])->send("_New post imported at ".$current_time."_ ".date_default_timezone_get());
  }
}
