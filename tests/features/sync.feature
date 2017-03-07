Feature: Sync
  Test the Catfish Importer sync

  Scenario: Queue a single post for import
    Given I purge the queue
    And I push the post "http://www.shortlist.com/food-drink/the-definitive-power-ranking-of-all-the-stuff-you-can-put-on-chips" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID

  Scenario: Action a single post import
    Given I pull an item from the queue
    Then I should run the queue function without Exception

  Scenario: Queue an entire category of posts for import
    Given I push all posts from the category sitemap "http://www.shortlist.com/sitemap/food-drink.xml" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID
    Then I purge the queue



  Scenario: Sync a post from url
    Given I have the queue action json
    """
    {"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/food-drink\/michelin-star-restaurants-odd-unusual-world-uk-guide-food","onExistAction":"update"}}
    """
    Then I should have imported the "michelin-star-restaurants-odd-unusual-world-uk-guide-food" post

  Scenario: Push category import to queue
    Given I have the queue action json
    """
    {"job":"importCategory","data":{"url":"http:\/\/www.shortlist.com\/sitemap\/food-drink.xml","onExistAction":"update"}}
    """
    Then I should have a list of urls



  Scenario: Get the full import status
    Given I retrive the full import status
    Then I should see a couple of imported out of at least 10000

    Scenario: Get a category import status
      Given I retrive the "news" category import status
      Then I should see a several of imported out of at least 100
