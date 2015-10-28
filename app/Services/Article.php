<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \TimberPost;

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

    $articleReformatted = new stdClass();

    $meshArticle = new \Mesh\Post($articleObject->headline);
    $meshCategory = new \Mesh\Term($articleObject->section->slug, 'category');
    wp_set_post_categories($meshArticle->id, $meshCategory->id['term_id']);

    $meshArticle->set('short_headline', $articleObject->shortHeadline);

    if (!$post = new TimberPost($meshArticle->id)) {
      throw new \Exception('Unexpected exception where Mesh did not create/fetch a post');
    }

    $widgets = Widget::getWidgetsFromUrl($articleUrl);
    Widget::setPostWidgets($post, $widgets);

    return $post;
  }


  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }
}
