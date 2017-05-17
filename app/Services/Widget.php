<?php
namespace AgreableCatfishImporterPlugin\Services;

use TimberPost;
use stdClass;
use Exception;
use Mesh;
use AgreableCatfishImporterPlugin\Services\Widgets\InlineImage;
use AgreableCatfishImporterPlugin\Services\Widgets\Video;
use AgreableCatfishImporterPlugin\Services\Widgets\Html;
use AgreableCatfishImporterPlugin\Services\Widgets\HorizontalRule;

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
  public static function setPostWidgets(TimberPost $post, array $widgets, stdClass $catfishPostObject) {

    $widgetNames = [];
    foreach ($widgets as $key => $widget) {

      $metaLabel = 'widgets_' . $key;

      switch ($widget->acf_fc_layout) {
        case 'embed':
          self::setPostMetaProperty($post, $metaLabel . '_embed', 'widget_embed', $widget->embed);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_embed_width', 'medium');
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'heading':
          self::setPostMetaProperty($post, $metaLabel . '_text', 'widget_heading_text', $widget->text);
          self::setPostMetaProperty($post, $metaLabel . '_aligment', 'widget_heading_alignment', $widget->alignment);
          self::setPostMetaProperty($post, $metaLabel . '_font', 'widget_heading_font', $widget->font);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'html':
          self::setPostMetaProperty($post, $metaLabel . '_html', 'widget_html', $widget->html);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'paragraph':
          self::setPostMetaProperty($post, $metaLabel . '_paragraph', 'widget_paragraph_html', $widget->paragraph);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'image':
          $image = new Mesh\Image($widget->image->src);

          self::setPostMetaProperty($post, $metaLabel . '_image', 'widget_image_image', $image->id);
          self::setPostMetaProperty($post, $metaLabel . '_border', 'widget_image_border', 0);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_image_width', $widget->image->width);
          self::setPostMetaProperty($post, $metaLabel . '_position', 'widget_image_position', $widget->image->position);
          self::setPostMetaProperty($post, $metaLabel . '_crop', 'widget_image_crop', 'original');

          if (isset($widget->image->caption)) {
            self::setPostMetaProperty($post, $metaLabel . '_caption', 'widget_image_caption', $widget->image->caption);
          }
          $widgetNames[] = $widget->acf_fc_layout;

          break;
        case 'video':
          self::setPostMetaProperty($post, $metaLabel . '_url', 'widget_video_url', $widget->video->url);
          self::setPostMetaProperty($post, $metaLabel . '_width', 'widget_video_width', $widget->video->width);
          self::setPostMetaProperty($post, $metaLabel . '_position', 'widget_video_position', $widget->video->position);
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'horizontal-rule':
          $widgetNames[] = $widget->acf_fc_layout;
          break;
        case 'promo':
          // Throw exception if promo widget found
          // To help decide if we need Promo widgets in the new pages CMS, throw an exception if a promo widget is found
          throw new Exception("Importer found a promo widget. Someone call Elliot.", 30);
          break;
      }

    }

    if ($catfishPostObject->type === 'gallery') {
      self::setGalleryWidget($post, $catfishPostObject, $widgetNames);
      $widgetNames[] = 'gallery';
    }

    // This is an array of widget names for ACF
    update_post_meta($post->id, 'widgets', serialize($widgetNames));
    update_post_meta($post->id, '_widgets', 'post_widgets');
  }

  protected static function setGalleryWidget($post, stdClass $postObject, $widgetNames) {
    $galleryApi = str_replace($postObject->__fullUrlPath, '/api/gallery-data' . $postObject->__fullUrlPath, $postObject->absoluteUrl);

    // Escape the url path using this handy helper
    $galleryApi = Sync::escapeAPIUrlPaths($galleryApi);

    if (!$galleryApiResponse = file_get_contents($galleryApi)) {
      throw new Exception('Unable to fetch gallery data from: ' . $galleryApi);
    }

    if (!$galleryData = json_decode($galleryApiResponse)) {
      throw new Exception('Unable to deserialise gallery API response');
    }

    if (!isset($galleryData->images) || !is_array($galleryData->images)) {
      throw new Exception('Was expecting an array of images in gallery data');
    }

    $imageIds = [];
    foreach($galleryData->images as $image) {

      $title = $image->title;
      if ($title == ".") {
        $title = $post->title;
      }
      $imageUrl = array_pop($image->__mainImageUrls);

      // Sideload the image
      $post_data = array(
        // 'post_title' => $title,
        'post_content' => $image->description,
        'post_excerpt' => $image->description
      );

      $post_attachement_id = self::simple_image_sideload($imageUrl.'.jpg', $post->ID, $title, $post_data);

      $imageIds[] = $post_attachement_id;
    }

    self::setPostMetaProperty($post, 'widgets_' . count($widgetNames) . '_gallery_items', 'widget_gallery_galleryitems', serialize($imageIds));
  }

  /**
   * Function to sideload image from Clock to Wordpress
   *
   * Adapted from Mark Wilkinson's function:
   * https://markwilkinson.me/2015/07/using-the-media-handle-sideload-function/
   */
  public function simple_image_sideload($url, $post_id, $desc, $post_data) {

    /**
     * download the url into wordpress
     * saved temporarly for now
     */
    $tmp = download_url( $url );
    /**
     * biild an array of file information about the url
     * getting the files name using basename()
     */
    $file_array = array(
        'name' => basename( $url ),
        'tmp_name' => $tmp
    );
    /**
     * Check for download errors
     * if there are error unlink the temp file name
     */
    if ( is_wp_error( $tmp ) ) {
        @unlink( $file_array[ 'tmp_name' ] );
        return $tmp;
    }
    /**
     * now we can use the sideload function
     * we pass it the file array of the file to handle
     * and the post id of the post to attach it too
     * it returns the attachment id if the file
     */
    $id = media_handle_sideload( $file_array, $post_id, $desc, $post_data );
    /**
     * check for handle sideload errors
     * if errors again unlink the file
     */
    if ( is_wp_error( $id ) ) {
        @unlink( $file_array['tmp_name'] );
        return $id;
    }
    /**
     * get the url from the newly upload file
     * $value now contians the file url in WordPress
     * $id is the attachment id
     */
    $value = wp_get_attachment_url( $id );

    return $id;
  }

  public static function getPostWidgets(TimberPost $post) {
    return $post->get_field('widgets');
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
   * Given a URL to an post, identify the widgets within HTML
   * and then build up an array of widget objects
   */
  public static function getWidgetsFromDom($postDom) {

    if (!$postDom) {
      throw new \Exception('Could not retrieve widgets from ' . $postUrl);
    }

    $widgets = array();

    foreach($postDom->find('.article__content .widget__wrapper') as $widgetWrapper) {

      if (isset($widgetWrapper->find('.widget')[0])) {
        $widget = $widgetWrapper->find('.widget')[0];

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
      } else if (isset($widgetWrapper->find('hr')[0])) {
        $widget = $widgetWrapper->find('hr')[0];
        $widgetData = HorizontalRule::getFromWidgetDom($widget);
      }

      if (is_array($widgetData)) {
        foreach($widgetData as $widget) {
          $widgets[] = self::makeWidget($widget->type, $widget);
        }
      } elseif ($widgetData) {
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
