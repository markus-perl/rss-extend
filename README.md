rss-extend
==========

rss-extend is written in PHP using Zend Framework 2. rss-extend takes a rss feed and injects its missing content.
This is done by following the url in feed entries and grabbing the content with a simple css selector.
If you run rss-extend on a public host the feeds can be added to any reader including Feedly Reader (http://www.feedly.com).
Rss-extend is for private use only.


News
------------

2013-11-21 - Readability Parser Integration
    <method>readability</method>
    <readability>
        <token>fd6a28ec4af4e451c93cbab91234a10d9cb1b3f8</token>
    </readability>


Requirements
------------

* Linux
* PHP 5.3.3 or later
* using a cron job for prefetching the feeds is recommended


Installation and Downloads
--------------------------

Download the latest source code and extract it to your web dir. If you're using apache make sure the .htaccess file is parsed.
An example config for nginx can be found at puppet/modules/project/files/nginx/sites-enabled/default.

Adding a Feed
-------------

For every feed you wanna add you have to write a simple xml config file.

Create a file with a unique name in the folder feeds/

Example: feeds/MyFeed.xml

    <?xml version="1.0" encoding="UTF-8"?>
    <configdata>
        <MyFeed>
            <name>Screen Display Name</name>
            <url>http://my-feed/orig.rss</url>
            <method>dom</method>
            <dom>
                <content>.articleText</content><!-- CSS content selector -->
                <image>.articleImage img</image><!-- CSS image selector -->
            </dom>
            <postProcess>
                <mobilizer></mobilizer><!-- inserts a link into the feed to view links with google mobilizer -->
            </postProcess>
        </MyFeed>
    </configdata>

Available Post Processors
-------------------------

- Mobilizer: inserts a link into the feed to view links with google mobilizer
- RelativeLinks: adds a fixed path to a relative link
- RemoveAttribs: remove html attribs from the fetched content
- Static Image: adds a fixed path to an image

Available Pre Processors
-------------------------

- FixUrl: Reformat the target link by a regex

Development
----------

+ Checkout repository
+ Install vagrant and then run

        $ vagrant up

+ now open http://vagrant:8080 in your browser


Testing
-------

To run the tests ssh to your vagrant machine and enter:

    $ /vagrant/bin/phpunit


Contact
-------
* Github: [http://www.github.com/markus-perl/rss-extend](http://www.github.com/markus-perl/rss-extend)
* E-Mail: markus (at) open-mmx.de