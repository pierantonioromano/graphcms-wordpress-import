<?php 

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

?>