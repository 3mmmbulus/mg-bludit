<?php defined('MAIGEWAN') or die('Maigewan CMS.');

require_once PATH_HELPERS . 'imagelibrary.class.php';

class CommentLibrary extends ImageLibrary
{
    protected $configDirectoryName = 'comments';
    protected $translationPrefix = 'comments-library';
    protected $types = array(
        'entity' => array(
            'folder' => 'entities',
            'prefix' => 'CMT'
        ),
        'link' => array(
            'folder' => 'nicknames',
            'prefix' => 'NCK'
        )
    );
    protected $allowedImageExtensions = array('txt');
    protected $maxLinkFileSize = 5242880; // 5MB for nickname text files
    protected $maxTextFileSize = 5242880; // 5MB for comment content files
    protected $metadataFile = 'metadata.json';
    protected $metadata = array();
    protected $statusOptions = array('active', 'paused', 'archived');
    protected $maxUploadFiles = 100;

    public function __construct($basePath = null)
    {
        parent::__construct($basePath);
        $this->loadMetadata();
    }

    public function getStatusOptions()
    {
        return $this->statusOptions;
    }

    public function getMaxUploadFiles()
    {
        return $this->maxUploadFiles;
    }

    public function getAllEntries()
    {
        return array(
            'content' => $this->listEntries('entity'),
            'nicknames' => $this->listEntries('link')
        );
    }

    public function getStatusSummary()
    {
        $summary = array();
        foreach ($this->statusOptions as $status) {
            $summary[$status] = 0;
        }

        foreach ($this->metadata as $meta) {
            $status = isset($meta['status']) ? $meta['status'] : 'active';
            if (!isset($summary[$status])) {
                $summary[$status] = 0;
            }
            $summary[$status]++;
        }

        return $summary;
    }

    public function getSources()
    {
        $sources = array();
        foreach ($this->metadata as $meta) {
            if (empty($meta['source_file'])) {
                continue;
            }
            $sources[$meta['source_file']] = true;
        }
        return array_keys($sources);
    }

    public function addEntityFile(array $file, $category)
    {
        return $this->addTextFile('entity', 'content', $file, $category);
    }

    public function addLinkFile(array $file, $category)
    {
        return $this->addTextFile('link', 'nickname', $file, $category);
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

        $result = parent::deleteEntry($type, $id);
        if ($result) {
            $this->deleteMetadata($relativePath);
        }

        return $result;
    }

