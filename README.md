Agreable Catfish Importer Plugin
===============

For importing Catfish content in to Croissant

# Setup

The Catfish importer uses Amazon SQS queues, calls Envoyer heartbeat urls, reports handled errors directly to Bugsnag and requires some environment variables to be set before it will run. Make sure you set all of the following before attempting to run the command line actions:

```
AWS_SQS_KEY=
AWS_SQS_SECRET=
AWS_SQS_CATFISH_IMPORTER_REGION=
AWS_SQS_CATFISH_IMPORTER_QUEUE=

ENVOYER_HEARTBEAT_URL_IMPORTER=
ENVOYER_HEARTBEAT_URL_UPDATED_POSTS_SCANNER=
ENVOYER_HEARTBEAT_URL_SCHEDULED_POSTS_CACHE=

BUGSNAG_API_KEY=

CATFISH_IMPORTER_TARGET_URL=http://www.shortlist.com/
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

The queue commands offer direct access to pushing single or multiple posts into queue. It also supports an onExistAction attribute which selects how to handle existing posts in the database. By default the importer will update posts in place.

```
wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken
wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken skip
wp catfish queue http://www.shortlist.com/entertainment/the-toughest-world-record-ever-has-been-broken delete-insert
wp catfish queue all
wp catfish queue http://www.shortlist.com/sitemap/entertainment/48-hours-to.xml
```

### Work command

The work command actions one single item in the queue.

```
wp catfish work
```

### Purge command

The purge command **deletes all queue items**. Used to give tests a clean environment to work with.

```
wp catfish purge
```

### Clear Automated Testing posts command

The clearautomatedtesting command **deletes all posts with the automated_testing meta tag**.

```
wp catfish clearautomatedtesting
```
### Scan and import updated posts command

The scanupdates command finds any new posts since the last import ran and directly imports them.

```
wp catfish scanupdates
```

## Find missing posts command

The findmissing command goes through each sitemap and post in Clock and checks to see if we have a post with the catfish_importer_url value to match it. with the optional `--queuemissing` command you can add those that are missing to the queue.

```
wp catfish findmissing --queuemissing=true
```

### --debug

If you are having trouble or not receiving any output from the wp cli command then you can add the `--debug` flag to get detailed output from wp cli.

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

First install Supervisor using `apt-get`:

```
sudo apt-get install supervisor
```

*You may need to reboot the service using `sudo reboot now` for the supervisord service to start running for the first time*

Supervisor configuration files are typically stored in the `/etc/supervisor/conf.d` directory. Within this directory, you may create any number of configuration files that instruct supervisor how your processes should be monitored. For example, let's create a `/etc/supervisor/conf.d/catfish-worker.conf` file that starts and monitors a queue:work process:

```
[program:catfish-worker]
process_name=%(program_name)s_%(process_num)02d
command=wp catfish work
directory=/[PLUGINDIR]/agreable-catfish-importer-plugin/
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

To stop the Supervisor process run:

```
sudo supervisorctl stop catfish-worker:*
```

## Scanner with crontab

The new posts scanner runs on an interval and imports any new posts that have been added since the last post was importer.

Natively the Wordpress cron hijacks user page loads to run actions. We disable the nave Wordpress cron already.

To run the updates scan add the following line to your crontab:

```
*/5 *   * * *   ubuntu  cd /var/www/pages-staging.shortlist.com/htdocs/current/web/app/plugins/agreable-catfish-importer-plugin && wp catfish scanupdates > /var/log/cron/catfishscanupdates.log 2>&1
```

Make sure that you edit the `/etc/crontab` file directly so that you can add the user element of the command. Using `crontab -e` does not allow you to select with user the command will run by.

*Because the posts are added to the queue rather than imported directly I'd suggest running the scanner every 5 minutes, 2 minutes at the least to stop any duplicates being added to the queue.*
