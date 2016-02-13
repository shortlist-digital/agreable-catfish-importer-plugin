<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \TimberPost;
use Sunra\PhpSimple\HtmlDomParser;

use AgreableCatfishImporterPlugin\Services\User;
use AgreableCatfishImporterPlugin\Services\Category;

class Post {
  public static function getPostFromUrl($postUrl) {
    $postJsonUrl = $postUrl . '.json';
    $postString = file_get_contents($postJsonUrl);

    if (!$object = json_decode($postString)) {
      throw new \Exception('Unable to retrieve JSON from URL ' . $postJsonUrl);
    }

    if (!isset($object->article)) {
      throw new \Exception('"Article" property does not exist in JSON');
    }

    $postObject = $object->article;
    $postDom = HtmlDomParser::str_get_html($object->content);
    $postReformatted = new stdClass();

    $meshPost = new \Mesh\Post($postObject->slug);
    $meshPost->set('post_title', $postObject->headline);

    $meshPost->set('article_type', self::setArticleType($object));

    if (isset($object->article->__author)) {
      $meshPost->set('post_author', self::setAuthor($object->article->__author));
    }

    Category::attachCategories($object->artecle->section, $postUrl, $meshPost->id);

    $postTags = array();
    foreach($object->article->tags as $tag) {
      array_push($postTags, $tag->tag);
    }
    wp_set_post_tags($meshPost->id, $postTags);


    $meshPost->set('short_headline', $postObject->shortHeadline, true);
    $meshPost->set('sell', $postObject->sell, true);

    $meshPost->set('catfish-importer_imported', true, true);

    // If automated testing, set some metadata
    if (isset($_SERVER['is-automated-testing'])) {
      $meshPost->set('automated_testing', true, true);
    }

    $meshPost->set('catfish-importer_url', $postUrl, true);
    $meshPost->set('catfish-importer_imported', true, true);
    $meshPost->set('catfish-importer_date-updated', time(), true);

    if (!$post = new TimberPost($meshPost->id)) {
      throw new \Exception('Unexpected exception where Mesh did not create/fetch a post');
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

    update_post_meta($post->id, 'hero_images', $heroImageIds);
    update_post_meta($post->id, '_hero_images', 'article_basic_hero_images');
    set_post_thumbnail($post->id, $heroImageIds[0]);
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
