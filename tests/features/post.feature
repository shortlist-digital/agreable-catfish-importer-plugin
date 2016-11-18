Feature: Post
  Test the importing an post

  Scenario: Fetch the all widgets post
    Given the post "http://www.stylist.co.uk/test-tech/all-widgets-test?previewId=46kzaa0"
    Then I should have an object of the post
    And the post has the headline "All widgets test wow"
    And the post slug is "all-widgets-test"
    And the post has the property "short_headline" of "All widgets test (short headline)"
    And the post has the property "sell" of "This is the sell"
    And the category slug "test-tech"
    And the widgets "paragraph,image,image,image,horizontal-rule,embed,embed,embed,embed,embed"
    And the paragraph widget at index 0:
      """
<p>This is my HTML widget.&nbsp;</p>
<p>I would use the word &quot;Wow&quot; to say how good it is. Here&#39;s some characters:&nbsp;&pound;&pound;!@$%)(*&amp;^%</p>
<p>This is <strong>bold</strong>.</p>
<p>This is <em>emphasised</em>.</p>
<p>This is a <a href="http://google.com/" target="_blank">link</a>.</p>

      """
    And the "image" "width" is "full" at index 0
    And the "image" "position" is "center" at index 0
    And the "image" "caption" at index 0 is:
      """
<p>This is the caption text</p>

      """
    And the image filename is "wedding-furs.jpg" at index 0
    And the "embed" "width" is "medium" at index 0
    And the "embed" "embed" contains "player.vimeo.com/video/142546902" at index 1
    And the "embed" "width" is "medium" at index 1
    And the "embed" "embed" contains "https://www.facebook.com/StylistMagazine/videos/10155916444819572/" at index 2
    And the number of hero images is 2
    And the post has import metadata

  Scenario: Fetch a gallery-type article
    Given the post "http://www.stylist.co.uk/books/the-best-new-books-novels-of-november-jean-lucey-pratt-number-11-public-library"
    Then I should have an object of the post
    And the post has the headline "The best new books of November to cosy up to"
    And the number of hero images is 1
    And the widgets "paragraph,gallery"
    And there are 10 gallery images

  Scenario: Correctly encode the paragraph widget of an article
    Given the post "http://www.stylist.co.uk/people/the-liz-jones-interview"
    Then I should have an object of the post
    And the post has the headline "The Liz Jones Interview"
    And the widgets "paragraph,paragraph,paragraph,paragraph"
    And the paragraph widget at index 0:
      """
<div class="legacy-custom-html">
    <p>She’s been fired, divorced and reviled for her forthright and outlandish views which she charts in her weekly columns for the <em>Daily Mail</em>, but she never stays down for long. <em>Stylist</em>’s Alix Walker meets the UK’s most controversial writer</p>
</div>

      """
