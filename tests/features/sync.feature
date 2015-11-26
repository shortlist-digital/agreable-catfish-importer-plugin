Feature: Sync
  Test the Catfish Importer sync

  Scenario: Sync just the last 2 posts of a single category
    Given I sync 2 most recent posts from the category sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have 2 imported "books" posts