    public function deleteEntries($type, array $ids)
    {
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->deleteEntry($type, $id)) {
                $deleted++;
            }
        }
        return $deleted;
    }

    public function updateStatus($type, $id, $status)
    {
        if (!$this->isValidType($type)) {
            throw new RuntimeException('Invalid type.');
        }

        $relativePath = $this->decodeEntryId($type, $id);
        if ($relativePath === null) {
            throw new RuntimeException('Invalid identifier.');
        }

        $status = $this->normalizeStatus($status);
        $meta = $this->getMetadata($relativePath);
        $meta['status'] = $status;
        $this->setMetadata($relativePath, $meta);

        return true;
    }

    public function incrementUsage($type, $id, $amount = 1)
    {
        if (!$this->isValidType($type)) {
            return false;
        }

        $relativePath = $this->decodeEntryId($type, $id);
        if ($relativePath === null) {
            return false;
        }

        $meta = $this->getMetadata($relativePath);
        $current = isset($meta['usage_count']) ? (int)$meta['usage_count'] : 0;
        $meta['usage_count'] = max(0, $current + (int)$amount);
        $this->setMetadata($relativePath, $meta);

        return true;
    }

    public function getDetail($type, $id, $limit = 100)
    {
        if (!$this->isValidType($type)) {
            throw new RuntimeException('Invalid type.');
        }

        $entry = $this->findEntry($type, $id);
        if (!$entry) {
            throw new RuntimeException('Entry not found.');
        }

        $relativePath = $this->decodeEntryId($type, $id);
        if ($relativePath === null) {
            throw new RuntimeException('Invalid identifier.');
        }

        $absolute = $this->getAbsolutePath($relativePath);
        if (!is_file($absolute)) {
            throw new RuntimeException('File not found.');
        }

        $lines = $this->readLinkLines($absolute, $limit);
        $meta = $this->getMetadata($relativePath);

        return array(
            'entry' => $entry,
            'preview' => $lines,
            'metadata' => array(
                'status' => isset($meta['status']) ? $meta['status'] : 'active',
                'source_file' => isset($meta['source_file']) ? $meta['source_file'] : $entry['original_name'],
                'imported_at' => isset($meta['imported_at']) ? $meta['imported_at'] : $entry['uploaded_at'],
                'usage_count' => isset($meta['usage_count']) ? (int)$meta['usage_count'] : 0,
                'source_type' => isset($meta['source_type']) ? $meta['source_type'] : ($type === 'entity' ? 'content' : 'nickname')
            )
        );
    }

    protected function addTextFile($type, $sourceType, array $file, $category)
    {
        $this->assertUploadValid($file);
        $this->assertCategory($type, $category);

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'txt') {
            throw new RuntimeException('COMMENTS_LIBRARY_UNSUPPORTED_FORMAT');
        }

        $limit = $type === 'entity' ? $this->maxTextFileSize : $this->maxLinkFileSize;
        if ($limit > 0 && isset($file['size']) && (int)$file['size'] > $limit) {
            throw new RuntimeException('COMMENTS_LIBRARY_FILE_TOO_LARGE');
        }

        $targetDir = $this->getCategoryDirectory($type, $category);
        $this->ensureDirectory($targetDir);

        $fileName = $this->generateStoredFileName($file['name']);
        $targetPath = $targetDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new RuntimeException('Failed to move uploaded file.');
        }

        $relativePath = $this->buildRelativePath($type, $category, $fileName);
        $this->setMetadata($relativePath, array(
            'status' => 'active',
            'source_file' => $file['name'],
            'source_type' => $sourceType,
            'usage_count' => 0,
            'imported_at' => date('Y-m-d H:i:s')
        ));

        return $this->buildEntryFromPath($type, $relativePath);
    }

    protected function buildEntryFromPath($type, $relativePath, $fileInfo = null)
    {
        $entry = parent::buildEntryFromPath($type, $relativePath, $fileInfo);
        if (!$entry) {
            return null;
        }

        $absolute = $this->getAbsolutePath($relativePath);
        $lineCount = $this->countLinkLines($absolute);
        $entry['line_count'] = $lineCount;
        $entry['quantity'] = $lineCount;

        $meta = $this->getMetadata($relativePath);
        $entry['status'] = isset($meta['status']) ? $meta['status'] : 'active';
        $entry['source_file'] = isset($meta['source_file']) ? $meta['source_file'] : $entry['original_name'];
        $entry['imported_at'] = isset($meta['imported_at']) ? $meta['imported_at'] : $entry['uploaded_at'];
        $entry['usage_count'] = isset($meta['usage_count']) ? (int)$meta['usage_count'] : 0;
        $entry['source_type'] = isset($meta['source_type']) ? $meta['source_type'] : ($type === 'entity' ? 'content' : 'nickname');

        return $entry;
    }

    protected function loadMetadata()
    {
        $path = $this->getMetadataPath();
        if (!is_file($path)) {
            $this->metadata = array();
            return;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $this->metadata = array();
            return;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $this->metadata = $decoded;
        } else {
            $this->metadata = array();
        }
    }

    protected function saveMetadata()
    {
        $path = $this->getMetadataPath();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            $this->ensureDirectory($directory . DIRECTORY_SEPARATOR);
        }

        $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        @file_put_contents($path, json_encode($this->metadata, $options));
    }

    protected function getMetadataPath()
    {
        return $this->basePath . $this->metadataFile;
    }

    protected function getMetadata($relativePath)
    {
        return isset($this->metadata[$relativePath]) && is_array($this->metadata[$relativePath])
            ? $this->metadata[$relativePath]
            : array();
    }

    protected function setMetadata($relativePath, array $data)
    {
        $this->metadata[$relativePath] = array_merge(
            array(
                'status' => 'active',
                'source_file' => '',
                'source_type' => 'content',
                'usage_count' => 0,
                'imported_at' => date('Y-m-d H:i:s')
            ),
            $data
        );
        $this->saveMetadata();
    }

    protected function deleteMetadata($relativePath)
    {
        if (isset($this->metadata[$relativePath])) {
            unset($this->metadata[$relativePath]);
            $this->saveMetadata();
        }
    }

    protected function normalizeStatus($status)
    {
        $status = strtolower(trim((string)$status));
        if (!in_array($status, $this->statusOptions, true)) {
            return 'active';
        }
        return $status;
    }

    public function exportAsDataset()
    {
        $records = array();
        foreach ($this->listEntries('entity') as $entry) {
            $records[] = $this->normalizeForFrontend('entity', $entry);
        }
        foreach ($this->listEntries('link') as $entry) {
            $records[] = $this->normalizeForFrontend('link', $entry);
        }
        return $records;
    }

    protected function normalizeForFrontend($type, array $entry)
    {
        return array(
            'id' => $entry['id'],
            'type' => $type === 'entity' ? 'content' : 'nickname',
            'category' => $entry['category'],
            'categoryLabel' => $entry['category_label'],
            'sourceFile' => $entry['source_file'],
            'status' => $entry['status'],
            'lineCount' => (int)$entry['line_count'],
            'usageCount' => (int)$entry['usage_count'],
            'importedAt' => $entry['imported_at'],
            'uploadedAt' => $entry['uploaded_at'],
            'size' => (int)$entry['size'],
            'storedName' => $entry['stored_name'],
            'originalName' => $entry['original_name'],
            'typeLabel' => $entry['source_type']
        );
    }
}
