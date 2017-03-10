Feature: Sync
  Test the Catfish Importer sync

  Scenario: Delete all automated testing posts
    Given I delete all automated_testing posts
    Then I should have no automated_testing posts


  Scenario: Queue a single post for import
    Given I purge the queue
    And I delete all automated_testing posts
    And I push the post "http://www.shortlist.com/food-drink/the-chicken-connoisseur-is-in-new-york-rating-jay-z-old-chicken-spot-asap-bari-vlone" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID

  Scenario: Action a single post import
    Given I pull an item from the queue and run it
    Then I should have imported the "the-chicken-connoisseur-is-in-new-york-rating-jay-z-old-chicken-spot-asap-bari-vlone" post
    And I delete all automated_testing posts

  Scenario: Queue an entire category of posts for import
    Given I push all posts from the category sitemap "http://www.shortlist.com/sitemap/food-drink.xml" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID


  Scenario: Sync a new post from url
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"update"}}'
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post

  Scenario: Push category import to queue
    Given I process the queue action json '{"job":"importCategory","data":{"url":"http:\/\/www.shortlist.com\/sitemap\/entertainment\/48-hours-to.xml","onExistAction":"update"}}'
    Then I should have an array of queue IDs


  Scenario: Sync a post from url using update
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"update"}}'
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post
    And I should have updated the post updated time

  Scenario: Sync a post from url using delete-insert
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"delete-insert"}}'
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post
    And I should have updated the post created time

  Scenario: Sync a post from url using skip
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"skip"}}'
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post
    And I should have the identical the post created and updated time


  Scenario: Get the full import status
    Given I retrieve the full import status
    Then I should see 100 imported out of at least 10000

  Scenario: Get a category import status
    Given I retrieve the "http://www.shortlist.com/sitemap/news.xml" category import status
    Then I should see 3 imported out of at least 5
