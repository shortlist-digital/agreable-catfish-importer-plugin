Feature: agreable-catfish-importer-plugin
  Test the Catfish Importer plugin

  Scenario: Fetch list of sections
    Given the sitemap index "http://www.stylist.co.uk/sitemap-index.xml"
    Then I should have a list of sections

  Scenario: Fetch articles from a section sitemap
    Given the section sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have a list of articles