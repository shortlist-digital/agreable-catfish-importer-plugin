<?php
namespace AgreableCatfishImporterPlugin\Services;

use Sabre;
use Sabre\Xml\Reader;

class SitemapParser {

  function __construct() {
    $this->sitemap_url = get_field('catfish_website_url', 'option')."sitemap-index.xml";
    $this->sitemap_xml = file_get_contents($this->sitemap_url);
    $this->service = new \Sabre\Xml\Service();
    $this->do_mapping();
  }

  private function do_mapping() {
    $this->service->elementMap = [
      '{http://www.sitemaps.org/schemas/sitemap/0.9}sitemapindex' => function(Reader $reader) {
        return Sabre\Xml\Deserializer\repeatingElements($reader, '{http://www.sitemaps.org/schemas/sitemap/0.9}sitemap');
      },
      '{http://www.sitemaps.org/schemas/sitemap/0.9}urlset' => function(Reader $reader) {
        return Sabre\Xml\Deserializer\repeatingElements($reader, '{http://www.sitemaps.org/schemas/sitemap/0.9}url');
      }
    ];
  }

  public function filter_node_value(&$node) {
    return $node = $node[0]['value'];
  }

  public function get_section_posts($url) {
    $section_xml = file_get_contents($url);
    $loc_nodes = $this->service->parse($section_xml);
    array_walk($loc_nodes, array($this, 'filter_node_value'));
    return $loc_nodes;
  }

  public function get_sections() {
    $loc_nodes = $this->service->parse($this->sitemap_xml);
    array_walk($loc_nodes, array($this, 'filter_node_value'));
    return $loc_nodes;
  }

  public function get_all_posts_sorted() {
    $posts = array();
    $sections = $this->get_sections();
    foreach($sections as  $section) {
      $posts[$section] = $this->get_section_posts($section);
    }
    return $posts;
  }

  public function get_all_posts() {
    $posts = array();
    $sections = $this->get_sections();
    foreach($sections as  $section) {
      $section_posts = $this->get_section_posts($section);
      foreach($section_posts as $post) {
        array_push($posts, $post);
      }
    }
    return $posts;
  }

}
