<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

class InlineImage {
  public static function getFromWidgetDom($widgetDom) {

    $widgetData = new stdClass();
    $widgetData->type = 'image';
    $widgetData->image = new stdClass();
    $image = $widgetDom->find('img');
    $widgetData->image->src = $image[0]->src;
    $widgetData->image->filename = substr($widgetData->image->src, strrpos($widgetData->image->src, '/') + 1);
    $widgetData->image->name = substr($widgetData->image->filename, 0, strrpos($widgetData->image->filename, '.'));
    $widgetData->image->extension = substr($widgetData->image->filename, strrpos($widgetData->image->filename, '.') + 1);

    $imageCaptionElements = $widgetDom->find('.inline-image__caption');
    if (count($imageCaptionElements) > 0) {
      $widgetData->image->caption = $imageCaptionElements[0]->innertext;
    }

    $inlineImageElements = $widgetDom->find('.inline-image');
    if (count($inlineImageElements) > 0) {
      $classes = $inlineImageElements[0]->class;

      if (strpos($classes, 'inline-image--full') !== false) {
        $widgetData->image->width = 'large';
      } else if (strpos($classes, 'inline-image--medium') !== false) {
        $widgetData->image->width = 'medium';
      } else {
        $widgetData->image->width = 'small';
      }

      if (strpos($classes, 'inline-image--center') !== false) {
        $widgetData->image->position = 'center';
      } else if (strpos($classes, 'inline-image--left') !== false) {
        $widgetData->image->position = 'left';
      } else {
        $widgetData->image->position = 'right';
      }
    }
    return $widgetData;
  }
}
