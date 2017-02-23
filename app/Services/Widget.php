<?php
namespace AgreableCatfishImporterPlugin\Services;

use TimberPost;
use stdClass;
use Exception;
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
          // Upload the image and return image id
          $image_id = self::sideLoadImage($widget->image->src);

          self::setPostMetaProperty($post, $metaLabel . '_image', 'widget_image_image', $image_id);
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
        $title = "";
      }
      $imageUrl = array_pop($image->__mainImageUrls);

      // Upload the image and return image id
      $image_id = self::sideLoadImage($imageUrl);

      $imagePost = get_post($image_id);
      $imagePost->post_title = $title;
      $imagePost->post_excerpt = $image->description;

      wp_update_post($imagePost);

      $imageIds[] = $image_id;
    }

    self::setPostMetaProperty($post, 'widgets_' . count($widgetNames) . '_gallery_items', 'widget_gallery_galleryitems', serialize($imageIds));
  }

  public static function getPostWidgets(TimberPost $post) {
    // return ~ $post->get_field('widgets');
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
   * Create an image for image widgets and gallery widgets to use
   */
  protected static function sideLoadImage($url) {
    $image_exists = self::checkIfImageExists( $url );
		if ($image_exists) {
			$upload_info = self::getImageData( $url );
			$id = self::createImage( $upload_info );
		} else {
			$url = str_replace(' ', '%20', $url);
			$upload_info = self::uploadImage( $url );
			$id = self::createImage( $upload_info );
		}
		return $id;
  }

  /**
   * create function adapted from Mesh for performance
   */
	protected static function createImage( $image_info, $post_type = 'attachment' ) {
		$filename = $image_info['file'];
		$pathinfo = pathinfo( $filename );
		$filetype = wp_check_filetype( basename( $filename ), null );
		$data = array(
			'post_title' => $pathinfo['basename'],
			'post_mime_type' => $filetype['type'],
			'guid' => $image_info['url'],
			'post_type' => $post_type,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		$pid = wp_insert_attachment( $data, $filename, 1 );
		if ( !function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}
		$metadata = wp_generate_attachment_metadata( $pid, $image_info['file'] );
		wp_update_attachment_metadata( $pid, $metadata );
		return $pid;
	}

  /**
   * uploadImage function adapted from Mesh for performance
   */
	protected static function uploadImage( $url ) {
		$location = self::getSideloadedFileLocation( $url );
		if ( !function_exists( 'download_url' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
		}
		$tmp = download_url( $url );
		$file_array = array();
		$file_array['tmp_name'] = $tmp;
		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}
		// do the validation and storage stuff
		$locinfo = pathinfo( $location );
		return wp_upload_bits( $locinfo['basename'], null, file_get_contents( $file_array['tmp_name'] ) );
	}

	/**
   * Image utils adapted from Mesh for performance
   */
	public static function getSideloadedFileLocation( $url ) {
		$upload = wp_upload_dir();
		$dir = $upload['path'];
		$file = parse_url( $url );
		$path_parts = pathinfo( $file['path'] );
		$basename = md5( $url );
		$ext = 'jpg';
		if ( isset( $path_parts['extension'] ) ) {
			$ext = $path_parts['extension'];
		}
		return $dir . '/' . $basename . '.' . $ext;
	}

  /**
   * checkIfImageExists utils adapted from Mesh for performance
   */
	protected static function checkIfImageExists( $url ) {
		$file_name_in_fs = self::getSideloadedFileLocation( $url );
		if ( file_exists( $file_name_in_fs ) ) {
			return true;
		}
		return false;
	}

  /**
   * getImageData utils adapted from Mesh for performance
   */
  protected static function getImageData( $url ) {
		$location = self::getSideloadedFileLocation( $url );
		$new_url = str_replace(ABSPATH, '', $location);
		$new_url = get_site_url().'/'.$new_url;
		$data = array('file' => $location, 'url' => $new_url);
		return $data;
	}

  /**
   * A small helper for setting post metadata
   */
  protected static function setPostMetaProperty(TimberPost $post, $acfKey, $widgetProperty, $value) {
    update_post_meta($post->id, $acfKey, $value);
    update_post_meta($post->id, '_' . $acfKey, $widgetProperty);
  }
}
