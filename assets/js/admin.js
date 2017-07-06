(function ($, window) {
    $(function () {

        var $reImportButtons = $('.js-catfish-reimport');
        $reImportButtons.click(function (e) {
            e.preventDefault();
            var $this = $(this);
            var data = {
                'action': 'catfishimporter_start_sync-url',
                'url': $this.data('id')
            };
            $.post(ajaxurl, data, function (re) {
                alert('Your item was queued. It should be processed shortly');
            });
        });

        if ($('body.settings_page_catfish_importer').length === 0) {
            return // If not on Catfish importer page, quit
        }


        // Category and All Sync Elements
        var $categoryOnExistAction = $('select[id=categoryOnExistAction]')
        var $categorySelect = $('select[name=category]')
        var $syncCategoryButton = $('input[name=sync-category]')
        var $syncUrlButton = $('input[name=sync-url]')
        // Single Sync URL Elements
        var $syncUrl = $('input[name=url]')
        var $onExistAction = $('select[id=onExistAction]')
        var $status = $('.import-status')
        var ajaxUrl = window.ajaxurl;

        $syncCategoryButton.click(onSyncCategoryClick)
        $syncUrlButton.click(onSyncUrlClick)

        listSections()
        getCurrentStatus()

        /**
         * Get all categories from master sitemap and populate form field with them
         */
        function listSections() {
            $.getJSON(ajaxUrl + '?action=catfishimporter_list_categories', function onListSections(response) {
                $categorySelect.html('')
                $categorySelect.append($('<option value="">').html('Select one...'))
                response.forEach(function (category) {
                    $categorySelect.append($('<option>').html(category))
                })

                $categorySelect.on('change', function (event) {
                    var sitemapSelected = $(event.currentTarget).val()
                    console.log('Sitemap selected: ' + sitemapSelected)
                    getCategoryStatus(sitemapSelected)
                })
            });
        }

        /**
         * Handle category URL sync submission
         * TODO: Link to queue in AWS
         */
        function onSyncCategoryClick(event) {
            event.preventDefault()
            $status.html('Syncing category\r\n')
            $syncCategoryButton.attr('disabled', 'disabled')
            $.post(
                ajaxUrl + '?action=catfishimporter_start_sync-category',
                {
                    catfishimporter_onExistAction: $categoryOnExistAction.val(),
                    catfishimporter_category_sitemap: $categorySelect.val()
                },
                function onSyncResponse(response) {
                    $status.html($status.html() + 'Sync queued. \r\n')
                    $syncCategoryButton.removeAttr('disabled')
                    console.log(response);
                }
            ).fail(function onSyncError(error) {
                $status.html($status.html() + 'Sync failed, see error in developer console')
                $syncCategoryButton.removeAttr('disabled')
                console.log(error)
            })
        }

        /**
         * Get the total imported posts % from selected category
         */
        function getCategoryStatus(sitemapUrl) {
            $status.html('Fetching stats for category&hellip;')
            if (sitemapUrl == 'all' || sitemapUrl == 'Select one...') {
                // If selected all then show the total import status
                getCurrentStatus()
            } else {
                $.get(
                    ajaxUrl + '?action=catfishimporter_get_category_status&sitemapUrl=' + sitemapUrl,
                    function onResponse(response) {
                        console.log(response)
                        $status.html('Current status for category: imported ' +
                            response.importedCount + ' out of ' + response.categoryTotal +
                            ' (' + Math.round((response.importedCount / response.categoryTotal) * 100) + '%)\r\n')
                    }
                ).fail(function onSyncError(error) {
                    $status.html($status.html() + 'Get category status for ' + sitemapUrl + ' failed\r\n')
                    $syncCategoryButton.removeAttr('disabled')
                    console.log(error)
                })
            }
        }

        /**
         * Handle single URL sync submission
         */
        function onSyncUrlClick(event) {
            event.preventDefault()
            $status.html('Syncing URL\r\n')
            $syncUrlButton.attr('disabled', 'disabled')
            $.post(
                ajaxUrl + '?action=catfishimporter_start_sync-url',
                {
                    catfishimporter_onExistAction: $onExistAction.val(),
                    catfishimporter_url: $syncUrl.val()
                },
                function onSyncResponse(response) {
                    // If ID string is return show success
                    if (typeof response === 'string' || response instanceof String) {
                        $status.html($status.html() + 'Sync URL queued.' + '\r\n')
                        // $status.html($status.html() + 'ID: ' + response.post.id + ', ' + response.post.url + '\r\n')
                    } else {
                        $status.html($status.html() + 'Sync URL failed. ' + '\r\n')
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

        /**
         * Get the total imported posts %
         */
        function getCurrentStatus() {
            $status.html('Fetching stats&hellip;')
            $.get(
                ajaxUrl + '?action=catfishimporter_get_status',
                function onResponse(response) {
                    console.log(response)
                    $status.html('Current status: imported ' +
                        response.importedCount + ' out of ' + response.total +
                        ' (' + Math.round((response.importedCount / response.total) * 100) + '%)\r\n')
                }
            ).fail(function onSyncError(error) {
                $status.html($status.html() + 'Get category status for ' + sitemapUrl + ' failed\r\n')
                $syncCategoryButton.removeAttr('disabled')
                console.log(error)
            })
        }


    });

})(jQuery, window);
