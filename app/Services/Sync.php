<?php
namespace AgreableCatfishImporterPlugin\Services;

class Sync {
  public static function getCategories() {
    return Sitemap::getCategoriesFromIndex('http://www.stylist.co.uk/sitemap-index.xml');
  }
}
