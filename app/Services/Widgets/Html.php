<?php
namespace AgreableCatfishImporterPlugin\Services\Widgets;

use stdClass;
use Illuminate\Support\Collection;
use Sunra\PhpSimple\HtmlDomParser;
use AgreableCatfishImporterPlugin\Services\Widgets\Paragraph;
use AgreableCatfishImporterPlugin\Services\Widgets\Heading;
use AgreableCatfishImporterPlugin\Services\Widgets\Embed;

use Exception;

class Html {
  public static function checkIfValidParagraph($html_string) {
    $allowable_tags = '<a><b><i><br><em><sup><sub><strong><p><h3><ul><ol><li><span><center>';
    $stripped_string = strip_tags($html_string, $allowable_tags);
    $test = ($html_string == $stripped_string);
    if (ctype_space(strip_tags(html_entity_decode($html_string, ENT_HTML5, 'iso-8859-1')))) {
      return false;
    }
    return $test;
  }

  public static function getFromWidgetDom($widgetDom) {
    // Remove the <div class="legacy-custom-html"/> that Clock wrap around the content
    if (isset($widgetDom->find('.legacy-custom-html')[0])) {
      $widgetDom = $widgetDom->find('.legacy-custom-html')[0];
    }

    if (self::checkIfValidParagraph($widgetDom->innertext)) {
      // Return paragraph only posts as a single paragraphy widget
      return Paragraph::getFromWidgetDom($widgetDom);
    } else {
      // Break up mixed content articles into separate widgets
      // die(var_dump('array_filter(self::breakIntoWidgets($widgetDom))', array_filter(self::breakIntoWidgets($widgetDom))));
      return array_filter(self::breakIntoWidgets($widgetDom));
    }
  }

  public static function breakIntoWidgets($widgetDom) {

    $widgets = [];
    // Loop through all DOM nodes to create widgets from them
    foreach($widgetDom->find('*') as  $index => $node) {

      // Check if this DOM node is a valid paragraph widget
      if (self::checkIfValidParagraph($node->outertext)) {
        // Remove blank <p>&nbsp;</p> paragraphy
        $clean_paragraph = str_replace("<p>&nbsp;</p>", "", $node->outertext);

        $paragraphDom = HtmlDomParser::str_get_html($clean_paragraph);
        array_push($widgets, Paragraph::getFromWidgetDom($paragraphDom));
      } else {
        // Store H2 tags as headline widget
        if ($node->tag == 'h2') {
          array_push($widgets, Heading::getFromWidgetDom($node));
          continue;
        }
        // Store embeddible content as embed widgets
        if ($embedWidgets = Embed::getWidgetsFromDom($node)) {

          foreach ($embedWidgets as $widget)  {
            array_push($widgets, $widget);
          }

          continue;
        } else {
          // Skip excess script tags from a social embed string eg. "<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>"
          if (!self::checkIfEmbedScriptTag($node->outertext)) {
            $html = new stdClass();
            $html->type = 'html';
            $html->html = $node->outertext;
            array_push($widgets, $html);
          }
        }
      }
    }

    // Merge adjacent widgets of the same type together
    foreach($widgets as $index => $widget) {
      if ($index != 0) {
        $prev = $widgets[$index-1];
        if (($prev->type == 'html') && ($widget->type == 'html')) {
          $widget->html = $prev->html.$widget->html;
          unset($widgets[$index-1]);
        }
        if (($prev->type == 'paragraph') && ($widget->type == 'paragraph')) {
          $widget->paragraph = $prev->paragraph.$widget->paragraph;
          unset($widgets[$index-1]);
        }
      }
    }
    $widgets = array_values($widgets);

    // Use the Laravel Collection class to group widgets
    $widgetCollection = new Collection($widgets);

    // Check if post contains widgets with the html type
    $htmlCheck = $widgetCollection->reduce(function ($carry, $widget) {
      return ($widget->type == 'html');
    });
    if ($htmlCheck) {
      $widgets = array($widgetCollection->reduce(function ($carry, $item) {
        try {
          if (!$carry) {
            $carry = new stdClass();
            $carry->html = '';
          }
          $carry->type = $item->type;
          $carry->html .= $item->html;

        // Collection doesn't error so catch any Exceptions hee
        } catch (Exception $e) {
          print_r($e);
        }
        return $carry;
      }));
    }
    return count($widgets) ? $widgets : false;
  }

  /**
   * Check if the DOM element is a excess script tag from a social embed string
   */
  public static function checkIfEmbedScriptTag($string) {
    $whitelist = array(
      'platform.twitter.com',
      'platform.instagram.com'
    );
    foreach($whitelist as $check) {
      if (preg_match("/$check/", $string)) {
        return true;
      }
    }
    return false;
  }
}
