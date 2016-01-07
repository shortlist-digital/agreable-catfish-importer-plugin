jQuery(function() {
  if (jQuery('body.catfish-importer_page_sync').length === 0) {
    return // If not on Catfish importer page, quit
  }

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
      response.forEach(function(category) {
        $categorySelect.append($('<option>').html(category))
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