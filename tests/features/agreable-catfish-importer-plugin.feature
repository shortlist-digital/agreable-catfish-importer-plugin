Feature: agreable-catfish-importer-plugin
  Test the Catfish Importer plugin

  Scenario: Fetch list of sections
    Given the sitemap index "http://www.stylist.co.uk/sitemap-index.xml"
    Then I should have a list of sections

  Scenario: Fetch articles from a section sitemap
    Given the section sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have a list of articles

  Scenario: Fetch the all widgets article
    Given the article "http://www.stylist.co.uk/test-tech/all-widgets-test?previewId=46kzaa0"
    Then I should have an object of the article
    And the article has the headline "All widgets test"
    And the article has the property "short_headline" of "All widgets test (short headline)"
    And the category slug "test-tech"
    And the widgets "paragraph,image,image,image,video,video"
    And the image filename is "wedding-furs.jpg" at index 0
    And the paragraph widget at index 0:
      """
<p>This is my HTML widget.&nbsp;</p>
<p>This is <strong>bold</strong>.</p>
<p>This is <em>emphasised</em>.</p>
<p>This is a <a href="http://google.com/" target="_blank">link</a>.</p>

      """
    And the "image" "width" is "full" at index 0
    And the "image" "position" is "center" at index 0
    And the "video" "width" is "full" at index 0
    And the "video" "position" is "center" at index 0
    And the "video" "url" is "//player.vimeo.com/video/142546902" at index 1
    And the "video" "width" is "medium" at index 1
    And the "video" "position" is "left" at index 1
    And there are 2 hero images
