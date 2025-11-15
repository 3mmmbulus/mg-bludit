<?php defined('MAIGEWAN') or die('Maigewan CMS.');

checkRole(array('admin'));

require_once PATH_HELPERS . 'commentlibrary.class.php';

header('Content-Type: application/json; charset=UTF-8');

$library = new CommentLibrary();
$action = isset($_REQUEST['action']) ? Sanitize::html($_REQUEST['action']) : '';

try {
    switch ($action) {
        case 'list':
            echo json_encode(array(
                'status' => 'success',
                'data' => array(
                    'records' => $library->exportAsDataset(),
                    'statusSummary' => $library->getStatusSummary(),
                    'statusOptions' => $library->getStatusOptions(),
                    'sources' => $library->getSources(),
                    'categories' => $library->getCategories()
                )
            ));
            exit;

        case 'detail':
            $type = normalizeRequestType($_GET['type'] ?? '');
            $id = Sanitize::html($_GET['id'] ?? '');
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
            if ($limit < 1) {
                $limit = 100;
            }
            if ($id === '' || !$type) {
                throw new RuntimeException('Invalid request.');
            }

            $detail = $library->getDetail($type, $id, $limit);
            echo json_encode(array('status' => 'success', 'data' => $detail));
            exit;

        case 'upload':
            validateCsrf();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Invalid method.');
            }

            $requestedType = isset($_POST['library_type']) ? Sanitize::html($_POST['library_type']) : '';
            $type = normalizeRequestType($requestedType);
            if (!$type) {
                throw new RuntimeException('Invalid upload type.');
            }

            $category = isset($_POST['category']) ? Sanitize::html($_POST['category']) : '';
            if (!$library->isValidCategory($category, $type)) {
                throw new RuntimeException('Invalid category.');
            }

            if (!isset($_FILES['files'])) {
                throw new RuntimeException('No files provided.');
            }

            $files = normalizeFilesArray($_FILES['files']);
            if (empty($files)) {
                throw new RuntimeException('No files provided.');
            }

            $maxUpload = $library->getMaxUploadFiles();
            if (count($files) > $maxUpload) {
                throw new RuntimeException(sprintf('Exceeded upload limit of %d files.', $maxUpload));
            }

            $processed = 0;
            foreach ($files as $file) {
                if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if ($type === 'entity') {
                    $library->addEntityFile($file, $category);
                } else {
                    $library->addLinkFile($file, $category);
                }
                $processed++;
            }

            if ($processed === 0) {
                throw new RuntimeException('Upload failed.');
            }

            echo json_encode(array(
                'status' => 'success',
                'message' => sprintf('%d files uploaded successfully.', $processed),
                'data' => array(
                    'records' => $library->exportAsDataset(),
                    'statusSummary' => $library->getStatusSummary(),
                    'sources' => $library->getSources()
                )
            ));
            exit;

        case 'delete':
            validateCsrf();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Invalid method.');
            }

            $requestedType = isset($_POST['type']) ? Sanitize::html($_POST['type']) : '';
            $type = normalizeRequestType($requestedType);
            $id = isset($_POST['id']) ? Sanitize::html($_POST['id']) : '';
            if (!$type || $id === '') {
                throw new RuntimeException('Invalid delete request.');
            }

            if (!$library->deleteEntry($type, $id)) {
                throw new RuntimeException('Unable to delete the file.');
            }

            echo json_encode(array(
                'status' => 'success',
                'message' => 'File deleted.',
                'data' => array(
                    'records' => $library->exportAsDataset(),
                    'statusSummary' => $library->getStatusSummary()
                )
            ));
            exit;

        case 'bulk-delete':
            validateCsrf();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Invalid method.');
            }

            $rawItems = isset($_POST['items']) ? $_POST['items'] : array();
            if (is_string($rawItems)) {
                $decoded = json_decode($rawItems, true);
                if (is_array($decoded)) {
                    $rawItems = $decoded;
                }
            }
            if (!is_array($rawItems) || empty($rawItems)) {
                throw new RuntimeException('No items selected.');
            }

            $deleted = 0;
            foreach ($rawItems as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $type = normalizeRequestType($item['type'] ?? '');
                $id = Sanitize::html($item['id'] ?? '');
                if ($type && $id !== '' && $library->deleteEntry($type, $id)) {
                    $deleted++;
                }
            }

            if ($deleted === 0) {
                throw new RuntimeException('Nothing deleted.');
            }

            echo json_encode(array(
                'status' => 'success',
                'message' => sprintf('%d files deleted.', $deleted),
                'data' => array(
                    'records' => $library->exportAsDataset(),
                    'statusSummary' => $library->getStatusSummary()
                )
            ));
            exit;

        case 'update-status':
            validateCsrf();
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new RuntimeException('Invalid method.');
            }

            $requestedType = isset($_POST['type']) ? Sanitize::html($_POST['type']) : '';
            $type = normalizeRequestType($requestedType);
            $id = isset($_POST['id']) ? Sanitize::html($_POST['id']) : '';
            $status = isset($_POST['status']) ? Sanitize::html($_POST['status']) : '';
            if (!$type || $id === '' || $status === '') {
                throw new RuntimeException('Invalid request.');
            }

            $library->updateStatus($type, $id, $status);

            echo json_encode(array(
                'status' => 'success',
                'message' => 'Status updated.',
                'data' => array(
                    'records' => $library->exportAsDataset(),
                    'statusSummary' => $library->getStatusSummary()
                )
            ));
            exit;

        default:
            throw new RuntimeException('Unsupported action.');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        'status' => 'error',
        'message' => $e->getMessage()
    ));
    exit;
}

function normalizeRequestType($type)
{
    $normalized = strtolower(trim((string)$type));
    if ($normalized === 'content' || $normalized === 'entity') {
        return 'entity';
    }
    if ($normalized === 'nickname' || $normalized === 'link') {
        return 'link';
    }
    return '';
}

function normalizeFilesArray($files)
{
    $result = array();
    if (!is_array($files)) {
        return $result;
    }

    if (is_array($files['name'])) {
        $count = count($files['name']);
        for ($index = 0; $index < $count; $index++) {
            $result[] = array(
                'name' => $files['name'][$index] ?? '',
                'type' => $files['type'][$index] ?? '',
                'tmp_name' => $files['tmp_name'][$index] ?? '',
                'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$index] ?? 0
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

function validateCsrf()
{
    global $security;
    $token = isset($_POST['tokenCSRF']) ? $_POST['tokenCSRF'] : '';
    if (!$security->checkTokenCSRF($token)) {
        throw new RuntimeException('Security token validation failed.');
    }
}
