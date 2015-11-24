<?php
namespace AgreableCatfishImporterPlugin\Services;

use \TimberPost;
use \stdClass;
use Sunra\PhpSimple\HtmlDomParser;
use AgreableCatfishImporterPlugin\Services\Widgets\InlineImage;
use AgreableCatfishImporterPlugin\Services\Widgets\Video;
use AgreableCatfishImporterPlugin\Services\Widgets\Html;

class Widget {
  public static function makeWidget($widgetName, stdClass $data) {
    $widget = clone $data;
    $widget->acf_fc_layout = $widgetName;
    return $widget;
  }

  public static function addWidgetToWidgets($widget, $widgets) {
    $widgets[] = $widget;
  }

  /**
   * Attach widgets to the $post via WP metadata
   */
  public static function setPostWidgets(TimberPost $post, array $widgets) {
    $widgetNames = [];

    foreach ($widgets as $key => $widget) {

      $metaLabel = 'article_widgets_' . $key;

      switch ($widget->acf_fc_layout) {
        case 'paragraph':
          self::setPostMetaProperty($post, $metaLabel . '_paragraph', 'widget_paragraph_html', $widget->paragraph);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'image':
          $image = new \Mesh\Image($widget->image->src);

          self::setPostMetaProperty($post, $metaLabel . '_image', 'widget_image_image', $image->id);
          self::setPostMetaProperty($post, $metaLabel . '_border', 'widget_image_border', 0);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_image_width', $widget->image->width);
          self::setPostMetaProperty($post, $metaLabel . '_position', 'widget_image_position', $widget->image->position);
          self::setPostMetaProperty($post, $metaLabel . '_crop', 'widget_image_crop', 'original');
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'video':
          self::setPostMetaProperty($post, $metaLabel . '_url', 'widget_video_url', $widget->video->url);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_video_width', $widget->video->width);
          self::setPostMetaProperty($post, $metaLabel . '_position', 'widget_video_position', $widget->video->position);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
      }

    }

    // This is an array of widget names for ACF
    update_post_meta($post->id, 'article_widgets', serialize($widgetNames));
    update_post_meta($post->id, '_article_widgets', 'article_widgets');
  }

  public static function getPostWidgets(TimberPost $post) {
    return $post->get_field('article_widgets');
  }

  /**
   * Get widgets from a post. If provided a widget name, only these are returned
   * If an index is provided only return the widget at that index
   */
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

  /**
   * Given a URL to an article, identify the widgets within HTML
   * and then build up an array of widget objects
   */
  public static function getWidgetsFromUrl($articleUrl) {
    $articleHtml =  HtmlDomParser::file_get_html($articleUrl);
    if (!$articleHtml) {
      throw new \Exception('Could not retrieve widgets from ' . $articleUrl);
    }

    $widgets = array();

    foreach($articleHtml->find('.article__content .widget') as $widget) {

      // Get class name
      $matches = [];
      preg_match('/widget--([a-z-0-9]*)/', $widget->class, $matches);
      if (count($matches) !== 2) {
        throw new \Exception('Expected to retrieve widget name from class name');
      }

      $widgetData = null;
      $widgetName = $matches[1];

      switch ($widgetName) {
        case 'html':
          $widgetData = Html::getFromWidgetDom($widget);
          break;
        case 'inline-image':
          $widgetData = InlineImage::getFromWidgetDom($widget);
          break;
        case 'video':
          $widgetData = Video::getFromWidgetDom($widget);
          break;
      }

      if ($widgetData) {
        $widgets[] = self::makeWidget($widgetData->type, $widgetData);
      }

    }

    return $widgets;
  }

  /**
   * A small helper for setting post metadata
   */
  protected static function setPostMetaProperty(TimberPost $post, $acfKey, $widgetProperty, $value) {
    update_post_meta($post->id, $acfKey, $value);
    update_post_meta($post->id, '_' . $acfKey, $widgetProperty);
  }
}
