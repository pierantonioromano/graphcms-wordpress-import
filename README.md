# Intro
This project is a starting point to import your content from Wordpress to GraphCMS, but it can be also customized for others Headless CMS that support GraphQL API.

# Getting started
Clone this repository on your local machine.
A local PHP stack, like MAMP, is required; you can also put this package on your remote server, but be careful: this script has no built-in access control, so you should put it in a restricted access folder, and remove it after your migration process is completed.

Run Composer and install required dependencies:
```
php composer.phar install
```
Then you should rename config-sample.php to config.php:
```
mv config-sample.php config.php
```

# How is the app structured
Opening the app in the browser, you will notice a home screen with a list of available Models.
The app is built to manage all Wordpress contents as a "Model".
Each "Model" needs a "Runner", put in the /runners subfolder.

When you click a model, the app calls the Wordpress REST API endpoint, using the parameters provided in the config file.
You will see a summary of those parameters, and the total count of available contents.
After starting the import, a call to the corresponding runner will be made for each content ID.
You will see a report for each processed record.

# Configuration settings
Let's take a look at the config.php file:
```
//Wordpress Settings
$wpUserPwd = "admin_user:application_password";
$wpEndpoints = array();
$wpEndpoints['posts_total'] = "https://www.example.com/wp-json/wp/v2/posts?per_page=100&context=view&status=publish&_fields[]=id";
$wpEndpoints['posts_single'] = "https://www.example.com/wp-json/wp/v2/posts/%d?context=edit&_embed";

//GraphCMS Settings
$graphCmsDefaultAuthor = "<author-id>";
$graphCmsEndpoint = "https://api-eu-central-1.graphcms.com/v2/<project-id>/master";
$graphCmsUploadEndpoint = "https://api-eu-central-1.graphcms.com/v2/<project-id>/master/upload";
$graphCmsToken = "Bearer <your-token>";
```
The first section is relative to your Wordpress environment.
The first thing you have to do is to create an [Application Password for your REST API](https://artisansweb.net/how-to-use-application-passwords-in-wordpress-for-rest-api-authentication/); put your admin username and the new created password in the $wpUserPwd variable.

The two sample endpoints are for posts.
The first one is needed to fetch post IDs.
The second one is for the single API call. Notice the %d parameter, which will be replaced with post ID during the import.
Feel free to customize them, according to the [Wordpress REST API reference](https://developer.wordpress.org/rest-api/reference/).
If you are planning to add your custom Runners, remember to add the two new endpoints in the config.php file.

The second section is relative to your GraphCMS environment.
You can find your endpoints in your GraphCMS control panel, in the **Project Settings / Api Access / Endpoint** section; please notice the two different endpoints, the first one is for content management, the second one is for assets upload.
You should create a **Permanent Auth Token**, giving to it all the permissions you need; copy the token in the $graphCMS variable.
The $graphCmsDefaultAuthor is the ID of the default author for imported content; you can create a new Author in GraphCMS and then put the ID in this variable.

# Is the app ready for production?
The answer is: it depends.
Since every Wordpress and GraphCMS setup can be heavily customized, this app is not intended to be a "click and run" solution.
You should investigate the sample Posts Runner and make sure that field names, data format and GraphQL Queries are properly suited to your needs.

# FAQ
## How can I display ACF custom fields in Wordpress API output?
If you use Advanced Custom Fields, you have to [enable the option](https://www.advancedcustomfields.com/resources/wp-rest-api-integration/) in the plugin settings to show them in Wordpress API output.

## Why is an Application Password needed for Wordpress?
The Application Password is needed to use the context=edit parameter in the Wordpress calls, which is useful to display both "raw" and "rendered" content for every field

## Can I add a custom Runner?
Yes, you can take the Posts Runner as an example, and place your custom Runner in the /runners folder.
The Runner's structure is quite simple, you have to write code to process each Wordpress record and then create the query to perform the insert on GraphCMS.
Don't forget to add the new endpoints to the config.php file.

## What are the "raw" and "rendered" content fields in the Wordpress API response?
Using context=edit parameter, you will get two versions for Wordpress system fields, such as Title, Content or Excerpt.
The "raw" one contains the value as it's stored in the database, without any custom processing.
The "rendered" one contains the value after Wordpress processing, with html entities, text replacements, shortcodes parsing, and so on.
You can customize the runner and choose the one that suits your needs.

## How can I map categories, tags, authors...?
There isn't a universal answer to this question.
You should write some custom functions to map your Wordpress fields to the ones on the GraphCMS side.
You can place your code in the functions.php file and then call the function from each Runner, when parsing the Wordpress output.

## How are assets managed?
In the sample Posts Runner, I created a function that uploads images with the [GraphCMS Upload API](https://graphcms.com/docs/api-reference/content-api/assets).
The featured post image is uploaded and then referenced in the cover_image field.
Images contained in the post body are uploaded via API and then a url replacement is made in the post content.

## Can I run the import for many times?
Yes, Posts Runner performs an "UpSert" mutation (update or insert), taking the post slug as unique field.
Remember that assets are not cleaned or updated, so you should delete them from GraphCMS before running a new import.

## How can I make the import faster?
Each import request needs some seconds to be processed, but if you have a huge amount of content, this may take a while.
The bottlenecks of the import process are asset processing and Wordpress API time response.
While there is nothing to do for assets, you can improve Wordpress API time response stripping out the "_embed" parameter from the endpoint urls. 
Please notice that stripping out the "_embed" parameter means that you have to customize your Wordpress theme/plugin code to output media data in the API response, and change the Runner code to fetch new added fields.

## Where can I see imported content?
You can see imported content in the corresponding model on GraphCMS.
Please note that imported content is in DRAFT stage, so you should perform a bulk Publish operation before seeing them on your frontend.