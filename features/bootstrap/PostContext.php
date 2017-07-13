<?php

require_once( __DIR__ . '/../../../../../wp/wp-load.php' );

use AgreableCatfishImporterPlugin\Services\Post;
use AgreableCatfishImporterPlugin\Services\Widget;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_Assert as Assert;

/**
 * Class PostContext
 */
class PostContext implements \Behat\Behat\Context\Context {
	/**
	 * @var \TimberPost
	 */
	private $post;

	/**
	 * NOTE: The FeatureContext clears all posts with the automated_testing data
	 * from the database on BeforeFeature and AfterFeature events.
	 */

	/**
	 * @Given /^the post "([^"]*)"$/
	 * @param $url
	 */
	public function thePost( $url ) {
		$this->post = Post::getPostFromUrl( $url );
	}

	/**
	 * @Then /^I should have an object of the post$/
	 */
	public function iShouldHaveAnObjectOfThePost() {
		Assert::assertInstanceOf( \TimberPost::class, $this->post );
	}

	/**
	 * @Given /^the post has the headline "([^"]*)"$/
	 * @param $headline
	 */
	public function thePostHasTheHeadline( $headline ) {
		Assert::assertEquals( $headline, $this->post->post_title );
	}

	/**
	 * @Given /^the post slug is "([^"]*)"$/
	 * @param $slug
	 */
	public function thePostSlugIs( $slug ) {
		Assert::assertEquals( $this->post->slug, $slug );
	}

	/**
	 * @Given /^the post has the property "([^"]*)" of "([^"]*)"$/
	 * @param $key
	 * @param $value
	 */
	public function thePostHasThePropertyOf( $key, $value ) {
		Assert::assertEquals( $value, $this->post->get_field( $key ) );
	}

	/**
	 * @Given /^the category slug "([^"]*)"$/
	 * @param $categorySlug
	 */
	public function theCategorySlug( $categorySlug ) {
		$category = Post::getCategory( $this->post );
		Assert::assertEquals( $categorySlug, $category->slug );
	}

	/**
	 * @Given /^the widgets "([^"]*)"$/
	 * @param $expectedWidgetsString
	 */
	public function theWidgets( $expectedWidgetsString ) {
		$widgets     = Widget::getPostWidgets( $this->post );
		$widgetNames = [];
		foreach ( $widgets as $widget ) {
			$widgetNames[] = $widget['acf_fc_layout'];
		}

		Assert::assertEquals( $expectedWidgetsString, implode( ',', $widgetNames ) );
	}

	/**
	 * @Given /^the paragraph widget at index (\d+):$/
	 * @param $index
	 * @param PyStringNode $string
	 */
	public function theParagraphWidgetAtIndex( $index, PyStringNode $string ) {
		$widget = Widget::getPostWidgetsFiltered( $this->post, 'paragraph', $index );
		Assert::assertNotNull( $widget );
		Assert::assertEquals( (string) $string, $widget['paragraph'] );
	}

	/**
	 * @Given /^the image filename is "([^"]*)" at index (\d+)$/
	 * @param $filename
	 * @param $index
	 */
	public function theImageFilenameIsAtIndex( $filename, $index ) {
		$widget = Widget::getPostWidgetsFiltered( $this->post, 'image', $index );

		Assert::assertNotNull( $widget );

		// Assert::markTestIncomplete('TODO: Mesh/Image.php assigns filename as MD5.');

		// Assert::assertEquals($widget['image']['filename'], $filename);
	}

	/**
	 * @Given /^the "([^"]*)" "([^"]*)" is "([^"]*)" at index (\d+)$/
	 * @param $widgetName
	 * @param $property
	 * @param $value
	 * @param $index
	 * @param bool $stringSearch
	 */
	public function theWidgetPropertyIsAtIndex( $widgetName, $property, $value, $index, $stringSearch = false ) {

		$widget = Widget::getPostWidgetsFiltered( $this->post, $widgetName, $index );

		Assert::assertNotNull( $widget );
		Assert::assertTrue( isset( $widget[ $property ] ) );

		if ( $stringSearch ) {
			Assert::assertContains( $value, $widget[ $property ] );
		} else {
			Assert::assertEquals( $value, $widget[ $property ] );
		}
	}

