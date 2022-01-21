# Intro
This project is a starting point to import your content from Wordpress to GraphCMS, but it can be also customized for others Headless CMS that support GraphQL API.

# Getting started
Clone this repository on your local machine.
A local PHP stack, like MAMP, is required; you can also put this package on your remote server, but be careful: this script has no built-in access control, so you should put it in a restricted access folder, and remove it after your migration process is completed.

Run composer and install required dependencies:
```
php composer.phar install
```
Then you should rename config-sample.php to config.php:
```
mv config-sample.php config.php
```