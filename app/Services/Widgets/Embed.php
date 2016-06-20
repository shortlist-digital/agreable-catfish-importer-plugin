<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Embed {
  public static function getFromWidgetDom($widgetDom) {
    if (preg_match('/iframe/', $widgetDom->innertext)) {
      return self::handleFrame($widgetDom);
    } elseif (preg_match('/blockquote/', $widgetDom->innertext)) {
      return self::handleBlock($widgetDom);
    } else {
      return false;
    }
  }

  public static function handleFrame() {
    return false;
  }

  public static function handleBlock($widgetDom) {
    $links = $widgetDom->find('a');
    foreach($links as $link) {
      $href = $link->href;
      if (preg_match('/(?=.*twitter)(?=.*status)/', $href)) {
        $widgetData = new stdClass();
        $widgetData->type = 'embed';
        $widgetData->embed = $href;
        $test = array();
        array_push($test, $widgetData);
        array_push($test, $widgetData);
        return $test;
        //return $widgetData;
        break;
      }
    }
  }
}

