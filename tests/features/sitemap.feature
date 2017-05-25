Feature: Sitemap
  Test the Catfish Importer plugin

  Scenario: Fetch list of categorys
    Given the sitemap index "http://www.stylist.co.uk/sitemap-index.xml"
    Then I should have a list of categories

  Scenario: Fetch posts from a category sitemap
    Given the category sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have a list of posts
