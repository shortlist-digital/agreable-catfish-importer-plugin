<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Paragraph {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'paragraph';
    $widgetDom = self::filterBadTags($widgetDom);
    $widgetData->paragraph = $widgetDom->innertext;
    return $widgetData;
  }

  public static function filterBadTags($html) {
    $badTags = 'span, center';
    if (count($html->find($badTags))) {
      foreach($html->find($badTags) as $index=>$element) {
        $html->find($badTags, $index)->outertext = $element->innertext;
      }
    }
    return $html;
  }
}
