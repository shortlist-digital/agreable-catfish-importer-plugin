<?php

namespace AgreableCatfishImporterPlugin\Services\Widgets;

use AgreableCatfishImporterPlugin\Services\Post;
use Sunra\PhpSimple\HtmlDomParser;

class Html {
	public static function checkIfValidParagraph( $html_string ) {
		$allowable_tags  = '<a><b><i><br><em><sup><sub><strong><p><h3><ul><ol><li><span><center>';
		$stripped_string = strip_tags( $html_string, $allowable_tags );
		$test            = ( $html_string == $stripped_string );
		if ( ctype_space( strip_tags( html_entity_decode( $html_string, ENT_HTML5, 'iso-8859-1' ) ) ) ) {
			return false;
		}

		return $test;
	}

	public static function getFromWidgetDom( $widgetDom ) {
		// Remove the <div class="legacy-custom-html"/> that Clock wrap around the content
		if ( isset( $widgetDom->find( '.legacy-custom-html' )[0] ) ) {
			$widgetDom = $widgetDom->find( '.legacy-custom-html' )[0];
		}

		if ( self::checkIfValidParagraph( $widgetDom->innertext ) ) {
			// Return paragraph only posts as a single paragraphy widget
			return Paragraph::getFromWidgetDom( $widgetDom );
		} else {
			// Break up mixed content articles into separate widgets
			// die(var_dump('array_filter(self::breakIntoWidgets($widgetDom))', array_filter(self::breakIntoWidgets($widgetDom))));
			return array_filter( self::breakIntoWidgets( $widgetDom ) );
		}
	}

	public static function breakIntoWidgets( $widgetDom ) {

		$widgets = [];
		// Loop through all DOM nodes to create widgets from them

		foreach ( $widgetDom->find( '*' ) as $index => $node ) {
			/**
			 * @var $node \simplehtmldom_1_5\simple_html_dom
			 */
			// Check if this DOM node is a valid paragraph widget
			if ( self::checkIfValidParagraph( $node->outertext ) ) {
				// Remove blank <p>&nbsp;</p> paragraphy
				$clean_paragraph = str_replace( "<p>&nbsp;</p>", "", $node->outertext );

				$paragraphDom = HtmlDomParser::str_get_html( $clean_paragraph );
				array_push( $widgets, Paragraph::getFromWidgetDom( $paragraphDom ) );
			} elseif ( $node->tag == 'h2' ) {
				array_push( $widgets, Heading::getFromWidgetDom( $node ) );
				continue;
			} // Store embeddible content as embed widgets
			elseif ( ( $embedWidgets = Embed::getWidgetsFromDom( $node ) ) ) {

				// Push all embed widgets into the widget array
				foreach ( $embedWidgets as $widget ) {
					array_push( $widgets, $widget );
				}

				continue;
			} elseif ( ! self::checkIfEmbedScriptTag( $node->outertext ) ) {

				$html = new \stdClass();

				$html->type = 'html';
				$html->html = $node->outertext;
				array_push( $widgets, $html );

			} elseif ( $node->tag != 'script' ) {

				throw new \Exception( 'undefined widget exception ' . json_encode( $node ) . ' while processing: ' , 500 );
			}

		}
		//TODO: Check why is that happening. Probably wasn't there before
		$widgets = array_filter( $widgets, function ( $w ) {
			return is_object( $w );
		} );

		// Merge adjacent widgets of the same type together
		foreach ( $widgets as $index => $widget ) {
			if ( $index == 0 ) {
				continue;
			}
			$prev = $widgets[ $index - 1 ];
			if ( ( $prev->type == 'html' ) && ( $widget->type == 'html' ) ) {
				$widget->html = $prev->html . $widget->html;
				unset( $widgets[ $index - 1 ] );
			}
			if ( ( $prev->type == 'paragraph' ) && ( $widget->type == 'paragraph' ) ) {
				$widget->paragraph = $prev->paragraph . $widget->paragraph;
				unset( $widgets[ $index - 1 ] );
			}

		}
		$widgets = array_values( $widgets );

		return count( $widgets ) ? $widgets : false;
	}

	/**
	 * Check if the DOM element is a excess script tag from a social embed string
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public static function checkIfEmbedScriptTag( $string ) {
		$whitelist = array(
			'platform.twitter.com',
			'platform.instagram.com'
		);
		foreach ( $whitelist as $check ) {
			if ( preg_match( "/$check/", $string ) ) {
				return true;
			}
		}

		return false;
	}
}
