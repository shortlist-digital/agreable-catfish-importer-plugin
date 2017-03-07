Agreable Catfish Importer Plugin
===============

For importing Catfish content in to Croissant

# Managing the Importer Queue

Items can be added to the queue using the Wordpress interface or using the command line. To action items in the queue you can only use the command line interface.

WP_CLI should already be setup in vagrant for you.

To run wp commands specifically for the importer begin by logging into vagrant and finding the plugin directory:

```
vagrant ssh
cd /vagrant/web/app/plugins/agreable-catfish-importer-plugin
```

When running ```wp``` in the command line from this directory a couple of new commands will be added.

Queue commands:

```
wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken --debug
wp catfish queue all
wp catfish queue http://www.shortlist.com/sitemap/entertainment/48-hours-to.xml --debug
```

Listen command:

```
wp catfish listen
```
