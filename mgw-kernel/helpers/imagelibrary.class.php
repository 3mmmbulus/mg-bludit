<?php defined('MAIGEWAN') or die('Maigewan CMS.');

class ImageLibrary
{
    protected $basePath;

    protected $types = array(
        'entity' => array(
            'folder' => 'entities',
            'prefix' => 'ENT'
        ),
        'link' => array(
            'folder' => 'links',
            'prefix' => 'LNK'
        )
    );

    protected $allowedImageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif', 'tif', 'tiff', 'ico', 'heic', 'heif');

    protected $maxLinkFileSize = 2097152; // 2MB

    protected $defaultCategories = array(
        'enterprise',
        'blog',
        'news',
        'commerce',
        'community',
        'directory',
        'forum',
        'tools',
        'video',
        'image',
        'download',
        'document',
        'qa',
        'other'
    );

    protected $categoryCache = array();

    public function __construct($basePath = null)
    {
        $this->basePath = $basePath ?: PATH_CONFIG . 'image-library' . DS;
        $this->ensureDirectory($this->basePath);

        foreach ($this->types as $type => $info) {
            $this->ensureDirectory($this->basePath . $info['folder'] . DS);
            $this->migrateLegacyManifest($type);
        }
    }

    public function getCategories($type = null)
    {
        $cacheKey = '_all';
        if ($type !== null && $this->isValidType($type)) {
            $cacheKey = $type;
        } else {
            $type = null;
        }

        if (isset($this->categoryCache[$cacheKey])) {
            return $this->categoryCache[$cacheKey];
        }

        $categories = $this->defaultCategories;
        $typesToScan = $type !== null ? array($type) : array_keys($this->types);

        foreach ($typesToScan as $scanType) {
            $directory = $this->getTypeDirectory($scanType);
            if (!is_dir($directory)) {
                continue;
            }

            $items = scandir($directory);
            if ($items === false) {
                continue;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $fullPath = $directory . $item;
                if (!is_dir($fullPath)) {
                    continue;
                }

                if ($item === '' || $item[0] === '.') {
                    continue;
                }

                $categories[] = $item;
            }
        }

        $categories = array_values(array_unique($categories));

        $otherIndex = array_search('other', $categories, true);
        if ($otherIndex !== false) {
            unset($categories[$otherIndex]);
        }

        sort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        $categories[] = 'other';

        $this->categoryCache[$cacheKey] = $categories;

        return $categories;
    }

    public function isValidType($type)
    {
        return isset($this->types[$type]);
    }

    public function isValidCategory($category, $type = null)
    {
        $category = trim((string)$category);
        if (!$this->isSafeCategoryName($category)) {
            return false;
        }

        if (in_array($category, $this->defaultCategories, true)) {
            return true;
        }

        if ($type !== null && $this->isValidType($type)) {
            $directory = $this->getCategoryDirectory($type, $category);
            if (is_dir($directory)) {
                return true;
            }

            return true;
        }

        foreach ($this->types as $typeKey => $info) {
            $directory = $this->getCategoryDirectory($typeKey, $category);
            if (is_dir($directory)) {
                return true;
            }
        }

        return false;
    }

    public function getTypeFolder($type)
    {
        return $this->types[$type]['folder'];
    }

    public function getTypeDirectory($type)
    {
        return $this->basePath . $this->getTypeFolder($type) . DS;
    }

    public function listEntries($type)
    {
        if (!$this->isValidType($type)) {
            return array();
        }

        $entries = array();
        $typeDirectory = $this->getTypeDirectory($type);
        if (!is_dir($typeDirectory)) {
            return $entries;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $typeDirectory,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }

            $extension = strtolower($fileInfo->getExtension());
            if ($type === 'entity' && !in_array($extension, $this->allowedImageExtensions, true)) {
                continue;
            }
            if ($type === 'link' && $extension !== 'txt') {
                continue;
            }

            $relativePath = $this->normalizeRelativePath($fileInfo->getPathname());
            if ($relativePath === null) {
                continue;
            }

            $entry = $this->buildEntryFromPath($type, $relativePath, $fileInfo);
            if ($entry) {
                $entries[] = $entry;
            }
        }

        usort($entries, function ($a, $b) {
            return strcmp($b['uploaded_at'], $a['uploaded_at']);
        });

