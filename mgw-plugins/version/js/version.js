function getLatestVersion() {

	console.log("[INFO] [PLUGIN VERSION] Getting list of versions of Maigewan.");

	fetch("https://version.maigewan.com")
		.then(response => response.json())
		.then(json => {
			console.log("[INFO] [PLUGIN VERSION] Request completed.");

			// Constant MAIGEWAN_BUILD is defined on variables.js
			if (json.stable.build > MAIGEWAN_BUILD) {
				const currentVersion = document.querySelector(".current-version");
				const newVersion = document.querySelector(".new-version");
				if (currentVersion) currentVersion.style.display = 'none';
				if (newVersion) newVersion.style.display = 'block';
			}
		})
		.catch(error => {
			console.log("[WARN] [PLUGIN VERSION] There is some issue to get the version status.");
		});
}

getLatestVersion();