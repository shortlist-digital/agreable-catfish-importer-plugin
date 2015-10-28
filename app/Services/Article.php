<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;

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
    $meshArticle->set('short_headline', $articleObject->shortHeadline);

    if (!$post = get_post($meshArticle->id)) {
      throw new \Exception('Unexpected exception where Mesh did not create/fetch a post');
    }

    return $post;
  }
}