        return $entries;
    }

    public function getStats($type)
    {
        $entries = $this->listEntries($type);
        $totalSize = 0;
        foreach ($entries as $entry) {
            $totalSize += isset($entry['size']) ? (int)$entry['size'] : 0;
        }

        return array(
            'count' => count($entries),
            'size' => $totalSize
        );
    }

    public function getPaginatedEntries($type, $page = 1, $perPage = 20)
    {
        if (!$this->isValidType($type)) {
            return array(
                'entries' => array(),
                'total' => 0,
                'totalSize' => 0,
                'page' => 1,
                'perPage' => max(1, (int)$perPage),
                'totalPages' => 1,
                'offset' => 0
            );
        }

        $page = max(1, (int)$page);
        $perPage = max(1, (int)$perPage);

        $entries = $this->listEntries($type);
        $total = count($entries);
        $totalSize = 0;
        foreach ($entries as $entry) {
            $totalSize += isset($entry['size']) ? (int)$entry['size'] : 0;
        }

        $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
        $page = min($page, $totalPages);
        $offset = $total > 0 ? ($page - 1) * $perPage : 0;
        $pagedEntries = array_slice($entries, $offset, $perPage);

        return array(
            'entries' => $pagedEntries,
            'total' => $total,
            'totalSize' => $totalSize,
            'page' => $total > 0 ? $page : 1,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'offset' => $offset
        );
    }

    public function formatBytes($bytes)
    {
        $bytes = (int)$bytes;
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = array('KB', 'MB', 'GB', 'TB');
        $bytes = $bytes / 1024;
        foreach ($units as $unit) {
            if ($bytes < 1024) {
                return round($bytes, 2) . ' ' . $unit;
            }
            $bytes = $bytes / 1024;
        }

        return round($bytes, 2) . ' PB';
    }

    public function addEntityFile(array $file, $category)
    {
        $this->assertUploadValid($file);
        $this->assertCategory('entity', $category);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedImageExtensions, true)) {
            throw new RuntimeException('IMAGE_LIBRARY_UNSUPPORTED_IMAGE:' . $extension);
        }

        $targetDir = $this->getCategoryDirectory('entity', $category);
        $this->ensureDirectory($targetDir);

        $fileName = $this->generateStoredFileName($file['name']);
        $targetPath = $targetDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return $this->buildEntryFromPath('entity', $this->buildRelativePath('entity', $category, $fileName));
    }

    public function addLinkFile(array $file, $category)
    {
        $this->assertUploadValid($file);
        $this->assertCategory('link', $category);

        if ($file['size'] > $this->maxLinkFileSize) {
            throw new RuntimeException('IMAGE_LIBRARY_LINK_SIZE_LIMIT');
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'txt') {
            throw new RuntimeException('IMAGE_LIBRARY_UNSUPPORTED_LINK_FORMAT');
        }

        $targetDir = $this->getCategoryDirectory('link', $category);
        $this->ensureDirectory($targetDir);

        $fileName = $this->generateStoredFileName($file['name']);
        $targetPath = $targetDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        return $this->buildEntryFromPath('link', $this->buildRelativePath('link', $category, $fileName));
    }

    public function deleteEntry($type, $id)
    {
        if (!$this->isValidType($type)) {
            return false;
        }

        $relativePath = $this->decodeEntryId($type, $id);
        if ($relativePath === null) {
            return false;
        }

        $path = $this->getAbsolutePath($relativePath);
        if (!is_file($path)) {
            return true;
        }

        return @unlink($path);
    }

    public function deleteEntries($type, array $ids)
    {
        $successCount = 0;
        foreach ($ids as $id) {
            if ($this->deleteEntry($type, $id)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    public function findEntry($type, $id)
    {
        if (!$this->isValidType($type)) {
            return null;
        }

        $relativePath = $this->decodeEntryId($type, $id);
        if ($relativePath === null) {
            return null;
        }

        return $this->buildEntryFromPath($type, $relativePath);
    }

    public function getAbsolutePath($relativePath)
    {
        return $this->basePath . str_replace(array('/', '\\'), DS, $relativePath);
    }

    public function getEntityPreviewData(array $entry)
    {
        $path = $this->getAbsolutePath($entry['path']);
        if (!is_file($path)) {
            throw new RuntimeException('File not found.');
        }

        $info = getimagesize($path);

        return array(
            'id' => $entry['id'],
            'name' => $entry['original_name'],
            'category' => $entry['category'],
            'category_label' => $this->translateCategory($entry['category']),
            'size' => $entry['size'],
            'uploaded_at' => $entry['uploaded_at'],
            'dimensions' => $info ? ($info[0] . 'x' . $info[1]) : null,
            'url' => HTML_PATH_ADMIN_ROOT . 'ajax/image-library-preview?type=entity&id=' . urlencode($entry['id']) . '&format=raw'
        );
    }

    public function getLinkPreviewData(array $entry)
    {
        $path = $this->getAbsolutePath($entry['path']);
        if (!is_file($path)) {
            throw new RuntimeException('Link file not found.');
        }

        $lines = $this->readLinkLines($path, 50);

        return array(
            'id' => $entry['id'],
            'name' => $entry['original_name'],
            'category' => $entry['category'],
            'category_label' => $this->translateCategory($entry['category']),
            'size' => $entry['size'],
            'uploaded_at' => $entry['uploaded_at'],
            'quantity' => $entry['quantity'],
            'lines' => $lines
        );
    }

    public function streamEntityFile(array $entry)
    {
        $path = $this->getAbsolutePath($entry['path']);
        if (!is_file($path)) {
            throw new RuntimeException('File not found.');
        }

        $mime = $this->detectMimeType($path);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    public function streamLinkFile(array $entry)
    {
        $path = $this->getAbsolutePath($entry['path']);
        if (!is_file($path)) {
            throw new RuntimeException('Link file not found.');
        }

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    protected function ensureDirectory($path)
    {
        if (!is_dir($path)) {
            @mkdir($path, DIR_PERMISSIONS, true);
        }
    }

    protected function invalidateCategoryCache()
    {
        $this->categoryCache = array();
    }

    protected function isSafeCategoryName($category)
    {
        if ($category === null) {
            return false;
        }

        $category = (string)$category;
        if ($category === '') {
            return false;
        }

        if (trim($category) === '') {
            return false;
        }

        if (strpos($category, '..') !== false) {
            return false;
        }

        if (preg_match('/[\\\/]/', $category)) {
            return false;
        }

        return true;
    }

    protected function assertUploadValid(array $file)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            throw new RuntimeException('Invalid upload parameters.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed with error code: ' . $file['error']);
        }

        $tmp = isset($file['tmp_name']) ? $file['tmp_name'] : '';
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new RuntimeException('Potential file upload attack detected.');
        }
    }

    protected function assertCategory($type, $category)
    {
        $category = trim((string)$category);

        if (!$this->isSafeCategoryName($category)) {
            throw new RuntimeException('Invalid category provided.');
        }

        if (!$this->isValidType($type)) {
            throw new RuntimeException('Invalid category type provided.');
        }

        if (!$this->isValidCategory($category, $type)) {
            throw new RuntimeException('Invalid category provided.');
        }

        $directory = $this->getCategoryDirectory($type, $category);
        if (!is_dir($directory)) {
            $this->ensureDirectory($directory);
            $this->invalidateCategoryCache();
        }
    }

    protected function getCategoryDirectory($type, $category)
    {
        return $this->getTypeDirectory($type) . $category . DS;
    }

    protected function buildRelativePath($type, $category, $fileName)
    {
        return $this->getTypeFolder($type) . '/' . $category . '/' . $fileName;
    }

    protected function buildEntryFromPath($type, $relativePath, $fileInfo = null)
    {
        $absolute = $this->getAbsolutePath($relativePath);
        if (!is_file($absolute)) {
            return null;
        }

        $parts = explode('/', $relativePath);
        if (count($parts) < 2) {
            return null;
        }

        $typeFolder = array_shift($parts);
        if ($typeFolder !== $this->getTypeFolder($type)) {
            return null;
        }

        $storedName = array_pop($parts);
        $category = count($parts) > 0 ? $parts[0] : 'other';

        return array(
            'id' => $this->encodeEntryId($type, $relativePath),
            'original_name' => $this->extractOriginalName($storedName),
            'stored_name' => $storedName,
            'path' => $relativePath,
            'category' => $category,
            'category_label' => $this->translateCategory($category),
            'size' => $fileInfo ? $fileInfo->getSize() : filesize($absolute),
            'uploaded_at' => date('Y-m-d H:i:s', $fileInfo ? $fileInfo->getMTime() : filemtime($absolute)),
            'quantity' => $type === 'link' ? $this->countLinkLines($absolute) : 1
        );
    }

    protected function normalizeRelativePath($absolutePath)
    {
        $normalizedBase = rtrim(realpath($this->basePath) ?: $this->basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $normalizedAbsolute = realpath($absolutePath) ?: $absolutePath;
        $normalizedAbsolute = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $normalizedAbsolute);

        if (strpos($normalizedAbsolute, $normalizedBase) !== 0) {
            return null;
        }

        $relative = substr($normalizedAbsolute, strlen($normalizedBase));
        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }

    protected function generateStoredFileName($originalName)
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $encodedOriginal = $this->encodeFileName($originalName);

        $unique = uniqid();
        if (function_exists('random_bytes')) {
            try {
                $unique = bin2hex(random_bytes(4));
            } catch (Exception $e) {
                $unique = uniqid();
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(4);
            if ($bytes !== false) {
                $unique = bin2hex($bytes);
            }
        }

        return $encodedOriginal . '__' . $unique . ($extension ? '.' . $extension : '');
    }

    protected function encodeFileName($name)
    {
        return rtrim(strtr(base64_encode($name), '+/', '-_'), '=');
    }

    protected function decodeFileName($encoded)
    {
        $padding = strlen($encoded) % 4;
        if ($padding) {
            $encoded .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($encoded, '-_', '+/'));
        return $decoded !== false ? $decoded : $encoded;
    }

    protected function extractOriginalName($storedName)
    {
        $parts = explode('__', $storedName, 2);
        if (count($parts) === 2) {
            return $this->decodeFileName($parts[0]);
        }

        return $storedName;
    }

    protected function encodeEntryId($type, $relativePath)
    {
        $prefix = $this->types[$type]['prefix'] . '_';
        $encoded = rtrim(strtr(base64_encode($relativePath), '+/', '-_'), '=');
        return $prefix . $encoded;
    }

    protected function decodeEntryId($type, $id)
    {
        $prefix = $this->types[$type]['prefix'] . '_';
        if (strpos($id, $prefix) !== 0) {
            return null;
        }

        $encoded = substr($id, strlen($prefix));
        $padding = strlen($encoded) % 4;
        if ($padding) {
            $encoded .= str_repeat('=', 4 - $padding);
        }

        $relativePath = base64_decode(strtr($encoded, '-_', '+/'));
        if ($relativePath === false) {
            return null;
        }

        return $relativePath;
    }

    protected function countLinkLines($absolutePath)
    {
        $handle = fopen($absolutePath, 'r');
        if ($handle === false) {
            return 0;
        }

        $count = 0;
        while (($line = fgets($handle)) !== false) {
            if (trim($line) !== '') {
                $count++;
            }
        }

        fclose($handle);
        return $count;
    }

    protected function readLinkLines($absolutePath, $limit = null)
    {
        $result = array();
        $handle = fopen($absolutePath, 'r');
        if ($handle === false) {
            return $result;
        }

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $result[] = $line;
            if ($limit !== null && count($result) >= $limit) {
                break;
            }
        }

        fclose($handle);
        return $result;
    }

    protected function detectMimeType($path)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            if ($mime !== false) {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    protected function translateCategory($category)
    {
        global $L;
        if (isset($L) && is_object($L) && method_exists($L, 'g')) {
            $translated = $L->g('image-library-category-' . $category);
            if ($translated !== 'image-library-category-' . $category) {
                return $translated;
            }
        }

        return $category;
    }

    protected function getManifestPath($type)
    {
        return $this->getTypeDirectory($type) . 'manifest.json';
    }

    protected function migrateLegacyManifest($type)
    {
        $path = $this->getManifestPath($type);
        if (!file_exists($path)) {
            return;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (!is_array($data)) {
            @unlink($path);
            return;
        }

        foreach ($data as $entry) {
            $relativePath = isset($entry['path']) ? $entry['path'] : '';
            if ($relativePath === '') {
                continue;
            }

            $absolute = $this->getAbsolutePath($relativePath);
            if (!is_file($absolute)) {
                continue;
            }

            $entryCategory = isset($entry['category']) ? $entry['category'] : '';
            $category = $this->isValidCategory($entryCategory, $type) ? $entryCategory : 'other';
            $targetDir = $this->getCategoryDirectory($type, $category);
            $this->ensureDirectory($targetDir);

            $originalName = isset($entry['original_name']) ? $entry['original_name'] : basename($relativePath);
            $newFileName = $this->generateStoredFileName($originalName);
            $targetPath = $targetDir . $newFileName;

            if (@rename($absolute, $targetPath)) {
                if (!empty($entry['uploaded_at'])) {
                    $timestamp = strtotime($entry['uploaded_at']);
                    if ($timestamp) {
                        @touch($targetPath, $timestamp);
                    }
                }

                $this->cleanupEmptyDirectory(dirname($absolute), $this->getTypeDirectory($type));
            }
        }

    $this->invalidateCategoryCache();

    @unlink($path);
    }

    protected function cleanupEmptyDirectory($directory, $stopDirectory)
    {
        if (!is_dir($directory)) {
            return;
        }

        $directory = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $stopDirectory = rtrim($stopDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($directory === $stopDirectory) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..') {
                return;
            }
        }

        @rmdir($directory);

        $parent = dirname(rtrim($directory, DIRECTORY_SEPARATOR));
        if ($parent && $parent !== $directory) {
            $this->cleanupEmptyDirectory($parent, $stopDirectory);
        }
    }
}
