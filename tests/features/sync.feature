Feature: Sync
  Test the Catfish Importer sync

  Scenario: Queue a single post for import
    Given I push 2 most recent posts from the category sitemap "http://www.shortlist.com/sitemap/food-drink.xml" to the queue
    And I specify the update method as "delete-insert"
    Then I should have 2 items in the queue

  Scenario: Queue an entire category of posts for import
    Given I push all posts from the category sitemap "http://www.shortlist.com/sitemap/food-drink.xml" to the queue
    Then I should have multiple items in the queue

  Scenario: Sync just the last 2 posts of a single category
    Given I sync 2 most recent posts from the category sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have 2 imported "books" posts

  Scenario: Get the status of a category import
    Given I sync 2 most recent posts from the category sitemap "http://www.stylist.co.uk/sitemap/ad-section-1.xml"
    Then I should have 2 imported "ad-section-1" posts
    And I should have the category import status of 2 out of 3 imported

  Scenario: Get the full import status
    Given I retrive the full import status
    Then I should see a couple of imported out of at least 10000
