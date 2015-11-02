<?php
namespace AgreableCatfishImporterPlugin\Services;

use \TimberPost;
use \stdClass;
use Sunra\PhpSimple\HtmlDomParser;

class Widget {
  public static function makeWidget($widgetName, stdClass $data) {
    $widget = clone $data;
    $widget->acf_fc_layout = $widgetName;
    return $widget;
  }

  public static function addWidgetToWidgets($widget, $widgets) {
    $widgets[] = $widget;
  }

  public static function setPostWidgets(TimberPost $post, array $widgets) {
    $meta_article_widgets = [];
    foreach ($widgets as $key => $widget) {
      $meta_article_widgets[] = $widget->acf_fc_layout;

      $metaLabel = 'article_widgets_' . $key;

      switch ($widget->acf_fc_layout) {
        case 'paragraph':
          self::setPostMetaProperty($post, $metaLabel . '_paragraph', 'widget_paragraph_html', $widget->paragraph);
          break;
        case 'image':
          $image = new \Mesh\Image($widget->image->src);

          self::setPostMetaProperty($post, $metaLabel . '_image', 'widget_image_image', $image->id);
          self::setPostMetaProperty($post, $metaLabel . '_border', 'widget_image_border', 0);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_image_width', $widget->image->width);
          self::setPostMetaProperty($post, $metaLabel . '_position', 'widget_image_position', $widget->image->position);
          self::setPostMetaProperty($post, $metaLabel . '_crop', 'widget_image_crop', 'original');
          break;
      }

    }

    update_post_meta($post->id, '_article_widgets', 'article_widgets');
    update_post_meta($post->id, 'article_widgets', serialize($meta_article_widgets));
  }

  public static function getPostWidgets(TimberPost $post) {
    return $post->get_field('article_widgets');
  }

  public static function getPostWidgetsFiltered(TimberPost $post, $name = null, $index = null) {
    $widgets = self::getPostWidgets($post);
    if ($name) {
      $filteredWidgets = [];
      foreach($widgets as $key => $widget) {
        if ($widget['acf_fc_layout'] === $name) {
          $filteredWidgets[] = $widget;
        }
      }
      $widgets = $filteredWidgets;
    }

    if ($index !== null) {
      if (isset($widgets[$index])) {
        return $widgets[$index];
      }

      return null;
    }
    return $widgets;
  }

  public static function getWidgetsFromUrl($articleUrl) {
    $articleHtml =  HtmlDomParser::file_get_html($articleUrl);
    if (!$articleHtml) {
      throw new \Exception('Could not retrieve widgets from ' . $articleUrl);
    }

    $widgets = array();

    foreach($articleHtml->find('.article__content .widget') as $widget) {

      $widgetData = new stdClass();

      // Get class name
      $matches = [];
      preg_match('/widget--([a-z-0-9]*)/', $widget->class, $matches);
      if (count($matches) !== 2) {
        throw new \Exception('Expected to retrieve widget name from class name');
      }
      $widgetData->type = $matches[1];

      switch ($widgetData->type) {
        case 'html':
          $widgetData->type = 'paragraph';
          $widgetData->paragraph = $widget->innertext;
          break;
        case 'inline-image':
          $widgetData->type = 'image';
          $widgetData->image = new stdClass();
          $image = $widget->find('img');
          $widgetData->image->src = $image[0]->src;
          $widgetData->image->filename = substr($widgetData->image->src, strrpos($widgetData->image->src, '/') + 1);
          $widgetData->image->name = substr($widgetData->image->filename, 0, strrpos($widgetData->image->filename, '.'));
          $widgetData->image->extension = substr($widgetData->image->filename, strrpos($widgetData->image->filename, '.') + 1);

          $imageCaptionElements = $widget->find('.inline-image__caption');
          if (count($imageCaptionElements) > 0) {
            $widgetData->image->caption = $imageCaptionElements[0]->innertext;
          }

          $inlineImageElements = $widget->find('.inline-image');
          if (count($inlineImageElements) > 0) {
            $classes = $inlineImageElements[0]->class;

            if (strpos($classes, 'inline-image--full') !== false) {
              $widgetData->image->width = 'full';
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

          break;
      }


      $widgets[] = self::makeWidget($widgetData->type, $widgetData);
    }

    return $widgets;
  }

  protected static function setPostMetaProperty(TimberPost $post, $acfKey, $widgetProperty, $value) {
    update_post_meta($post->id, $acfKey, $value);
    update_post_meta($post->id, '_' . $acfKey, $widgetProperty);
  }

}
