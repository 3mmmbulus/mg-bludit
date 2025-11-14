<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once(PATH_HELPERS . 'imagelibrary.class.php');

$pageLangFile = PATH_LANGUAGES . 'pages/image-library/' . $site->language() . '.json';
if (file_exists($pageLangFile)) {
    $pageLangData = json_decode(file_get_contents($pageLangFile), true);
    if (is_array($pageLangData)) {
        foreach ($pageLangData as $key => $value) {
            $L->db[$key] = $value;
        }
    }
}

$imageLibrary = new ImageLibrary();
$categories = $imageLibrary->getCategories();
$allowedPerPage = array(20, 50, 100, 200);

$activeType = isset($_GET['type']) ? Sanitize::html($_GET['type']) : 'entity';
if (!$imageLibrary->isValidType($activeType)) {
    $activeType = 'entity';
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
    if ($imageLibrary->isValidType($requestedType)) {
        $redirectType = $requestedType;
    }

    try {
        if ($action === 'upload') {
            $libraryType = Sanitize::html($_POST['library_type'] ?? '');
            $category = Sanitize::html($_POST['category'] ?? '');

            if (!$imageLibrary->isValidType($libraryType)) {
                throw new RuntimeException($L->g('image-library-error-invalid-type'));
            }

            if (!$imageLibrary->isValidCategory($category, $libraryType)) {
                throw new RuntimeException($L->g('image-library-error-invalid-category'));
            }

            if (!isset($_FILES['files'])) {
                throw new RuntimeException($L->g('image-library-error-no-files'));
            }

            $files = normalizeFilesArray($_FILES['files']);
            if (empty($files)) {
                throw new RuntimeException($L->g('image-library-error-no-files'));
            }

            $processed = 0;
            foreach ($files as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                if ($libraryType === 'entity') {
                    $imageLibrary->addEntityFile($file, $category);
                } else {
                    $imageLibrary->addLinkFile($file, $category);
                }

                $processed++;
            }

            if ($processed === 0) {
                throw new RuntimeException($L->g('image-library-error-upload-failed'));
            }

            Alert::set(sprintf($L->g('image-library-upload-success'), $processed));
        } elseif ($action === 'delete') {
            $libraryType = Sanitize::html($_POST['library_type'] ?? '');
            $id = Sanitize::html($_POST['id'] ?? '');

            if (!$imageLibrary->isValidType($libraryType) || $id === '') {
                throw new RuntimeException($L->g('image-library-error-invalid-request'));
            }

            if ($imageLibrary->deleteEntry($libraryType, $id)) {
                Alert::set($L->g('image-library-delete-success'));
            } else {
                throw new RuntimeException($L->g('image-library-error-delete-failed'));
            }
        } elseif ($action === 'bulk-delete') {
            $libraryType = Sanitize::html($_POST['library_type'] ?? '');
            $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? $_POST['ids'] : array();

            if (!$imageLibrary->isValidType($libraryType) || empty($ids)) {
                throw new RuntimeException($L->g('image-library-error-invalid-request'));
            }

            $deleted = $imageLibrary->deleteEntries($libraryType, array_map(function ($value) {
                return Sanitize::html($value);
            }, $ids));
            if ($deleted > 0) {
                Alert::set(sprintf($L->g('image-library-bulk-delete-success'), $deleted));
            } else {
                throw new RuntimeException($L->g('image-library-error-delete-failed'));
            }
        }
    } catch (Exception $e) {
        $message = translateImageLibraryError($e->getMessage(), $L);
        Alert::set($message, ALERT_STATUS_FAIL);
    }

    $redirectQuery = 'image-library';
    if ($imageLibrary->isValidType($redirectType)) {
        $redirectQuery .= '?type=' . urlencode($redirectType);
    }

    Redirect::page($redirectQuery);
}

$entityStats = $imageLibrary->getStats('entity');
$linkStats = $imageLibrary->getStats('link');
$entityPagination = $imageLibrary->getPaginatedEntries('entity', $activeType === 'entity' ? $pageNumber : 1, $perPage);
$linkPagination = $imageLibrary->getPaginatedEntries('link', $activeType === 'link' ? $pageNumber : 1, $perPage);
$entityEntries = $entityPagination['entries'];
$linkEntries = $linkPagination['entries'];
$activePagination = $activeType === 'entity' ? $entityPagination : $linkPagination;
$perPageOptions = $allowedPerPage;
$currentPerPage = $perPage;
$currentPageNumber = $activePagination['page'];
$totalPages = $activePagination['totalPages'];
$totalItems = $activePagination['total'];
$pageOffset = $activePagination['offset'];

$layout['title'] .= ' - ' . $L->g('image-library-title');

function normalizeFilesArray($files)
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

function translateImageLibraryError($message, $language)
{
    $map = array(
        'IMAGE_LIBRARY_LINK_SIZE_LIMIT' => 'image-library-error-link-size',
        'IMAGE_LIBRARY_UNSUPPORTED_LINK_FORMAT' => 'image-library-error-link-format'
    );

    if (isset($map[$message])) {
        return $language->g($map[$message]);
    }

    if (strpos($message, 'IMAGE_LIBRARY_UNSUPPORTED_IMAGE:') === 0) {
        $extension = substr($message, strlen('IMAGE_LIBRARY_UNSUPPORTED_IMAGE:'));
        return sprintf($language->g('image-library-error-image-format'), strtoupper($extension));
    }

    return $message;
}
