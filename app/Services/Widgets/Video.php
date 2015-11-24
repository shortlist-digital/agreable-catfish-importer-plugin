<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

class Video {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'video';
    $widgetData->video = new stdClass();
    $videoIframe = $widgetDom->find('iframe');
    if (!isset($videoIframe[0])) {
      return $widgetData;
    }

    $widgetData->video->url = $videoIframe[0]->src;

    $innerDom = $widgetDom->find('.article__content__inline-video');
    if (count($innerDom) > 0) {
      $classes = $innerDom[0]->class;

      if (strpos($classes, 'inline-video--full') !== false) {
        $widgetData->video->width = 'full';
      } else if (strpos($classes, 'inline-video--medium') !== false) {
        $widgetData->video->width = 'medium';
      } else {
        $widgetData->video->width = 'small';
      }

      if (strpos($classes, 'inline-video--center') !== false) {
        $widgetData->video->position = 'center';
      } else if (strpos($classes, 'inline-video--left') !== false) {
        $widgetData->video->position = 'left';
      } else {
        $widgetData->video->position = 'right';
      }
    }
    return $widgetData;
  }
}