	/**
	 * @Given /^the "([^"]*)" "([^"]*)" contains "([^"]*)" at index (\d+)$/
	 * @param $widgetName
	 * @param $property
	 * @param $value
	 * @param $index
	 */
	public function theWidgetPropertyStringSearchIsAtIndex( $widgetName, $property, $value, $index ) {
		$this->theWidgetPropertyIsAtIndex( $widgetName, $property, $value, $index, true );
	}

	/**
	 * @Given /^the "([^"]*)" "([^"]*)" at index (\d+) is:$/
	 * @param $widgetName
	 * @param $property
	 * @param $index
	 * @param PyStringNode $value
	 */
	public function theWidgetPropertyMultilineIsAtIndex( $widgetName, $property, $index, PyStringNode $value ) {
		$this->theWidgetPropertyIsAtIndex( $widgetName, $property, $value, $index );
	}

	/**
	 * @Given /^the number of hero images is (\d+)$/
	 * @param $expectedHeroImageNumber
	 */
	public function theNumberOfHeroImagesIs( $expectedHeroImageNumber ) {
		$heroImages = $this->post->get_field( 'hero_images' );
		Assert::assertEquals( $expectedHeroImageNumber, count( $heroImages ) );
	}

	/**
	 * @Given /^there are (\d+) gallery images$/
	 * @param $expectedNumberOfGalleryImages
	 */
	public function thereAreGalleryImages( $expectedNumberOfGalleryImages ) {
		$widgets = $this->post->get_field( 'widgets' );
		foreach ( $widgets as $widget ) {
			if ( $widget['acf_fc_layout'] === 'gallery' ) {
				Assert::assertEquals( $expectedNumberOfGalleryImages, count( $widget['gallery_items'] ) );
			}
		}
	}

	/**
	 * @Given /^the post has import metadata$/
	 */
	public function thePostHasImportMetadata() {
		Assert::assertEquals( true, $this->post->get_field( 'catfish_importer_imported' ) );
		Assert::assertNotNull( $this->post->get_field( 'catfish_importer_date_updated' ) );
	}

	/**
	 * @Given /^gallery item (\d+) has title "([^"]*)"$/
	 * @param $gallery_index
	 * @param $title
	 */
	public function galleryItemHasTitle( $gallery_index, $title ) {
		$widget = $this->getGalleryWidgetFromPost( $this->post );

		Assert::assertNotNull( $widget );

		$gallery_item = $widget['gallery_items'][ $gallery_index - 1 ];
		Assert::assertEquals( $title, $gallery_item['title'] );
	}

	/**
	 * @Given /^gallery item (\d+) has caption:$/
	 * @param $gallery_index
	 * @param PyStringNode $caption
	 */
	public function galleryItemHasCaption( $gallery_index, PyStringNode $caption ) {
		$widget = $this->getGalleryWidgetFromPost( $this->post );

		Assert::assertNotNull( $widget );

		$gallery_item = $widget['gallery_items'][ $gallery_index - 1 ];
		Assert::assertEquals( (string) $caption, $gallery_item['caption'] );
	}

	/**
	 * @param $post
	 *
	 * @return mixed
	 */
	protected function getGalleryWidgetFromPost( $post ) {
		$widgets = $this->post->get_field( 'widgets' );
		foreach ( $widgets as $widget ) {
			if ( $widget['acf_fc_layout'] === 'gallery' ) {
				return $widget;
			}
		}
	}

	/**
	 * @Given /^the post has (\d+) "([^"]*)" widgets$/
	 * @param $count
	 * @param $widget_type
	 */
	public function thePostHasWidgets( $count, $widget_type ) {
		$widgets     = $this->post->get_field( 'widgets' );
		$image_count = 0;
		foreach ( $widgets as $key => $widget ) {
			if ( $widget['acf_fc_layout'] == $widget_type ) {
				$image_count = $image_count + 1;
			}
		}
		Assert::assertEquals( $count, $image_count );
	}

	/**
	 * @Given /^the post has (\d+) tags$/
	 * @param $count
	 */
	public function thePostHasTags( $count ) {
		$count = (int) $count;
		$id    = $this->post->id;
		Assert::assertEquals( $count, count( wp_get_post_tags( $id, [ 'fields' => 'ids' ] ) ) );
		Assert::assertEquals( $count, count( get_field( 'tags', $id, false ) ) );
	}

}
