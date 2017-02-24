<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \TimberPost;
use Sunra\PhpSimple\HtmlDomParser;
use AgreableCatfishImporterPlugin\Services\Notification;

use AgreableCatfishImporterPlugin\Services\User;
use AgreableCatfishImporterPlugin\Services\Category;

use Exception;

class Post {

  public static function notifyError($message) {
    $notifier = new Notification;
    $notifier->error($message);
  }

  public static function getPostFromUrl($postUrl) {
    $fail = false;
    $postJsonUrl = $postUrl . '.json';
    try {
      $postString = file_get_contents($postJsonUrl);
    } catch (Exception $e) {
      self::notifyError('Unable to retrieve JSON from URL ' . $postJsonUrl);
      return false;
    }

    if (!$object = json_decode($postString)) {
      self::notifyError('Unable to retrieve JSON from URL ' . $postJsonUrl);
      return false;
    }

    if (!isset($object->article)) {
      self::notifyError('"Article" property does not exist in JSON, might be a full page embed or microsite');
      return false;
    }

    // XXX: Create master post array to save into Wordpress

    $postObject = $object->article; // The article in object from as retrieved from Clock CMS API
    $postDom = HtmlDomParser::str_get_html($object->content); // A parsed object of the post content to be split into ACF widgets as a later point

    // Set post published date
    $displayDate = strtotime($postObject->displayDate);
    $displayDate = date('o\-m\-d G\:i\:s', $displayDate);

    // If no sell exists on this post then create it from the headline
    $sell = empty($postObject->sell) ? $postObject->headline : $postObject->sell;

    // Create the base array for the new Wordpress post
    $postArrayForWordpress = array(
      'post_name' => $postObject->slug,
      'post_title' => $postObject->headline,
      'post_date' => $displayDate,
      'post_date_gmt' => $displayDate,
      'post_modified' => $displayDate,
      'post_modified_gmt' => $displayDate
    );

    // Create or select Author ID
    if (isset($object->article->__author) &&
        isset($object->article->__author->emailAddress) &&
        $object->article->__author->emailAddress) {

      $postArrayForWordpress['post_author'] = self::setAuthor($object->article->__author);
    } else {
      $get_author_details = get_field('catfish_default_author', 'option');
      $default_author = $get_author_details['ID'];
      $postArrayForWordpress['post_author'] = $default_author;
    }

    // Create meta array for new post (Data that's not in the core post_fields)
    $postMetaArrayForWordpress = array(
      'short_headline' => $postObject->shortHeadline,
      'sell' => $sell,
      'header_type' => 'standard-hero',
      'header_display_headline' => true,
      'header_display_sell' => true,
      'catfish_importer_url' => $postUrl,
      'catfish_importer_imported' => true,
      'catfish_importer_date_updated' => time()
    );

    // If automated testing, set some metadata
    if (isset($_SERVER['is-automated-testing'])) {
      $postMetaArrayForWordpress['automated_testing'] = true;
    }

    // Save post and return ID of newly created post for updating Categories, tags and Widgets
    $wpPostId = wp_insert_post($postArrayForWordpress);
    // Save the post meta data (Any field that's not post_)
    self::setPostMetadata($wpPostId, $postMetaArrayForWordpress);

    // XXX: Actions to take place __after__ the post is saved and require either the Post ID or TimberPost object

    // Attach Categories to Post
    Category::attachCategories($object->article->section, $postUrl, $wpPostId);

    // Attach
    $postTags = array();
    foreach($object->article->tags as $tag) {
      if ($tag->type !== 'System') {
        array_push($postTags, ucwords($tag->tag));
      }
    }
    wp_set_post_tags($wpPostId, $postTags);

    // Catch failure to create TimberPost object
    if (!$post = new TimberPost($wpPostId)) {
      self::notifyError('Unexpected exception where Mesh did not create/fetch a post');
    }

    // Create the ACF Widgets from DOM content
    $widgets = Widget::getWidgetsFromDom($postDom);
    Widget::setPostWidgets($post, $widgets, $postObject);

    // Store header image
    $show_header = self::setHeroImages($post, $postDom, $postObject);
    $postArrayForWordpress['header_display_hero_image'] = $show_header;

    // Envoke any actions hooked to the 'catfish_importer_post' tag
    do_action('catfish_importer_post', $post->ID);

    return $post;
  }

  /**
   * Set or update multiple post meta properties at once
   */
  protected static function setPostMetadata($postId, $fields) {
    foreach ($fields as $fieldName => $value) {
      self::setPostMetaProperty($postId, $fieldName, $value);
    }
  }

  /**
   * Create or update a post meta field
   */
  protected static function setPostMetaProperty($postId, $fieldName, $value = '') {
    if ( empty( $value ) OR ! $value ) {
        delete_post_meta( $postId, $fieldName );
    } elseif ( ! get_post_meta( $postId, $fieldName ) ) {
        add_post_meta( $postId, $fieldName, $value );
    } else {
        update_post_meta( $postId, $fieldName, $value );
    }
  }

  protected static function setAuthor($authorObject) {
    $user_id = User::checkUserByEmail($authorObject->emailAddress);
    if ($user_id == false) {
      $user_id = User::insertCatfishUser($authorObject);
    }
    return $user_id;
  }

  protected static function setArticleType($articleObject) {
    if(isset($articleObject->analyticsPageTypeDimension)) {
      return strtolower($articleObject->analyticsPageTypeDimension);
    }
    return 'article';
  }

  protected static function setHeroImages(TimberPost $post, $postDom, $postObject) {
    $show_header = true;
    $heroImageDom = $postDom->find('.slideshow__slide img,.gallery-overview__main-image img');

    $heroImageIds = [];
    foreach($heroImageDom as $index => $heroImageDom) {
      $heroImage = new stdClass();
      $heroImage->src = $heroImageDom->src;
      $heroImage->filename = substr($heroImage->src, strrpos($heroImage->src, '/') + 1);
      $heroImage->name = substr($heroImage->filename, 0, strrpos($heroImage->filename, '.'));
      $heroImage->extension = substr($heroImage->filename, strrpos($heroImage->filename, '.') + 1);
      $meshImage = new \Mesh\Image($heroImage->src);
      $heroImage->id = $meshImage->id;
      $heroImageIds[] = (string)$heroImage->id;
    }
    if (!count($heroImageIds)) {
      $show_header = false;
    }

    if ((!count($heroImageIds)) && (isset($postObject->images->widgets[0]->imageUrl))) {
      $url = $postObject->images->widgets[0]->imageUrl;
      $heroImage = new stdClass();
      $heroImage->src = $url;
      $heroImage->filename = substr($url, strrpos($url, '/') + 1);
      $heroImage->name = substr($heroImage->filename, 0, strrpos($heroImage->filename, '.'));
      $heroImage->extension = substr($heroImage->filename, strrpos($heroImage->filename, '.') + 1);
      $meshImage = new \Mesh\Image($heroImage->src);
      $heroImage->id = $meshImage->id;
      $heroImageIds[] = (string)$heroImage->id;
    }

    if (array_key_exists(0, $heroImageIds)) {
      update_post_meta($post->id, 'hero_images', $heroImageIds);
      update_post_meta($post->id, '_hero_images', 'article_basic_hero_images');
      set_post_thumbnail($post->id, $heroImageIds[0]);
    } else {
      $message = "$post->title has no hero images";
      self::notifyError($message);
    }
    return $show_header;
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
