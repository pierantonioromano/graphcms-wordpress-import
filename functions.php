<?php

	
//Manage uploads on GraphCMS
function uploadGraphCMSAsset($assetUrls, $returnField)
{
	global $graphCmsUploadEndpoint, $graphCmsToken;

	if(!is_array($assetUrls))
		return false;

	$retVal = array();

	foreach($assetUrls as $singleAsset)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $graphCmsUploadEndpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "url=" . $singleAsset);

		$headers = array();
		$headers[] = 'Authorization: ' . $graphCmsToken;
		$headers[] = 'Content-Type: application/x-www-form-urlencoded';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		if($result)
		{
			$uploadResponse = json_decode($result);

			if($uploadResponse)
			{
				if(property_exists($uploadResponse, $returnField))
				{
					$uploadAssetField = $uploadResponse->{$returnField};

					$retVal[$singleAsset] = $uploadAssetField;
				}
			}
		}
	}

	return $retVal;
}

//Extract all images from post body
function extractImageUrlsFromPost($postBody)
{
	$imagesList = array();
	preg_match_all('/<img[^>]+>/i',$postBody, $result);
	
	if($result && count($result) > 0)
	{
		foreach($result[0] as $img_tag)
		{
			//echo "---".$img_tag;
			$pattern = '/src="([^"]*)"/';
			preg_match($pattern, $img_tag, $matches);
			$src = $matches[1];
			$imagesList[] = $src;
		}
	}

	return $imagesList;
}

?>