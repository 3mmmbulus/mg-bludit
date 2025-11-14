<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once(PATH_HELPERS . 'imagelibrary.class.php');

$imageLibrary = new ImageLibrary();
$type = Sanitize::html($_GET['type'] ?? '');
$id = Sanitize::html($_GET['id'] ?? '');
$format = Sanitize::html($_GET['format'] ?? 'json');

try {
    if (!$imageLibrary->isValidType($type) || $id === '') {
        throw new RuntimeException('Invalid request.');
    }

    $entry = $imageLibrary->findEntry($type, $id);
    if (!$entry) {
        throw new RuntimeException('Resource not found.');
    }

    if ($format === 'raw') {
        if ($type === 'entity') {
            $imageLibrary->streamEntityFile($entry);
        } else {
            $imageLibrary->streamLinkFile($entry);
        }
        exit;
    }

    if ($type === 'entity') {
        $data = $imageLibrary->getEntityPreviewData($entry);
    } else {
        $data = $imageLibrary->getLinkPreviewData($entry);
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
        'status' => 'success',
        'data' => $data
    ));
    exit;
} catch (Exception $e) {
    if ($format === 'raw') {
        header('HTTP/1.1 400 Bad Request');
        exit;
    }

    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array(
        'status' => 'error',
        'message' => $e->getMessage()
    ));
    exit;
}
