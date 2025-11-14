<?php defined('MAIGEWAN') or die('Maigewan CMS.');

if (!function_exists('handleLibraryRequest')) {
	function handleLibraryRequest(array $config)
	{
		global $L, $layout, $security, $site;

		$slug = $config['slug'];
		$languageKey = $config['languageKey'];
		$translationPrefix = $config['translationPrefix'];
		$libraryClass = $config['class'];
		$instanceVar = $config['instanceVar'];
		$defaultType = $config['defaultType'];

		$pageLangFile = PATH_LANGUAGES . 'pages/' . $languageKey . '/' . $site->language() . '.json';
		if (file_exists($pageLangFile)) {
			$pageLangData = json_decode(file_get_contents($pageLangFile), true);
			if (is_array($pageLangData)) {
				foreach ($pageLangData as $key => $value) {
					$L->db[$key] = $value;
				}
			}
		}

		$library = new $libraryClass();
		$GLOBALS[$instanceVar] = $library;
		$categories = $library->getCategories();
		$allowedPerPage = array(20, 50, 100, 200);

		$activeType = isset($_GET['type']) ? Sanitize::html($_GET['type']) : $defaultType;
		if (!$library->isValidType($activeType)) {
			$activeType = $defaultType;
		}

		$redirectType = $activeType;

		$perPage = isset($_GET['perPage']) ? (int)$_GET['perPage'] : 20;
		if (!in_array($perPage, $allowedPerPage, true)) {
			$perPage = 20;
		}

		$pageNumber = isset($_GET['page']) ? (int)$_GET['page'] : 1;
		if ($pageNumber < 1) {
			$pageNumber = 1;
		}

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$action = Sanitize::html($_POST['action'] ?? '');
			$requestedType = Sanitize::html($_POST['library_type'] ?? $activeType);
			if ($library->isValidType($requestedType)) {
				$redirectType = $requestedType;
			}

			try {
				if ($action === 'upload') {
					$libraryType = Sanitize::html($_POST['library_type'] ?? '');
					$category = Sanitize::html($_POST['category'] ?? '');

					if (!$library->isValidType($libraryType)) {
						throw new RuntimeException($L->g($translationPrefix . '-error-invalid-type'));
					}

					if (!$library->isValidCategory($category, $libraryType)) {
						throw new RuntimeException($L->g($translationPrefix . '-error-invalid-category'));
					}

					if (!isset($_FILES['files'])) {
						throw new RuntimeException($L->g($translationPrefix . '-error-no-files'));
					}

					$files = libraryNormalizeFilesArray($_FILES['files']);
					if (empty($files)) {
						throw new RuntimeException($L->g($translationPrefix . '-error-no-files'));
					}

					$processed = 0;
					foreach ($files as $file) {
						if ($file['error'] !== UPLOAD_ERR_OK) {
							continue;
						}

						if ($libraryType === 'entity') {
							$library->addEntityFile($file, $category);
						} else {
							$library->addLinkFile($file, $category);
						}

						$processed++;
					}

					if ($processed === 0) {
						throw new RuntimeException($L->g($translationPrefix . '-error-upload-failed'));
					}

					Alert::set(sprintf($L->g($translationPrefix . '-upload-success'), $processed));
				} elseif ($action === 'delete') {
					$libraryType = Sanitize::html($_POST['library_type'] ?? '');
					$id = Sanitize::html($_POST['id'] ?? '');

					if (!$library->isValidType($libraryType) || $id === '') {
						throw new RuntimeException($L->g($translationPrefix . '-error-invalid-request'));
					}

					if ($library->deleteEntry($libraryType, $id)) {
						Alert::set($L->g($translationPrefix . '-delete-success'));
					} else {
						throw new RuntimeException($L->g($translationPrefix . '-error-delete-failed'));
					}
				} elseif ($action === 'bulk-delete') {
					$libraryType = Sanitize::html($_POST['library_type'] ?? '');
					$ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();

					if (!$library->isValidType($libraryType) || empty($ids)) {
						throw new RuntimeException($L->g($translationPrefix . '-error-invalid-request'));
					}

					$deleted = $library->deleteEntries($libraryType, array_map(function ($value) {
						return Sanitize::html($value);
					}, $ids));
					if ($deleted > 0) {
						Alert::set(sprintf($L->g($translationPrefix . '-bulk-delete-success'), $deleted));
					} else {
						throw new RuntimeException($L->g($translationPrefix . '-error-delete-failed'));
					}
				}
			} catch (Exception $e) {
				$message = libraryTranslateError($e->getMessage(), $L, $translationPrefix);
				Alert::set($message, ALERT_STATUS_FAIL);
			}

			$redirectQuery = $slug;
			if ($library->isValidType($redirectType)) {
				$redirectQuery .= '?type=' . urlencode($redirectType);
			}

			Redirect::page($redirectQuery);
		}

		$entityStats = $library->getStats('entity');
		$linkStats = $library->getStats('link');
		$entityPagination = $library->getPaginatedEntries('entity', $activeType === 'entity' ? $pageNumber : 1, $perPage);
		$linkPagination = $library->getPaginatedEntries('link', $activeType === 'link' ? $pageNumber : 1, $perPage);
		$entityEntries = $entityPagination['entries'];
		$linkEntries = $linkPagination['entries'];
		$activePagination = $activeType === 'entity' ? $entityPagination : $linkPagination;
		$perPageOptions = $allowedPerPage;
		$currentPerPage = $perPage;
		$currentPageNumber = $activePagination['page'];
		$totalPages = $activePagination['totalPages'];
		$totalItems = $activePagination['total'];
		$pageOffset = $activePagination['offset'];

		$layout['title'] .= ' - ' . $L->g($translationPrefix . '-title');

		$GLOBALS['categories'] = $categories;
		$GLOBALS['entityStats'] = $entityStats;
		$GLOBALS['linkStats'] = $linkStats;
		$GLOBALS['entityEntries'] = $entityEntries;
		$GLOBALS['linkEntries'] = $linkEntries;
		$GLOBALS['activeType'] = $activeType;
		$GLOBALS['perPageOptions'] = $perPageOptions;
		$GLOBALS['currentPerPage'] = $currentPerPage;
		$GLOBALS['currentPageNumber'] = $currentPageNumber;
		$GLOBALS['totalPages'] = $totalPages;
		$GLOBALS['totalItems'] = $totalItems;
		$GLOBALS['pageOffset'] = $pageOffset;
		$GLOBALS['activePagination'] = $activePagination;
		$GLOBALS['defaultType'] = $defaultType;
	}
}

