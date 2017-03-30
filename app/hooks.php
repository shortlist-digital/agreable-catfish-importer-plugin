<?php

namespace AgreableCatfishImporterPlugin\Hooks;

use add_filter;
use TimberPost;

// Show the Reimport option in the posts listings
//

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

// Register WP Cron for Scan Updates function
// Create new cron to run every mintues
// See http://codex.wordpress.org/Plugin_API/Filter_Reference/cron_schedules

// Native WP Cron is disabled so this cron needs to be run by calling the full
// cron url like so:
// wget http://www.example.com/wp/wp-cron.php?doing_wp_cron=1 > /dev/null 2>&1

// require_once(ABSPATH . 'wp-settings.php');
add_filter( 'cron_schedules', 'catfish_cron_every_minute' );
function catfish_cron_every_minute( $schedules ) {
    $schedules['every_minute'] = array(
            'interval'  => 60,
            'display'   => __( 'Every Minute', 'textdomain' )
    );
    return $schedules;
}

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'catfish_cron_every_minute' ) ) {
    wp_schedule_event( time(), 'every_minute', 'updatedPostScan' );
}

// Hook into that action that'll fire every three minutes
add_action( 'catfish_cron_every_minute', 'callUpdatedPostCan' );
