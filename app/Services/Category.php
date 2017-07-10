<?php

namespace AgreableCatfishImporterPlugin\Services;

class Category {

	public static function attachCategories( $sectionObject, $postUrl, $postId ) {

		if ( self::postIsInRootCategory( $sectionObject->fullUrlPath ) ) {
			//Do single category
			$catId = self::createRootCategory( $sectionObject->name, $sectionObject->slug );
			wp_set_post_categories( $postId, $catId );
			update_field( 'basic_category', $catId, $postId );
		} else {
			// get both categories
			$parentSlug = self::getParentSlug( $sectionObject->fullUrlPath );
			$parentName = self::getCatfishCategoryNameBySlug( $parentSlug, $postUrl );
			$parentId   = self::createRootCategory( $parentName, $parentSlug );
			$catId      = self::createChildCategory( $sectionObject->name, $sectionObject->slug, $parentId );
			wp_set_post_categories( $postId, $catId );
			update_field( 'basic_category', $catId, $postId );
		}
	}

	public static function createRootCategory( $name, $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term ) {
			return $term->term_id;
		} else {
			$category = wp_insert_term(
				$name, // the term
				'category', // the taxonomy
				array(
					'slug' => $slug
				)
			);

			return $category['term_id'];
		}
	}

	public static function getCatfishCategoryNameBySlug( $slug, $postUrl ) {
		$data          = explode( $slug, $postUrl, 2 );
		$base_url      = $data[0];
		$parent_object   = Fetch::json( $base_url . $slug . ".json" );

		return $parent_object->section->name;
	}

	public static function getParentSlug( $fullUrlPath ) {
		$fullUrlPath    = ltrim( $fullUrlPath, "/" );
		$category_array = explode( "/", $fullUrlPath );

		return $category_array[0];
	}

	public static function postIsInRootCategory( $fullUrlPath ) {
		$fullUrlPath    = ltrim( $fullUrlPath, "/" );
		$category_array = explode( "/", $fullUrlPath );

		return ( count( $category_array ) < 2 );
	}

	public static function createChildCategory( $name, $slug, $parent_id ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term ) {
			$category = wp_update_term(
				$term->term_id, // the term
				'category', // the taxonomy
				array(
					'slug'   => $slug,
					'parent' => $parent_id
				)
			);

			return $category['term_id'];
		} else {
			$category = wp_insert_term(
				$name, // the term
				'category', // the taxonomy
				array(
					'slug'   => $slug,
					'parent' => $parent_id
				)
			);
			if ( is_wp_error( $category ) ) {
				$error_string = $category->get_error_message();
				echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
			}

			return $category['term_id'];
		}

	}
}
