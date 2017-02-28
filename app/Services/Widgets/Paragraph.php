<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Paragraph {
  public static function getFromWidgetDom($widgetDom) {
    // TODO: Trying to get property of non-object
    // Catch bug on long tail post
    if(!is_object($widgetDom)) {
      // Dom object not passed
      die(var_dump('Widget DOM not passed as an object.', $widgetDom, debug_backtrace()));
      return null;
    }
    $widgetData = new stdClass();
    $widgetData->type = 'paragraph';
    $widgetDom = self::filterBadTags($widgetDom);
    $widgetData->paragraph = $widgetDom->innertext;
    return $widgetData;
  }

  public static function filterBadTags($html) {
    $badTags = 'span, center';
    // TODO: Trying to get property of non-object
    // catch $html is boolean and work out why
    if( !is_object($html) ) {
      die(var_dump('$html not passed as a DOM object object.', $widgetDom));
      return null;
    }
    if (count($html->find($badTags))) {
      foreach($html->find($badTags) as $index=>$element) {
        $html->find($badTags, $index)->outertext = $element->innertext;
      }
    }
    return $html;
  }
}
