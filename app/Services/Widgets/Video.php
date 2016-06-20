<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

class Video {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'embed';
    $videoIframe = $widgetDom->find('iframe');
    $facebookVideo = $widgetDom->find('div[data-href]');

    if (!isset($videoIframe[0])) {
      $widgetData->embed = $facebookVideo[0]->{'data-href'};
    } else {
      $widgetData->embed = $videoIframe[0]->src;
    }

    return $widgetData;
  }
}
