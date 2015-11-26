<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \TimberPost;
use Sunra\PhpSimple\HtmlDomParser;

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
    $postDom = HtmlDomParser::file_get_html($postUrl);

    $postReformatted = new stdClass();

    $meshPost = new \Mesh\Post($postObject->headline);
    $meshCategory = new \Mesh\Term($postObject->section->slug, 'category');
    wp_set_post_categories($meshPost->id, $meshCategory->id['term_id']);

    $meshPost->set('short_headline', $postObject->shortHeadline, true);
    $meshPost->set('sell', $postObject->sell, true);
    $meshPost->set('catfish-importer_imported', true, true);
    $meshPost->set('catfish-importer_date-updated', time(), true);


    if (!$post = new TimberPost($meshPost->id)) {
      throw new \Exception('Unexpected exception where Mesh did not create/fetch a post');
    }

    self::setHeroImages($post, $postDom);

    $widgets = Widget::getWidgetsFromDom($postDom);
    Widget::setPostWidgets($post, $widgets);

    return $post;
  }

  protected static function setHeroImages(TimberPost $post, $postDom) {
    $heroImageDom = $postDom->find('.slideshow__slide img');

    $heroImageIds = [];
    foreach($heroImageDom as $index => $heroImageDom) {
      $heroImage = new stdClass();
      $heroImage->src = $heroImageDom->src;
      $heroImage->filename = substr($heroImage->src, strrpos($heroImage->src, '/') + 1);
      $heroImage->name = substr($heroImage->filename, 0, strrpos($heroImage->filename, '.'));
      $heroImage->extension = substr($heroImage->filename, strrpos($heroImage->filename, '.') + 1);
      $meshImage = new \Mesh\Image($heroImage->src);
      $heroImage->id = $meshImage->id;
      $heroImageIds[] = $heroImage->id;

    }

    update_post_meta($post->id, 'hero_images', serialize($heroImageIds));
    update_post_meta($post->id, '_hero_images', 'post_basic_hero_images');
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
