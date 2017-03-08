Agreable Catfish Importer Plugin
===============

For importing Catfish content in to Croissant

# Setup

The Catfish importer uses Amazon SQS queues and requires some environment variables to be set before it will run. Make sure you set all of the following before attempting to run the command line actions:

```
ILLUMINATE_ENCRYPTOR_KEY=
```

The Illuminate key is the equivelent to the ```APP_KEY``` in Laravel and must be an AES-256-CBC compatible encryption key.

```
AWS_KEY=
AWS_SECRET=
AWS_SQS_CATFISH_IMPORTER_REGION=
AWS_SQS_CATFISH_IMPORTER_QUEUE=
```

The rest can be filled from your AWS connection. Note: *the queue should be the fully qualified queue url including http.... _not_ just the name of the queue*.

# Managing the Importer Queue

Items can be added to the queue using the Wordpress interface or using the command line. To action items in the queue you can only use the command line interface.

WP_CLI should already be setup in vagrant for you.

To run wp commands specifically for the importer begin by logging into vagrant and finding the plugin directory:

```
vagrant ssh
cd /vagrant/web/app/plugins/agreable-catfish-importer-plugin
```

When running ```wp``` in the command line from this directory a couple of new commands will be added.

### Queue commands

The queue commands offer direct access to pushing single or multiple posts into queue.

```
wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken --debug
wp catfish queue all --debug
wp catfish queue http://www.shortlist.com/sitemap/entertainment/48-hours-to.xml --debug
```

### Work command

The work command actions one single item in the queue.

```
wp catfish work --debug
```

### Listen command

The listen command works through all items in the queue and continues watching for more queue items to be added.

```
wp catfish listen --debug
```
### Purge command

The purge command **deletes all queue items**. Used to give tests a clean environment to work with.

```
wp catfish listen --debug
```
### --debug

To get any output from the wp command, event success/failure messages and info messages you need to have the

# Queues

The following queues are setup in AWS for use in development, staging and production:

```
sh-catfish-importer-production
sh-catfish-importer-staging
sh-catfish-importer-develop
st-catfish-importer-production
st-catfish-importer-staging
st-catfish-importer-develop
```

`sh` is Shortlist and `st` Stylist.
