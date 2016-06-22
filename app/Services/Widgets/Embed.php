<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Embed {
  public static function getFromWidgetDom($widgetDom) {
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
    $url = $frame[0]->src;
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    if (isset($query['href'])) {
      $href = $query['href'];
      $url = $href;
    }
    $check = wp_oembed_get($url);
    if ($check) {
      $widgetData = new stdClass();
      $widgetData->type = 'embed';
      $widgetData->embed = $url;
      return $widgetData;
      break;
    }
    return false;
  }

  public static function handleBlock($widgetDom) {
    $links = $widgetDom->find('a');
    foreach(array_reverse($links) as $link) {
      $href = $link->href;
      $check = wp_oembed_get($href);
      if ($check) {
        $widgetData = new stdClass();
        $widgetData->type = 'embed';
        $widgetData->embed = $href;
        return $widgetData;
        break;
      }
    }
  }
}

