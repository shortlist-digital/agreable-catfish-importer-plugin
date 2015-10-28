Feature: agreable-catfish-importer-plugin
  Test the Catfish Importer plugin

  Scenario: Fetch list of sections
    Given the sitemap index "http://www.stylist.co.uk/sitemap-index.xml"
    Then I should have a list of sections

  Scenario: Fetch articles from a section sitemap
    Given the section sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have a list of articles

  Scenario: Fetch a Harry Potter article
    Given the article "http://www.stylist.co.uk/books/beautiful-images-from-first-ever-illustrated-harry-potter-book-released"
    Then I should have an object of the article
    And the article has the headline "Beautiful images from first ever illustrated Harry Potter book released"
