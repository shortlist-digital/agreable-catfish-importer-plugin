<?php
namespace AgreableCatfishImporterPlugin\Services;

class Sync {
  public static function getCategories() {
    return Sitemap::getSectionsFromIndex('http://www.stylist.co.uk/sitemap-index.xml');
  }
}
