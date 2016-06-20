<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;
use AgreableCatfishImporterPlugin\Services\Widgets\Paragraph;
use AgreableCatfishImporterPlugin\Services\Widgets\Heading;
use AgreableCatfishImporterPlugin\Services\Widgets\Embed;

class Html {
  public static function checkIfValidParagraph($html_string) {
    $allowable_tags = '<a><b><i><br><em><strong><p><h3>';
    $stripped_string = strip_tags($html_string, $allowable_tags);
    $test = ($html_string == $stripped_string);
    return $test;
  }

  public static function getFromWidgetDom($widgetDom) {
    if (self::checkIfValidParagraph($widgetDom->innertext)) {
      return Paragraph::getFromWidgetDom($widgetDom);
    }
    else {
      return array_filter(self::breakIntoWidgets($widgetDom));
    }
    return $widgetData;
  }

  public static function breakIntoWidgets($widgetDom) {
    $widgets = [];
    $previous_was_paragraph = false;
    $current_paragraph_string = "";
    foreach($widgetDom->find('*') as  $index=>$node) {
      if (self::checkIfValidParagraph($node->outertext)) {
        $current_paragraph_string .= $node->outertext;
      } else {
        if (!empty($current_paragraph_string)) {
          $paragraph_html = new \simple_html_dom();
          $paragraphDom = $paragraph_html->load($current_paragraph_string);
          $current_paragraph_string = "";
          array_push($widgets, Paragraph::getFromWidgetDom($paragraphDom));
        }
        if (isset($node->find('h2')[0])) {
          array_push($widgets, Heading::getFromWidgetDom($node));
        }
        if ($embedData = Embed::getFromWidgetDom($node)) {
          array_push($widgets, $embedData);
        } else {
          if (self::checkStringAgainstBlacklist($node->outertext)) {
            $html = new stdClass();
            $html->type = 'html';
            $html->html = $node->outertext;
            array_push($widgets, $html);
          }
        }
      }
    }
    return count($widgets) ? $widgets : false;
  }

  public static function checkStringAgainstBlacklist($string) {
    $allowed = true;
    $blacklist = array(
      'platform.twitter.com'
    );

    foreach($blacklist as $check) {
      if (preg_match("/$check/", $string)) {
        $allowed = false;
      }
    }

   return $allowed;
  }
}
