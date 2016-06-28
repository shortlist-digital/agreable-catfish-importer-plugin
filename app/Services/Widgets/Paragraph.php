<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;

class Paragraph {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'paragraph';
    $widgetData->paragraph = $widgetDom->innertext;
    return $widgetData;
  }
}
