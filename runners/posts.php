<?php

/*
	Migration runner for content type: Posts
*/

require '../vendor/autoload.php';
include("../config.php");
include("../functions.php");

//no time limit while running import
set_time_limit(0); 

$retVal = array("status" => "", "msg" => "");
$moduleSlug = basename($_SERVER['PHP_SELF'], ".php");
$moduleSlugTotal = $moduleSlug . "_total";
$moduleSlugSingle = $moduleSlug . "_single";

//Check that a valid ID was provided...
if(!isset($_GET['id']) || !is_numeric($_GET['id']))
	$retVal = array("status" => "KO", "msg" => "Invalid request ID.");
else
{
	//Call WP endpoint and extract content data
	$wpEndpoint = sprintf($wpEndpoints[$moduleSlugSingle], $_GET['id']);

	$wpClient = new \GuzzleHttp\Client();

	$wpResponse = $wpClient->request('GET', $wpEndpoint, [
		'headers' => [
			'Authorization' => 'Basic ' . base64_encode($wpUserPwd),
			'Content-Type' => 'application/json',
		]
	]);

	$wpResponseJson = $wpResponse->getBody()->getContents();
	$wpRecord = json_decode($wpResponseJson);

	$retVal['msg'] .= "<p class='text-gray-400'>Processing post: " . $wpRecord->title->raw . "</p>";

	//Build post data
	$currentPost = [];
	$currentPost['author'] = $graphCmsDefaultAuthor;
	$currentPost['title'] = $wpRecord->title->rendered;
	$currentPost['excerpt'] = $wpRecord->acf->postsubtitle ? $wpRecord->acf->postsubtitle : strip_tags($wpRecord->excerpt->raw);
	$currentPost['slug'] = $wpRecord->slug;
	$currentPost['content'] = $wpRecord->content->raw;
		
	//Date
	$postDateObj = new DateTime($wpRecord->date);
	$currentPost['date'] = $postDateObj->format(DateTime::ATOM); //Iso 8601

	//Featured image
	if($options[$moduleSlug]['process_featured_image'] === true && $options[$moduleSlug]['perform_dry_run'] !== true)
	{
		$currentPost['cover_image'] = property_exists($wpRecord->_embedded, "wp:featuredmedia") ? uploadGraphCMSAsset(array($wpRecord->_embedded->{"wp:featuredmedia"}[0]->media_details->sizes->full->source_url), 'id') : "";

		if($currentPost['cover_image'] != "")
		{
			$currentPost['cover_image'] = array_values($currentPost['cover_image'])[0];

			$retVal['msg'] .= "<p class='text-gray-400'>Imported featured image as: " . $currentPost['cover_image'] . "</p>";
		}
	}

	//Images in post body
	if($options[$moduleSlug]['process_post_body_images'] === true && $options[$moduleSlug]['perform_dry_run'] !== true)
	{
		$currentPostExtraImages = extractImageUrlsFromPost($currentPost['content']);

		if($currentPostExtraImages)
		{
			$currentPostExtraImagesUploadJob = uploadGraphCMSAsset($currentPostExtraImages, 'url');
			$currentPost['extraImages'] = $currentPostExtraImagesUploadJob;

			//Replace original images in post body
			$currentPost['content'] = str_replace(array_keys($currentPost['extraImages']), array_values($currentPost['extraImages']), $currentPost['content']);

			$retVal['msg'] .= "<p class='text-gray-400'>Imported " . count($currentPost['extraImages']) . " post body image(s)</p>";
		}
	}

	//Build GraphQL query
	if($options[$moduleSlug]['process_post_body_images'] === true && $options[$moduleSlug]['perform_dry_run'] !== true)
		$coverImageQuery = ($currentPost['cover_image'] != "") ? 'coverImage: {connect: {id: "' . $currentPost['cover_image'] . '"}}' : "";
	else
		$coverImageQuery = "";

	$query = <<<GQL
		mutation upsertPost {
			upsertPost( 
				upsert: {
					create: { 
						author: {connect: {id: "{$currentPost['author']}"}}, 
						title: """{$currentPost['title']}""", 
						excerpt: """{$currentPost['excerpt']}""", 
						slug: "{$currentPost['slug']}", 
						date: "{$currentPost['date']}", 
						content: """{$currentPost['content']}""", 
						{$coverImageQuery}
					}, 
					update: { 
						author: {connect: {id: "{$currentPost['author']}"}}, 
						title: """{$currentPost['title']}""", 
						excerpt: """{$currentPost['excerpt']}""", 
						slug: "{$currentPost['slug']}", 
						date: "{$currentPost['date']}", 
						content: """{$currentPost['content']}""", 
						{$coverImageQuery}
					}
				}
				where: { slug: "{$currentPost['slug']}" }
			)
			{
				id
				title
			}

		}
GQL;

	//Run GraphCMS Request
	if($options[$moduleSlug]['perform_dry_run'] === false)
	{
		$graphCmsClient = new \GuzzleHttp\Client();

		$graphCmsResponse = $graphCmsClient->request('POST', $graphCmsEndpoint, [
			'headers' => [
				'Authorization' => $graphCmsToken,
				'Content-Type' => 'application/json',
			],
			'json' => [
				'query' => $query
			]
		]);

		$graphCmsResponseJson = $graphCmsResponse->getBody()->getContents();
		$graphCmsBody = json_decode($graphCmsResponseJson);
		$graphCmsData = $graphCmsBody->data;

		//Process GraphCMS results
		if($graphCmsData->upsertPost->id)
		{
			$retVal['msg'] .= "<p class='text-sky-300'>Successful import: " . $graphCmsData->upsertPost->id . "</p>";
			$retVal['status'] = "OK";
		}
		else
		{
			$retVal['msg'] .= "<p class='text-red-300'>GraphCMS import error!</p>";
			$retVal['status'] = "KO";
		}
	}
	else
	{
		$retVal['msg'] .= "<p class='text-pink-300'>Dry run is active - No insert on GraphCMS was done!</p>";
		$retVal['status'] = "OK";
	}

}

//Give http response code
if($retVal['status'] == "OK")
	http_response_code(200);
else
	http_response_code(400);

//Give json response
echo json_encode($retVal);

?>