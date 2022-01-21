//Prevent from accidentally abort import
$(window).bind("beforeunload",function(event) {
	if (window.isRunningImport === true) {
		return "Do you really want to abort the import operation?";
	} else {
		return;
	}
});

//Start migration
function migrationStart()
{
	if(!window.moduleRunner || !window.moduleRequests)
	{
		alert("Unable to start migration. Check your module code and makle sure that content is available on your Wordpress endpoint.");
		return false;
	}

	window.isRunningImport = true;
	$("#migrationStartButton, #backIndexButton, #migrationEndpointSummary").hide();
	$("#migrationProgress").removeClass("done").addClass("running");
	$("#migrationLog").html('<p class="text-green-300">Starting import...</p>');
	$("#migrationLogWrapper").show();

	processModuleRequests();
}

//Process module requests - Ajax calls are queued and fired one by one
function processModuleRequests()
{
	if (window.moduleRequests.length) 
	{
		//Go on with import...	
		var moduleRequest = window.moduleRequests.shift();

		$.ajax({
			url: window.moduleRunner,
			data: { id: moduleRequest },
			method: 'GET',
			dataType: 'json',
			beforeSend: function(jqXHR, settings){
				$("#migrationLog").append('<p class="text-yellow-300 mt-2">Starting request for ID: ' + moduleRequest);
			}
		})
		.done(function(data, textStatus, jqXHR){
			window.totalSuccessfulRequests++;
			$("#moduleSuccessfulCounter").html(window.totalSuccessfulRequests);
			$("#migrationLog").append(data.msg);
		})
		.fail(function(data, textStatus, jqXHR){
			window.totalErroredRequests++;
			$("#moduleErroredCounter").html(window.totalErroredRequests);
			$("#migrationLog").append('<p class="text-red-300">Failed request for ID: ' + moduleRequest + " - See browser console for details");
			console.log("Request details for ID: " + moduleRequest);
			console.log(data);
		})
		.always(function() { 
			$('#migrationLogWrapper').stop().animate({
				scrollTop: $('#migrationLogWrapper')[0].scrollHeight
			}, 800);
			processModuleRequests(); //go on with queue
		});
	}
	else
	{
		//Finished import
		window.isRunningImport = false;
		$("#migrationProgress").removeClass("running").addClass("done");
		$("#migrationLog").append('<p class="text-yellow-300">Import completed!</p>');

		//Show retry buttons...
		$("#migrationStartButton")
		.text("Retry import")
		.removeAttr("onclick").click(function(){ document.location.reload(); })
		.show();
		$("#backIndexButton").show();
	}
}
