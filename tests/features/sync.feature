Feature: Sync
  Test the Catfish Importer sync

  Scenario: Sync just the last 10 posts of a single category
    Given I sync 10 most recent posts from the category "life"
    Then I should have 10 imported "life" posts