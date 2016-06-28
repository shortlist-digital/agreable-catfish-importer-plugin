<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;
use Sunra\PhpSimple\HtmlDomParser;
use AgreableCatfishImporterPlugin\Services\Widgets\Paragraph;
use AgreableCatfishImporterPlugin\Services\Widgets\Heading;
use AgreableCatfishImporterPlugin\Services\Widgets\Embed;

class Html {
  public static function checkIfValidParagraph($html_string) {
    $allowable_tags = '<a><b><i><br><em><strong><p><h3><ul><ol><li>';
    $stripped_string = strip_tags($html_string, $allowable_tags);
    $test = ($html_string == $stripped_string);
    if (ctype_space(strip_tags(html_entity_decode($html_string, ENT_HTML5, 'iso-8859-1')))) {
      echo "woo"; die;
      return false;
    }
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
        $new_paragraph = str_replace("<p>&nbsp;</p>", "", $node->outertext);
        $current_paragraph_string .= $new_paragraph;
      } else {
        if (!empty($current_paragraph_string)) {
          $paragraphDom = HtmlDomParser::str_get_html($current_paragraph_string);
          array_push($widgets, Paragraph::getFromWidgetDom($paragraphDom));
          $current_paragraph_string = "";
        }
        if ($node->tag == 'h2') {
          array_push($widgets, Heading::getFromWidgetDom($node));
          continue;
        }
        if ($embedData = Embed::getFromWidgetDom($node)) {
          array_push($widgets, $embedData);
          continue;
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
    if (!empty($current_paragraph_string)) {
      $paragraphDom = HtmlDomParser::str_get_html($current_paragraph_string);
      array_push($widgets, Paragraph::getFromWidgetDom($paragraphDom));
    }
    return count($widgets) ? $widgets : false;
  }

  public static function checkStringAgainstBlacklist($string) {
    $allowed = true;
    $blacklist = array(
      'platform.twitter.com',
      'platform.instagram.com'
    );

    foreach($blacklist as $check) {
      if (preg_match("/$check/", $string)) {
        $allowed = false;
      }
    }

   return $allowed;
  }
}
