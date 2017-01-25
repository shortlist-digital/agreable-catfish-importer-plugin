jQuery(function() {
  if (jQuery('body.toplevel_page_catfish-importer-index').length === 0) {
    return // If not on Catfish importer page, quit
  }
  console.log('CatfishImporter init')

  var $ = jQuery
  var $categorySelect = $('select[name=category]')
  var $urlSelect = $('select[name=url]')
  var $limitSelect = $('select[name=limit]')
  var $syncCategoryButton = $('input[name=sync-category]')
  var $syncUrlButton = $('input[name=sync-url]')
  var $syncUrl = $('input[name=url]')
  var $status = $('.import-status')
  var ajaxUrl = $('.ajax-url').html()

  $syncCategoryButton.click(onSyncCategoryClick)
  $syncUrlButton.click(onSyncUrlClick)

  listSections()

  function listSections() {
    $.getJSON(ajaxUrl + '?action=catfishimporter_list_categories', function onListSections(response) {
      $categorySelect.html('')
      $categorySelect.append($('<option value="">').html('Select one...'))
      response.forEach(function(category) {
        $categorySelect.append($('<option>').html(category))
      })

      $categorySelect.on('change', function(event) {
        var sitemapSelected = $(event.currentTarget).val()
        console.log('Sitemap selected: ' + sitemapSelected)
        getCategoryStatus(sitemapSelected)
      })
    });
  }

  function onSyncCategoryClick(event) {
    event.preventDefault()
    $status.html('Syncing category\r\n')
    $syncCategoryButton.attr('disabled', 'disabled')
    $.post(
      ajaxUrl + '?action=catfishimporter_start_sync-category',
      {
        catfishimporter_category_sitemap: $categorySelect.val(),
        catfishimporter_limit: $limitSelect.val(),

      },
      function onSyncResponse(response) {
        $status.html($status.html() + 'Sync complete, imported: ' + response.posts.length + '\r\n')
        response.posts.forEach(function onPost(post) {
          $status.html($status.html() + 'ID: ' + post.id + ', ' + post.url + '\r\n')
        })
        $syncCategoryButton.removeAttr('disabled')
        console.log(response);
      }
    ).fail(function onSyncError(error) {
      $status.html($status.html() + 'Sync failed, see error in developer console')
      $syncCategoryButton.removeAttr('disabled')
      console.log(error)
    })
  }

  function getCategoryStatus(sitemapUrl) {
    $status.html('Fetching stats for category&hellip;')
    $.get(
      ajaxUrl + '?action=catfishimporter_get_category_status&sitemapUrl=' + sitemapUrl,
      function onResponse(response) {
        console.log(response)
        $status.html('Current status for category: imported ' +
          response.importedCount + ' out of ' + response.categoryTotal +
          ' (' + Math.round((response.importedCount/response.categoryTotal)*100) + '%)\r\n')
      }
    ).fail(function onSyncError(error) {
      $status.html($status.html() + 'Get category status for ' + sitemapUrl + ' failed\r\n')
      $syncCategoryButton.removeAttr('disabled')
      console.log(error)
    })
  }

  function onSyncUrlClick(event) {
    event.preventDefault()
    $status.html('Syncing URL\r\n')
    $syncUrlButton.attr('disabled', 'disabled')
    $.post(
      ajaxUrl + '?action=catfishimporter_start_sync-url',
      {
        catfishimporter_url: $syncUrl.val()
      },
      function onSyncResponse(response) {
        if (response.success) {
          $status.html($status.html() + 'Sync URL success'  + '\r\n')
          $status.html($status.html() + 'ID: ' + response.post.id + ', ' + response.post.url + '\r\n')
        } else {
          $status.html($status.html() + 'Sync URL failed'  + '\r\n')
        }
        $syncUrlButton.removeAttr('disabled')
        console.log(response);
      }
    ).fail(function onSyncError(error) {
      $status.html($status.html() + 'Sync failed, see error in developer console')
      $syncUrlButton.removeAttr('disabled')
      console.log(error)
    })
  }


})
