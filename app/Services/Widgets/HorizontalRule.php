<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use \stdClass;

class HorizontalRule {
  public static function getFromWidgetDom($widgetDom) {
    $widgetData = new stdClass();
    $widgetData->type = 'horizontal-rule';
    return $widgetData;
  }
}
