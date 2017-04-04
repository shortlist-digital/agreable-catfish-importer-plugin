Agreable Catfish Importer Plugin
===============

For importing Catfish content in to Croissant

# Setup

The Catfish importer uses Amazon SQS queues and requires some environment variables to be set before it will run. Make sure you set all of the following before attempting to run the command line actions:

```
ILLUMINATE_ENCRYPTOR_KEY=
```

The Illuminate key is the equivalent to the ```APP_KEY``` in Laravel and must be an AES-256-CBC compatible encryption key. To generate a key use the following command:

```
cd /vagrant/web/app/plugins/agreable-catfish-importer-plugin
wp catfish generatekey
```

You also need to fill out the following details:

```
AWS_SQS_KEY=
AWS_SQS_SECRET=
AWS_SQS_CATFISH_IMPORTER_REGION=
AWS_SQS_CATFISH_IMPORTER_QUEUE=
```

These can be filled from your AWS connection. Note: *the queue should be the fully qualified queue url including http.... _not_ just the name of the queue*.

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
wp catfish purge --debug
```

### Clear Automated Testing posts command

The clearautomatedtesting command **deletes all posts with the automated_testing meta tag**.

```
wp catfish clearautomatedtesting
```
### Clear Automated Testing posts command

The scanupdates command finds any new posts since the last import ran and directly imports them.

```
wp catfish scanupdates
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

# Running the Importer on Staging and Production

## Queues with supervisord

The queueing system is run using supervisord worker processes. To setup the importer using supervisord follow these instructions:

Supervisor configuration files are typically stored in the /etc/supervisor/conf.d directory. Within this directory, you may create any number of configuration files that instruct supervisor how your processes should be monitored. For example, let's create a catfish-worker.conf file that starts and monitors a queue:work process:

```
[program:catfish-worker]
process_name=%(program_name)s_%(process_num)02d
command=wp catfish listen
directory=/var/www/pages-staging.shortlist.com/htdocs/current/web/app/dev/agreable-catfish-importer-plugin/
autostart=true
autorestart=true
user=ubuntu
numprocs=8
redirect_stderr=true
stdout_logfile=/var/log/supervisord/catfish-worker.log
```

In this example, the numprocs directive will instruct Supervisor to run 8 queue:work processes and monitor all of them, automatically restarting them if they fail. Of course, you should change the  queue:work sqs portion of the command directive to reflect your chosen queue driver.

Once the configuration file has been created, you may update the Supervisor configuration and start the processes using the following commands:

```
sudo supervisorctl reread

sudo supervisorctl update

sudo supervisorctl start catfish-worker:*

```

For more information on configuring and using Supervisor, consult the Supervisor documentation.

## Scanner with crontab

The new posts scanner runs on an interval and imports any new posts that have been added since the last post was importer.

Natively the Wordpress cron hijacks user page loads to run actions. We disable the nave Wordpres cron already.

To run the updates scan add the following line to your crontab;

```
*/5 * * * * cd /var/www/pages-staging.shortlist.com/htdocs/current/web/app/dev/agreable-catfish-importer-plugin; wp catfish scanupdates > /dev/null 2>&1
```

*Because the posts are added to the queue rather than imported directly I'd suggest running the scanner every 5 minutes, 2 minutes at the least to stop any duplicates being added to the queue.*
