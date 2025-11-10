class maigewanAjax {

	static async saveAsDraft(uuid, title, content) {
		let url = HTML_PATH_ADMIN_ROOT+"ajax/save-as-draft"
		try {
			const response = await fetch(url, {
				credentials: 'same-origin',
				method: "POST",
				headers: new Headers({
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
				}),
				body: new URLSearchParams({
					'tokenCSRF': tokenCSRF,
					'uuid': "autosave-" + uuid,
					'title': title,
					'content': content,
					'type': 'autosave'
				}),
			});
			const json = await response.json();
			return json;
		}
		catch (err) {
			console.log(err);
			return true;
		}
	}

	static async removeLogo() {
		let url = HTML_PATH_ADMIN_ROOT+"ajax/logo-remove"
		try {
			const response = await fetch(url, {
				credentials: 'same-origin',
				method: "POST",
				headers: new Headers({
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
				}),
				body: new URLSearchParams({
					'tokenCSRF': tokenCSRF
				}),
			});
			const json = await response.json();
			return json;
		}
		catch (err) {
			console.log(err);
			return true;
		}
	}

	// Alert the user when the user is not logged
	async userLogged(callBack) {
		console.log("[INFO] [MAIGEWAN AJAX] [userLogged()] Checking if the user is logged.");

		try {
			const response = await fetch(HTML_PATH_ADMIN_ROOT + "ajax/user-logged", {
				credentials: 'same-origin',
				method: "GET"
			});

			if (response.ok) {
				console.log("[INFO] [MAIGEWAN AJAX] [userLogged()] The user is logged.");
			} else if (response.status === 401) {
				console.log("[INFO] [MAIGEWAN AJAX] [userLogged()] The user is NOT logged.");
				callBack("You are not logged in anymore, so Maigewan can't save your settings and content.");
			}
		} catch (err) {
			console.log("[ERROR] [MAIGEWAN AJAX] [userLogged()] Request failed:", err);
		}
	}

	async generateSlug(text, parentKey, currentKey, callBack) {
		try {
			const response = await fetch(HTML_PATH_ADMIN_ROOT + "ajax/generate-slug", {
				credentials: 'same-origin',
				method: "POST",
				headers: new Headers({
					'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
				}),
				body: new URLSearchParams({
					'tokenCSRF': tokenCSRF,
					'text': text,
					'parentKey': parentKey,
					'currentKey': currentKey
				})
			});

			if (response.ok) {
				const data = await response.json();
				console.log("Maigewan AJAX: generateSlug(): success");
				callBack.val(data.slug);
			} else {
				console.log("Maigewan AJAX: generateSlug(): fail");
			}
		} catch (err) {
			console.log("Maigewan AJAX: generateSlug(): error", err);
		}
	}

}
