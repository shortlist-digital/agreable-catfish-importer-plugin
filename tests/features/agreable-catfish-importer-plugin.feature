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
    And the article has the headline "Beautiful images from first ever illustrated Harry Potter book released"
    And the article has the property "short_headline" of "Images from first ever illustrated Harry Potter book released"
    And the category slug "books"
    And the widgets "paragraph,paragraph,image,paragraph,image,paragraph,image,paragraph,image,paragraph,image,paragraph"
    And the first paragraph widget:
      """
      <p>Meet the Harry Potter characters like you've never seen them before.<br>
      <br>
      Publisher&nbsp;Scholastic has released four images from the first ever fully illustrated edition of <em>Harry Potter and the Sorcerer’s Stone.</em><br>
      <br>
      The book, which is the first of J.K. Rowling's bestselling series, is to be published worldwide on 6 October this year.<br>
      <br>
      Carnegie medal-winning illustrator Jim Kay&nbsp;was chosen to illustrate all seven <em>Harry Potter </em>books in full colour and design the new book jacket image.&nbsp;<br>
      <br>
      Get a sneak preview of four of the leading characters in full illustrated glory, below.</p>
      """
