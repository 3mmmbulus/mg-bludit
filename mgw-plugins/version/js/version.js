
function getLatestVersion() {

	console.log("[INFO] [PLUGIN VERSION] Getting list of versions of Maigewan.");

	$.ajax({
		url: "https://version.maigewan.com",
		method: "GET",
		dataType: 'json',
		success: function(json) {
			console.log("[INFO] [PLUGIN VERSION] Request completed.");

			// Constant MAIGEWAN_BUILD is defined on variables.js
			if (json.stable.build > MAIGEWAN_BUILD) {
				$(".current-version").hide();
				$(".new-version").show();
			}
		},
		error: function(json) {
			console.log("[WARN] [PLUGIN VERSION] There is some issue to get the version status.");
		}
	});
}

getLatestVersion();