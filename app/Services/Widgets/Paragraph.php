<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Paragraph {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'paragraph';
    $widgetDom = self::filterBadTags($widgetDom);
    // Catch $widgetDom == false
    if($widgetDom->innertext) {
      $widgetData->paragraph = $widgetDom->innertext;
      return $widgetData;
    }
    return $widgetDom;
  }

  public static function filterBadTags($html) {
    $badTags = 'span, center';
    // Catch if $html does not have the find() member function
    if($html) {
      if (count($html->find($badTags))) {
        foreach($html->find($badTags) as $index=>$element) {
          $html->find($badTags, $index)->outertext = $element->innertext;
        }
      }
    }
    return $html;
  }
}
