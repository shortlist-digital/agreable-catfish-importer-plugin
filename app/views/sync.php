<h1>Catfish Importer</h1>
<h2>Sync a Category</h2>
<p>
  To run full site imports use the Catfish command line tools. To queue all posts for import run 'wp catfish queue all' in the 'web/app/plugins/agreable-catfish-importer-plugin' folder.
</p>
<form>
  <label for="category">Category:</label>
  <select name='category' id='category'>
    <option value=''>Loading</option>
  </select>

  <label for="categoryOnExistAction">If Post Exists:</label>
  <select name='categoryOnExistAction' id='categoryOnExistAction'>
    <option value="update">Update Existing</option>
    <option value="delete-insert">Delete then Insert</option>
    <option value="skip">Skip</option>
  </select>

  <input type='submit' name='sync-category' value='Sync'>

</form>

<h2>Sync a URL</h2>
<form>
  <label for="url">URL:</label>
  <input id='url' name='url' placeholder='http://www.stylist.co.uk/life/the-top-15-most-retweeted-posts-by-women-of-2015' style="min-width: 20vw;"/>

  <label for="onExistAction">If Post Exists:</label>
  <select name='onExistAction' id='onExistAction'>
    <option value="update">Update Existing</option>
    <option value="delete-insert">Delete then Insert</option>
    <option value="skip" selected>Skip</option>
  </select>

  <input type='submit' name='sync-url' value='Sync URL'>
</form>

<h2>Status</h2>
<pre class='import-status'>Loading current status: &hellip;</pre>