Feature: Sync
  Test the Catfish Importer sync

  Scenario: Sync just the last 2 posts of a single category
    Given I sync 2 most recent posts from the category sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have 2 imported "books" posts

  Scenario: Get the status of a category import
    Given I sync 2 most recent posts from the category sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have 2 imported "books" posts
    And I should have the category import status of 2 out of 100 imported
    And the most recently imported matches the two imported articles
