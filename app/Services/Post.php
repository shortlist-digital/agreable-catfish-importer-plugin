<?php
namespace AgreableCatfishImporterPlugin\Services;

use \stdClass;
use \WP_Post;
use \WP_Query;
use \WP_CLI;
use \TimberPost;
use Sunra\PhpSimple\HtmlDomParser;

use AgreableCatfishImporterPlugin\Services\User;
use AgreableCatfishImporterPlugin\Services\Category;

use Exception;

class Post {

  /**
   * Get single post from Clock URL and import into the Pages CMS
   *
   */
  public static function getPostFromUrl($postUrl, $onExistAction = 'skip') {
    $fail = false;
    $postJsonUrl = $postUrl . '.json';
    try {
      $postString = file_get_contents($postJsonUrl);
    } catch (Exception $e) {
      throw new Exception('Unable to retrieve JSON from URL ' . $postJsonUrl);
    }

    if (!$object = json_decode($postString)) {
      throw new Exception('Unable to retrieve JSON from URL ' . $postJsonUrl);
    }

    if (!isset($object->article)) {
      throw new Exception('"Article" property does not exist in JSON, might be a full page embed or microsite');
    }

    // XXX: Create master post array to save into Wordpress

    // Create an empty wordpress post array to build up over the course of the
    // function and to insert or update using wp_insert_post or wp_update_post
    $postArrayForWordpress = array();

    $postObject = $object->article; // The article in object from as retrieved from Clock CMS API
    $postDom = HtmlDomParser::str_get_html($object->content); // A parsed object of the post content to be split into ACF widgets as a later point

    // Check if article exists and handle onExistAction
    $existingPost = self::getPostsWithSlug($postObject->slug);

    // Mark if the post already exists
    // This is used later on to decide if we should update or insert the post
    if(empty($existingPost)) {

      // If there's no existing post go ahead and import it fresh
      // Make $existingPost clearer to use in future if statements by setting as false
      $existingPost = false;

    } else {

      // If the post exists respect the onExistAction attribute
      switch ($onExistAction) {
        case 'update':

          // Update the existing post in place

          // Use the post object as the base of the post to update
          // Transmute object to array for the wp_update_post functon
          $postArrayForWordpress = (array) $existingPost[0];

          break;
        case 'delete-insert':

          // Delete existing post and add a new one below
          try {
            wp_delete_post($existingPost[0]->ID);
          } catch (Exception $e) {
            throw new Exception("Error deleting original post.");
          }

          break;
        case 'skip':
        default:

          // Default, skip any post that already exists
          // return the existing post object as is
          return $existingPost[0];

          break;
      }

    }

    // Set post published date
    $displayDate = strtotime($postObject->displayDate);
    $displayDate = date('o\-m\-d G\:i\:s', $displayDate);

    // If no sell exists on this post then create it from the headline
    $sell = empty($postObject->sell) ? $postObject->headline : $postObject->sell;

    // Create the base array for the new Wordpress post or merge with existing post if updating
    $postArrayForWordpress = array_merge(array(
      'post_name' => $postObject->slug,
      'post_title' => $postObject->headline,
      'post_date' => $displayDate,
      'post_date_gmt' => $displayDate,
      'post_modified' => $displayDate,
      'post_modified_gmt' => $displayDate,
      'post_status' => 'publish' // Publish the post on import
    ), $postArrayForWordpress); // Clock data from api take presidence over local data from Wordpress

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

    // Log the created time if this is the first time this post was imported
    if($existingPost == false || $existingPost && $onExistAction == 'delete-insert') {
      $postMetaArrayForWordpress['catfish_importer_date_created'] = time();
    }

    // If automated testing, set the automated_testing meta field
    if (isset($_SERVER['is-automated-testing'])) {

      $postMetaArrayForWordpress['automated_testing'] = true;

      // Do not mark delete-insert or update posts as automated_testing if they
      // weren't already marked automated_testing. This prevents tests from
      // deleteing existing posts
      if($onExistAction == 'delete-insert' || $onExistAction == 'update') {
        unset($postMetaArrayForWordpress['automated_testing']);
      }
    }

    // Insert or update the post
    if($existingPost && $onExistAction == 'update') {
      // Save post and return ID of newly created post for updating Categories,
      // tags and Widgets
      $wpPostId = wp_update_post($postArrayForWordpress);
    } else {
      // Save post and return ID of newly created post for updating Categories,
      //  tags and Widgets
      $wpPostId = wp_insert_post($postArrayForWordpress);
    }

    // Save the post meta data (Any field that's not post_)
    self::setPostMetadata($wpPostId, $postMetaArrayForWordpress);

    // XXX: Actions to take place __after__ the post is saved and require either the Post ID or TimberPost object

    // Attach Categories to Post
    Category::attachCategories($object->article->section, $postUrl, $wpPostId);

    // Add tags to post
    $postTags = array();
    foreach($object->article->tags as $tag) {
      if ($tag->type !== 'System') {
        array_push($postTags, ucwords($tag->tag));
      }
    }
    wp_set_post_tags($wpPostId, $postTags);

    // Catch failure to create TimberPost object
    if (!$post = new TimberPost($wpPostId)) {
      throw new Exception('Unexpected exception where Mesh did not create/fetch a post');
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
      throw new Exception($message);
    }
    return $show_header;
  }

  public static function getCategory(TimberPost $post) {
    $postCategories = wp_get_post_categories($post->id);
    return get_category($postCategories[0]);
  }

  /**
   * Get and return posts with matching slug
   */
  public static function getPostsWithSlug($slug) {
    $args = array(
      'name' => $slug,
      // 'post_name' => $slug,
      'post_type' => 'post',
      'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash')
    );
    $posts = get_posts($args);
    return $posts;
  }

  /**
   * Delete all post with the automated_testing metadata
   */
  public static function deleteAllAutomatedTestingPosts($cli = false) {
    $query = new WP_Query([
      'post_type' => 'post',
      'meta_key'  => 'automated_testing',
      'meta_value'  => true,
      'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
      'posts_per_page' => -1 // Return all posts at once.
    ]);

    if ( $query->have_posts() ) {
      $posts = $query->get_posts();
      foreach($posts as $post) {
        // TODO Delete all images associated with this post.

        if($post->ID) {
          if($cli) {
            WP_CLI::success('Deleting post ' . $post->ID);
          }
          wp_delete_post($post->ID, true);
        }
      }
    }

  }
}
