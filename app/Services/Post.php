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
      $fail = true;
    }

    if (!isset($object->article)) {
      self::notifyError('"Article" property does not exist in JSON, might be a full page embed or microsite');
      $fail = true;
    }

    if ($fail) {
      return false;
    }

    $postObject = $object->article;
    $postDom = HtmlDomParser::str_get_html($object->content);
    $postReformatted = new stdClass();

    $meshPost = new \Mesh\Post($postObject->slug);
    $meshPost->set('catfish_importer_url', $postUrl, true);
    $meshPost->set('catfish_importer_imported', true, true);
    $meshPost->set('catfish_importer_date_updated', time(), true);
    $meshPost->set('post_title', $postObject->headline);
    $meshPost->set('header_type', 'standard-hero');
    $meshPost->set('header_display_hero_image', true);
    $meshPost->set('header_display_headline', true);
    $meshPost->set('header_display_sell', true);

    // Set post published date
    $displayDate = strtotime($postObject->displayDate);
    $displayDate = date('o\-m\-d G\:i\:s', $displayDate);

    wp_update_post(array(
      'ID' => $meshPost->id,
      'post_date' => $displayDate,
      'post_date_gmt' => $displayDate,
      'post_modified' => $displayDate,
      'post_modified_gmt' => $displayDate
    ));

    $meshPost->set('article_type', self::setArticleType($object));

    if (isset($object->article->__author)) {
      $meshPost->set('post_author', self::setAuthor($object->article->__author));
    } else {
      $default_author = get_field('catfish_default_author', 'option');
      $author = $default_author['ID'];
      $meshPost->set('post_author', $author, true);
    }

    Category::attachCategories($object->article->section, $postUrl, $meshPost->id);

    $postTags = array();
    foreach($object->article->tags as $tag) {
      if ($tag->type !== 'System') {
        array_push($postTags, ucwords($tag->tag));
      }
    }
    wp_set_post_tags($meshPost->id, $postTags);

    $sell = !empty($postObject->sell) ? $postObject->sell : $postObject->headline;

    $meshPost->set('short_headline', $postObject->shortHeadline, true);
    $meshPost->set('sell', $sell, true);

    $meshPost->set('catfish_importer_imported', true, true);

    // If automated testing, set some metadata
    if (isset($_SERVER['is-automated-testing'])) {
      $meshPost->set('automated_testing', true, true);
    }


    if (!$post = new TimberPost($meshPost->id)) {
      self::notifyError('Unexpected exception where Mesh did not create/fetch a post');
    }

    self::setHeroImages($post, $postDom);

    $widgets = Widget::getWidgetsFromDom($postDom);
    Widget::setPostWidgets($post, $widgets, $postObject);

    return $post;
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

  protected static function setHeroImages(TimberPost $post, $postDom) {
    $heroImageDom = $postDom->find('.slideshow__slide img,.gallery-overview__main-image img,.gallery-overview img');

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

    if (array_key_exists(0, $heroImageIds)) {
      update_post_meta($post->id, 'hero_images', $heroImageIds);
      update_post_meta($post->id, '_hero_images', 'article_basic_hero_images');
      set_post_thumbnail($post->id, $heroImageIds[0]);
    } else {
      $message = "$post->title has no hero images";
      self::notifyError($message);
    }
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
