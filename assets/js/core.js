/*
	App Functions
*/

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
		alert("Unable to start migration. Check your module code and make sure that content is available on your Wordpress endpoint.");
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
	$("#migrationTimer").timer();

	if (window.moduleRequests.length) 
	{
		//Go on with import...	
		var moduleRequest = window.moduleRequests.shift();

		$.ajax({
			url: window.moduleRunner,
			data: { id: moduleRequest },
			method: 'GET',
			dataType: 'json',
			timeout: 0,
			beforeSend: function(jqXHR, settings){
				$("#migrationLog").append('<p class="text-white mt-2">Starting request for ID: ' + moduleRequest);
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
		$("#migrationTimer").timer('pause');
		$("#migrationProgress").removeClass("running").addClass("done");
		$("#migrationLog").append('<p class="text-green-300 mt-4">Import completed!</p>');

		//Show retry buttons...
		$("#migrationStartButton")
		.text("Retry import")
		.removeAttr("onclick").click(function(){ document.location.reload(); })
		.show();
		$("#backIndexButton").show();
	}
}


/*
	jQuery Timer Plugin
*/
!function(n){var a={PLUGIN_NAME:"timer",TIMER_RUNNING:"running",TIMER_PAUSED:"paused",TIMER_REMOVED:"removed",DAYINSECONDS:86400};function s(t){var e;return t=t||0,e=Math.floor(t/60),{days:t>=a.DAYINSECONDS?Math.floor(t/a.DAYINSECONDS):0,hours:3600<=t?Math.floor(t%a.DAYINSECONDS/3600):0,totalMinutes:e,minutes:60<=t?Math.floor(t%3600/60):e,seconds:t%60,totalSeconds:t}}function r(t){return((t=parseInt(t,10))<10&&"0")+t}function e(){return Math.round((Date.now?Date.now():(new Date).getTime())/1e3)}function i(t){var e,n;return 0<t.indexOf("sec")?n=Number(t.replace(/\ssec/g,"")):0<t.indexOf("min")?(e=(t=t.replace(/\smin/g,"")).split(":"),n=Number(60*e[0])+Number(e[1])):t.match(/\d{1,2}:\d{2}:\d{2}:\d{2}/)?(e=t.split(":"),n=Number(e[0]*a.DAYINSECONDS)+Number(3600*e[1])+Number(60*e[2])+Number(e[3])):t.match(/\d{1,2}:\d{2}:\d{2}/)&&(e=t.split(":"),n=Number(3600*e[0])+Number(60*e[1])+Number(e[2])),n}function o(t,e){t.state=e,n(t.element).data("state",e)}var d={getDefaultConfig:function(){return{seconds:0,editable:!1,duration:null,callback:function(){console.log("Time up!")},repeat:!1,countdown:!1,format:null,updateFrequency:500}},unixSeconds:e,secondsToPrettyTime:function(t){var e=s(t);return e.days?e.days+":"+r(e.hours)+":"+r(e.minutes)+":"+r(e.seconds):e.hours?e.hours+":"+r(e.minutes)+":"+r(e.seconds):e.minutes?e.minutes+":"+r(e.seconds)+" min":e.seconds+" sec"},secondsToFormattedTime:function(t,e){for(var n=s(t),i=[{identifier:"%d",value:n.days},{identifier:"%h",value:n.hours},{identifier:"%m",value:n.minutes},{identifier:"%s",value:n.seconds},{identifier:"%g",value:n.totalMinutes},{identifier:"%t",value:n.totalSeconds},{identifier:"%D",value:r(n.days)},{identifier:"%H",value:r(n.hours)},{identifier:"%M",value:r(n.minutes)},{identifier:"%S",value:r(n.seconds)},{identifier:"%G",value:r(n.totalMinutes)},{identifier:"%T",value:r(n.totalSeconds)}],o=0;o<i.length;o++)e=e.replace(i[o].identifier,i[o].value);return e},durationTimeToSeconds:function(t){if(!isNaN(Number(t)))return t;var e=(t=t.toLowerCase()).match(/\d+d/),n=t.match(/\d+h/),i=t.match(/\d+m/),o=t.match(/\d+s/);if(!(e||n||i||o))throw new Error("Invalid string passed in durationTimeToSeconds!");var s=0;return e&&(s+=Number(e[0].replace("d",""))*a.DAYINSECONDS),n&&(s+=3600*Number(n[0].replace("h",""))),i&&(s+=60*Number(i[0].replace("m",""))),o&&(s+=Number(o[0].replace("s",""))),s},prettyTimeToSeconds:i,setState:o,makeEditable:function(t){n(t.element).on("focus",function(){t.pause()}),n(t.element).on("blur",function(){t.totalSeconds=i(n(t.element)[t.html]()),t.resume()})},intervalHandler:function(t){if(t.totalSeconds=e()-t.startTime,t.config.countdown)return t.totalSeconds=t.config.duration-t.totalSeconds,0===t.totalSeconds&&(clearInterval(t.intervalId),o(t,a.TIMER_STOPPED),t.config.callback(),n(t.element).data("seconds")),void t.render();t.render(),t.config.duration&&0<t.totalSeconds&&(t.totalSeconds%t.config.duration==0||t.totalSeconds>t.config.duration)&&(t.config.callback&&t.config.callback(),t.config.repeat||(clearInterval(t.intervalId),o(t,a.TIMER_STOPPED),t.config.duration=null))}};function c(t,e){if(this.element=t,this.originalConfig=n.extend({},e),this.totalSeconds=0,this.intervalId=null,this.html="html","INPUT"!==t.tagName&&"TEXTAREA"!==t.tagName||(this.html="val"),this.config=d.getDefaultConfig(),e.duration&&(e.duration=d.durationTimeToSeconds(e.duration)),"string"!=typeof e&&(this.config=n.extend(this.config,e)),this.config.seconds&&(this.totalSeconds=this.config.seconds),this.config.editable&&d.makeEditable(this),this.startTime=d.unixSeconds()-this.totalSeconds,this.config.duration&&this.config.repeat&&this.config.updateFrequency<1e3&&(this.config.updateFrequency=1e3),this.config.countdown){if(!this.config.duration)throw new Error("Countdown option set without duration!");if(this.config.editable)throw new Error("Cannot set editable on a countdown timer!");this.config.startTime=d.unixSeconds()-this.config.duration,this.totalSeconds=this.config.duration}}c.prototype.start=function(){this.state!==a.TIMER_RUNNING&&(d.setState(this,a.TIMER_RUNNING),this.render(),this.intervalId=setInterval(d.intervalHandler.bind(null,this),this.config.updateFrequency))},c.prototype.pause=function(){this.state===a.TIMER_RUNNING&&(d.setState(this,a.TIMER_PAUSED),clearInterval(this.intervalId))},c.prototype.resume=function(){this.state===a.TIMER_PAUSED&&(d.setState(this,a.TIMER_RUNNING),this.config.countdown?this.startTime=d.unixSeconds()-this.config.duration+this.totalSeconds:this.startTime=d.unixSeconds()-this.totalSeconds,this.intervalId=setInterval(d.intervalHandler.bind(null,this),this.config.updateFrequency))},c.prototype.remove=function(){clearInterval(this.intervalId),d.setState(this,a.TIMER_REMOVED),n(this.element).data(a.PLUGIN_NAME,null),n(this.element).data("seconds",null)},c.prototype.reset=function(){var t=this.originalConfig;this.remove(),n(this.element).timer(t)},c.prototype.render=function(){this.config.format?n(this.element)[this.html](d.secondsToFormattedTime(this.totalSeconds,this.config.format)):n(this.element)[this.html](d.secondsToPrettyTime(this.totalSeconds)),n(this.element).data("seconds",this.totalSeconds)},n.fn.timer=function(e){return e=e||"start",this.each(function(){n.data(this,a.PLUGIN_NAME)instanceof c||n.data(this,a.PLUGIN_NAME,new c(this,e));var t=n.data(this,a.PLUGIN_NAME);"string"==typeof e?"function"==typeof t[e]&&t[e]():t.start()})}}(jQuery);