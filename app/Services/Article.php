<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \TimberPost;
use Sunra\PhpSimple\HtmlDomParser;

class Article {
  public static function getArticleFromUrl($articleUrl) {
    $articleJsonUrl = $articleUrl . '.json';
    $articleString = file_get_contents($articleJsonUrl);

    if (!$object = json_decode($articleString)) {
      throw new \Exception('Unable to retrieve JSON from URL ' . $articleJsonUrl);
    }

    if (!isset($object->article)) {
      throw new \Exception('Article property does not exist in JSON');
    }
    $articleObject = $object->article;
    $articleDom = HtmlDomParser::file_get_html($articleUrl);

    $articleReformatted = new stdClass();

    $meshArticle = new \Mesh\Post($articleObject->headline);
    $meshCategory = new \Mesh\Term($articleObject->section->slug, 'category');
    wp_set_post_categories($meshArticle->id, $meshCategory->id['term_id']);

    $meshArticle->set('short_headline', $articleObject->shortHeadline, true);
    $meshArticle->set('sell', $articleObject->sell, true);
    $meshArticle->set('catfish-importer_imported', true, true);
    $meshArticle->set('catfish-importer_date-updated', time(), true);


    if (!$post = new TimberPost($meshArticle->id)) {
      throw new \Exception('Unexpected exception where Mesh did not create/fetch a post');
    }

    self::setHeroImages($post, $articleDom);

    $widgets = Widget::getWidgetsFromDom($articleDom);
    Widget::setPostWidgets($post, $widgets);

    return $post;
  }

  protected static function setHeroImages(TimberPost $post, $articleDom) {
    $heroImageDom = $articleDom->find('.slideshow__slide img');

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
    update_post_meta($post->id, '_hero_images', 'article_basic_hero_images');
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
