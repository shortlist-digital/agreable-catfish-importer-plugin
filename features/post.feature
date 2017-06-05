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
    And the "image" "width" is "large" at index 0
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
    And gallery item 1 has title "Number 11 by Jonathan Coe"
    And gallery item 1 has caption:
      """
<p>Coe paints a bleak picture of life in the UK as librarians who rely on food banks exist alongside the super rich who build elaborate subterranean extensions to accommodate their swimming pools and cinemas.&nbsp; Staff quarters for the nannies and caretakers are always located on the deepest, darkest floor. Depressing and amusing in fairly equal measure.</p>

<p><em><a href="http://www.amazon.co.uk/372/dp/0670923796" target="_blank">Buy it here</a></em></p>
      """
    And gallery item 2 has title "The Clasp by Sloane Crosley"
    And gallery item 2 has caption:
      """
<p>When Kezia, Nathaniel and Victor meet up at the wedding of their ludicrously wealthy heiress friend, Victor stumbles into a mystery about a valuable necklace that will lead them all to France&hellip; A clever, funny story about those difficult post college years inspired by <em>The Necklace</em> by Guy de Maupassant.</p>

<p><em><a href="http://www.amazon.co.uk/The-Clasp-Sloane-Crosley/dp/0091954436" target="_blank">Buy it here</a></em></p>
      """

  Scenario: Correctly encode the paragraph widget of an article
    Given the post "http://www.stylist.co.uk/people/the-liz-jones-interview"
    Then I should have an object of the post
    And the post has the headline "The Liz Jones Interview"
    And the widgets "paragraph,paragraph,html,paragraph,html,paragraph,paragraph,paragraph"
    And the paragraph widget at index 0:
      """
<p>She’s been fired, divorced and reviled for her forthright and outlandish views which she charts in her weekly columns for the <em>Daily Mail</em>, but she never stays down for long. <em>Stylist</em>’s Alix Walker meets the UK’s most controversial writer</p>

      """


  Scenario: Article has correct number of widgets
    Given the post "http://www.shortlist.com/style/fashion/best-trainers-sneakers-hottest-february-2017"
    Then I should have an object of the post
    And the post has the headline "The 10 Hottest Trainers for February 2017"
    And the post has 10 "image" widgets
