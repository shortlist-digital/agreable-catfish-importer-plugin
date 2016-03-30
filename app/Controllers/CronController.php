<?php
namespace AgreableCatfishImporterPlugin\Controllers;



class CronController {
  function __construct() {
  $slack_webhook_url = 'https://hooks.slack.com/services/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';
    $settings = array(
      'username' => 'Catfish Importer'
    );
    $this->client = $client = new \Maknz\Slack\Client($slack_webhook_url, $settings);
  }


  public function tick() {
    $this->test_cron();
  }

  public function test_cron() {
    $current_time = date(DATE_RFC2822);
    $this->client->attach([
      'fallback' =>  'Imported POST NAME from SITE',
      'color' => '#4CD964',
      'fields' => [
        [
          'title' => 'Site',
          'value' => 'Shortlist Pages',
          'short' => true
        ],
        [
          'title' => 'Post Name',
          'value' => 'Example Post Name',
          'short' => true
        ]
      ]
    ])->send("New cron initiated message at $current_time");
  }
}
