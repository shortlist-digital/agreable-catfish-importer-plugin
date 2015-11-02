Feature: agreable-catfish-importer-plugin
  Test the Catfish Importer plugin

  Scenario: Fetch list of sections
    Given the sitemap index "http://www.stylist.co.uk/sitemap-index.xml"
    Then I should have a list of sections

  Scenario: Fetch articles from a section sitemap
    Given the section sitemap "http://www.stylist.co.uk/sitemap/books.xml"
    Then I should have a list of articles

  Scenario: Fetch a Harry Potter article
    Given the article "http://www.stylist.co.uk/books/beautiful-images-from-first-ever-illustrated-harry-potter-book-released"
    Then I should have an object of the article
    And the image filename is "3097e3e7-bdb8-469b-90c8-3680ce82c863-734x1020.jpeg" at index 0
    And the article has the headline "Beautiful images from first ever illustrated Harry Potter book released"
    And the article has the property "short_headline" of "Images from first ever illustrated Harry Potter book released"
    And the category slug "books"
    And the widgets "paragraph,paragraph,image,paragraph,image,paragraph,image,paragraph,image,paragraph,image,paragraph"
    And the paragraph widget at index 0:
      """
<p>Meet the Harry Potter characters like you&#39;ve never seen them before.</p>
<p> Publisher&nbsp;Scholastic has released four images from the first ever fully illustrated edition of <em>Harry Potter and the Sorcerer&rsquo;s Stone.</em></p>
<p> The book, which is the first of J.K. Rowling&#39;s bestselling series, is to be published worldwide on 6 October this year.</p>
<p> Carnegie medal-winning illustrator Jim Kay&nbsp;was chosen to illustrate all seven <em>Harry Potter </em>books in full colour and design the new book jacket image.&nbsp;</p>
<p> Get a sneak preview of four of the leading characters in full illustrated glory, below.</p>

      """
    And the image "width" is "full" at index 0
    And the image "position" is "center" at index 0
