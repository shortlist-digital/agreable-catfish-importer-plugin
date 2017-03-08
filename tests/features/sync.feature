Feature: Sync
  Test the Catfish Importer sync

  Scenario: Queue a single post for import
    Given I purge the queue
    And I push the post "http://www.shortlist.com/food-drink/the-chicken-connoisseur-is-in-new-york-rating-jay-z-old-chicken-spot-asap-bari-vlone" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID

  Scenario: Action a single post import
    Given I pull an item from the queue and run it
    Then I should have imported the "the-chicken-connoisseur-is-in-new-york-rating-jay-z-old-chicken-spot-asap-bari-vlone" post
    And I delete all automated_testing posts

  Scenario: Queue an entire category of posts for import
    Given I push all posts from the category sitemap "http://www.shortlist.com/sitemap/food-drink.xml" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID
    Then I purge the queue


  Scenario: Sync a post from url
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"update"}}'
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post

  Scenario: Push category import to queue
    Given I process the queue action json '{"job":"importCategory","data":{"url":"http:\/\/www.shortlist.com\/sitemap\/48-hours-to.xml","onExistAction":"update"}}'
    Then I should have an array of queue IDs


  Scenario: Get the full import status
    Given I retrieve the full import status
    Then I should see a couple of imported out of at least 10000

  Scenario: Get a category import status
    Given I retrieve the "news" category import status
    Then I should see a couple of imported out of at least 5
