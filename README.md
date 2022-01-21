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

# How is the app structured
Opening the app in the browser, you will notice a home screen with a list of available Models.
The app is built to manage all Wordpress contents as a "Model".
Each "Model" needs a "Runner", put in the /runners subfolder.

When you click a model, the app calls the Wordpress REST API endpoint, using the parameters provided in the config file.
You will see a summary of those parameters, and the total count of the available contents.
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
$graphCmsEndpoint = "https://api-eu-central-1.graphcms.com/v2/<project-id>/master";
$graphCmsUploadEndpoint = "https://api-eu-central-1.graphcms.com/v2/<project-id>/master/upload";
$graphCmsToken = "Bearer <your-token>";
```
The first section is relative to your Wordpress environment.
The first thing you have to do is to create an [Application Password for your REST API](https://artisansweb.net/how-to-use-application-passwords-in-wordpress-for-rest-api-authentication/); put user and the news created password in the $wpUserPwd variable.

The two sample endpoints are for posts.
The first one is needed to fetch post IDs.
The second one is for the single API call. Notice the %d parameter, which will be replaced with post ID during the import.
Feel free to customize them, according to the [Wordpress REST API reference](https://developer.wordpress.org/rest-api/reference/).
If you are planning to add your custom Runners, remember to add the two new endpoints in the config.php file.

The second section is relative to your GraphCMS environment.
You can find your endpoints in your GraphCMS control panel, in the **Project Settings / Api Access / Endpoint** section; please notice the two endpoints, the first one is for content management, the second one is for assets upload.
You should create a **Permanent Auth Token**, giving to it all the permissions you need; copy the token in the $graphCMS variable.

# Is the app ready for production?
The answer is: it depends.
