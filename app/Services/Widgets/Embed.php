<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Embed {
  public static function getWidgetsFromDom($widgetDom) {

    // var_dump($widgetDom->outertext);

    if (preg_match('/iframe/', $widgetDom->outertext)) {
      return self::handleFrame($widgetDom);
    } elseif (preg_match('/blockquote/', $widgetDom->outertext)) {
      return self::handleBlock($widgetDom);
    } else {
      return false;
    }
  }

  public static function handleFrame($widgetDom) {
    $frame = $widgetDom->find('iframe');
    if (isset($frame[0])) {
      $url = $frame[0]->src;
      $parts = parse_url($url);
      if (isset($parts['query'])) {
        parse_str($parts['query'], $query);
        if (isset($query['href'])) {
          $href = $query['href'];
          $url = $href;
        }
      }
      $check = wp_oembed_get($url);
      if ($check) {
        $widgetData = new stdClass();
        $widgetData->type = 'embed';
        $widgetData->embed = $url;
        return $widgetData;
        break;
      }
    }
    return false;
  }

  public static function handleBlock($widgetDom) {

    // Separate multiple embeds in a row
    $widgets = [];

    $blockquotes = $widgetDom->find('blockquote');

    foreach ($blockquotes as $blockquote) {

      $links = $blockquote->find('a');

      // Take the last link in each blockquote which should be the link to the tweet
      foreach(array_reverse($links) as $link) {

        $href = $link->href;
        $check = wp_oembed_get($href);
        if ($check) {
          $widgetData = new stdClass();
          $widgetData->type = 'embed';
          $widgetData->embed = $href;

          array_push($widgets, $widgetData);
          // Break because we only want the link to the embed not all the other links in the tweet.
          break;
        }
      }
    }

    $widgets = array_values($widgets);
    return count($widgets) ? array_filter($widgets) : false;
  }
}
