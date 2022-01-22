<?php include("header.php"); ?>

<div class="container p-6">
	<div class="grid grid-col-12">
		<div class="col-span-12">
			
			<?php

			//Module list...
			if(!isset($_GET['module']))
			{
				echo '<h2 class="text-xl font-bold mb-6">Available import modules</h2>';
				foreach (glob("runners/*.php") as $filename)
				{
					echo '<a class="block w-96 bg-gray-800 hover:bg-gray-700 font-bold p-4 mb-4 rounded-lg" href="' . $_SERVER['PHP_SELF'] . '?module=' . basename($filename, ".php") . '">' . ucwords(basename($filename, ".php")) . '</a>';
				}
			}
			else
			{
				//Module settings
				$moduleSlug = $_GET['module'];
				$moduleSlugTotal = $moduleSlug . "_total";
				$moduleSlugSingle = $moduleSlug . "_single";

				//Call WP Endpoint and fetch total data
				$client = new \GuzzleHttp\Client();

				$response = $client->request('GET', $wpEndpoints[$moduleSlugTotal], [
					'headers' => [
						'Authorization' => "Basic " . base64_encode($wpUserPwd),
						'Content-Type' => 'application/json',
					]
				]);

				$responseJson = $response->getBody()->getContents();
				$responseContent = json_decode($responseJson);

				//Create page wrapper and setup data
				if($responseContent && count($responseContent) > 0)
				{
					echo '<h3 class="text-xl font-bold mb-6">' . count($responseContent) . ' ' . $moduleSlug . ' found</h2>';
					?>

					<script>

						//Module variables
						window.isRunningImport = false;
						window.totalRequests = <?=count($responseContent)?>;
						window.totalSuccessfulRequests = 0;
						window.totalErroredRequests = 0;
						window.moduleSlug = "<?=$moduleSlug?>";
						window.moduleRunner = "runners/" + window.moduleSlug + ".php";
						window.moduleRequests = [
							<?php 
							foreach($responseContent as $singleContent)
								echo $singleContent->id . ",";
							?>
						];

					</script>

					<?php

					//Module progress
					$moduleSlug = $_GET['module'];
					$moduleSlugTotal = $moduleSlug . "_total";
					$moduleSlugSingle = $moduleSlug . "_single";

					?>

					<!-- Endpoint summary -->
					<div id="migrationEndpointSummary" class="my-6 mb-12 relative rounded-lg bg-gray-700 max-h-[500px] overflow-auto" style="font-family: 'Fira Code';">
						<div class="sticky top-0 left-0 w-full p-4 bg-gray-800 shadow-lg">
							<?=parse_url($wpEndpoints[$moduleSlugTotal], PHP_URL_PATH)?>
						</div>
						<div class="p-4">
							<?php

							//Show endpoint params
							$endpointParams = explode("&", parse_url($wpEndpoints[$moduleSlugTotal], PHP_URL_QUERY));
							$endpointParams = implode("###PARAM_SEPARATOR###", $endpointParams);
							$endpointParamsChunks = array_chunk(preg_split('/(=|###PARAM_SEPARATOR###)/', $endpointParams), 2);
							
							$endpointParamsResult = array_combine(array_column($endpointParamsChunks, 0), array_column($endpointParamsChunks, 1));
							$printable = json_encode($endpointParamsResult, JSON_PRETTY_PRINT);
							
							print "<pre>";
							print_r($printable);
							print "</pre>";

							?>
						</div>
					</div>

					<!-- Module start button -->
					<a id="backIndexButton" href="index.php" class="font-bold bg-gray-600 hover:bg-gray-700 py-4 px-8 mr-2 rounded-lg">Back to Modules</a>
					<a id="migrationStartButton" href="javascript:void(0)" onclick="migrationStart()" class="font-bold bg-blue-600 hover:bg-blue-700 py-4 px-8 rounded-lg">Start import</a>
					<!-- Module log wrapper -->
					<div id="migrationLogWrapper" class="my-6 relative rounded-lg bg-gray-700 h-[500px] overflow-auto" style="font-family: 'Fira Code'; display: none;">
						<div class="sticky top-0 left-0 w-full p-4 bg-gray-800 shadow-lg">
							Successful: <strong class="text-green-400 mr-4" id="moduleSuccessfulCounter">0</strong>
							Errors: <strong class="text-red-400 mr-4" id="moduleErroredCounter">0</strong>
							Time: <strong class="text-yellow-400" id="migrationTimer"></strong>
							<span id="migrationProgress" class="absolute right-4">
								<span class="running">
									<img src="assets/img/loading.svg" class="max-w-[24px] inline" alt="Loading..." />
									running import...
								</span>	
								<span class="done">
									Done!
								</span>
							</span>
						</div>
						<div id="migrationLog" class="p-4"></div>
					</div>

					<?php

				}
				else
					echo "No contents found on Wordpress Endpoint.";

				
			}

			?>
	</div>
</div>

<?php include("footer.php"); ?>