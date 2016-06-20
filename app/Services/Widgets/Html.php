<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;
use AgreableCatfishImporterPlugin\Services\Widgets\Paragraph;
use AgreableCatfishImporterPlugin\Services\Widgets\Heading;
use AgreableCatfishImporterPlugin\Services\Widgets\Embed;

class Html {
  public static function getFromWidgetDom($widgetDom) {
    $allowable_tags = '<a><b><i><br><em><strong><p><h3>';
    $html_string = $widgetDom->innertext;
    $stripped_string = strip_tags($html_string, $allowable_tags);
    $paragraph_test = ($html_string == $stripped_string);
    if ($paragraph_test) {
      return Paragraph::getFromWidgetDom($widgetDom);
    }
    if (isset($widgetDom->find('h2')[0])) {
      return Heading::getFromWidgetDom($widgetDom);
    }
    if ($embedData = Embed::getFromWidgetDom($widgetDom)) {
      return $embedData;
    }
    $widgetData = new stdClass();
    $widgetData->type = $paragraph_test ? 'paragraph' : 'html';
    $field_name = $paragraph_test ? 'paragraph' : 'html';
    $widgetData->{$field_name} = $widgetDom->innertext;
    return $widgetData;
  }
}