if (!function_exists('libraryNormalizeFilesArray')) {
	function libraryNormalizeFilesArray($files)
	{
		$result = array();

		if (is_array($files['name'])) {
			$fileCount = count($files['name']);
			for ($i = 0; $i < $fileCount; $i++) {
				$result[] = array(
					'name' => $files['name'][$i] ?? '',
					'type' => $files['type'][$i] ?? '',
					'tmp_name' => $files['tmp_name'][$i] ?? '',
					'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
					'size' => $files['size'][$i] ?? 0
				);
			}
		} else {
			$result[] = array(
				'name' => $files['name'] ?? '',
				'type' => $files['type'] ?? '',
				'tmp_name' => $files['tmp_name'] ?? '',
				'error' => $files['error'] ?? UPLOAD_ERR_NO_FILE,
				'size' => $files['size'] ?? 0
			);
		}

		return $result;
	}
}

if (!function_exists('libraryTranslateError')) {
	function libraryTranslateError($message, $language, $prefix)
	{
		$map = array(
			'IMAGE_LIBRARY_LINK_SIZE_LIMIT' => $prefix . '-error-link-size',
			'IMAGE_LIBRARY_UNSUPPORTED_LINK_FORMAT' => $prefix . '-error-link-format'
		);

		if (isset($map[$message])) {
			return $language->g($map[$message]);
		}

		$unsupportedPrefix = 'IMAGE_LIBRARY_UNSUPPORTED_IMAGE:';
		if (strpos($message, $unsupportedPrefix) === 0) {
			$extension = substr($message, strlen($unsupportedPrefix));
			return sprintf($language->g($prefix . '-error-image-format'), strtoupper($extension));
		}

		return $message;
	}
}
