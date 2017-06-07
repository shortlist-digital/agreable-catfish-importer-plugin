Feature: Sync
  Test the Catfish Importer sync



  Scenario: Delete all automated testing posts
    Given I delete all automated_testing posts
    Then I should have no automated_testing posts


  Scenario: Escape an API url path
    Given I escape the API url path "http://www.shortlist.com/entertainment/films/15-things-you-probably-didnt-know-about-Léon.json"
    Then The returned url should be valid


  Scenario: Queue a single post for import
    Given I purge the queue
    And I delete all automated_testing posts
    And I push the post "http://www.shortlist.com/food-drink/the-chicken-connoisseur-is-in-new-york-rating-jay-z-old-chicken-spot-asap-bari-vlone" with the update method as "delete-insert" to the queue
    Then I should have a valid queue ID

  Scenario: Action a single post import
    And I pull an item from the queue and run it
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
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/entertainment\/sport\/blind-footballer-scores-incredible-messi-style-goal-in-paralympics","onExistAction":"update"}}'
    Then I should have imported the "blind-footballer-scores-incredible-messi-style-goal-in-paralympics" post
    And I should have 'updated' the post in the last 10 seconds

  Scenario: Sync a post from url using delete-insert
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/entertainment\/films\/some-genius-has-edited-breaking-bad-movie-into-a-movie","onExistAction":"delete-insert"}}'
    Then I should have imported the "some-genius-has-edited-breaking-bad-movie-into-a-movie" post
    And I should have 'created' the post in the last 10 seconds

  Scenario: Sync a post from url using skip
    Given I process the queue action json '{"job":"importUrl","data":{"url":"http:\/\/www.shortlist.com\/news\/professor-allen-lichtman-who-predicted-every-us-election-since-1984-thinks-donald-trump-impeachment","onExistAction":"skip"}}'
    Then I should have imported the "professor-allen-lichtman-who-predicted-every-us-election-since-1984-thinks-donald-trump-impeachment" post
    And I should have not updated the post created or updated time


  Scenario: Get the full import status
    Given I retrieve the full import status
    Then I should see 100 imported out of at least 10000

  Scenario: Get a category import status
    Given I retrieve the "http://www.shortlist.com/sitemap/news.xml" category import status
    Then I should see 3 imported out of at least 5


  Scenario: Run the updated content scan cron function
    Given I run the cron function
    Then I should have updated the cron last run time
