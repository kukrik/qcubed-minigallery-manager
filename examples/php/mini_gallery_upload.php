<?php

    require_once('../qcubed.inc.php');
    require('../../src/VauuFileHandler.php');

    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;
    use QCubed\Plugin\VauuFileHandler;
    use QCubed\QDateTime;

    $options = array(
        //'ImageResizeQuality' => 75, // Default 85
        //'ImageResizeFunction' => 'imagecopyresized', // Default imagecopyresampled
        //'ImageResizeSharpen' => false, // Default true
        //'TempFolders' => ['thumbnail', 'medium', 'large'], // Please read the FileHandler description and manual
        //'ResizeDimensions' => [320, 480, 1500], // Please read the FileHandler description and manual
        //'DestinationPath' => null, // Please read the FileHandler description and manual
        'AcceptFileTypes' => ['jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif'], // Default null
        'DestinationPath' => !empty($_SESSION['folderPath']) ? $_SESSION['folderPath'] : null, // Default null
        //'MaxFileSize' => 1024 * 1024 * 2, // 2 MB // Default null
        //'MinFileSize' => 500000, // 500 kb // Default null
        //'UploadExists' => 'overwrite', // increment || overwrite Default 'increment'
    );

    /**
     * Class CustomFileUploadHandler
     *
     * Handles file uploads, processes and stores file metadata,
     * and updates associated folder information.
     */
    class MiniGalleryFileHandler extends VauuFileHandler
    {
        /**
         * Save uploaded file metadata to a database and only then output JSON.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         * @throws Exception
         */
        protected function uploadInfo(): void
        {
            if (($this->options['FileError'] ?? 0) != 0) {
                $this->handleError('File upload contains an error state.', $this->options['FileName'] ?? null);
            }

            if (empty($this->options['FileName']) || !is_file($this->options['FileName'])) {
                $this->handleError("An uploaded file isn't found after upload.", $this->options['FileName'] ?? null);
            }

            $fullPath = $this->options['FileName'];
            $extension = $this->getExtension($fullPath);
            $mimeType = $this->getMimeType($fullPath);
            $size = filesize($fullPath);
            $mtime = filemtime($fullPath);
            $dimensions = $this->getDimensions($fullPath);
            $width = $this->getImageWidth($fullPath);
            $height = $this->getImageHeight($fullPath);

            $obj = new Files();
            $obj->setFolderId($_SESSION['folderId']);
            $obj->setName(basename($fullPath));
            $obj->setType('file');
            $obj->setPath($this->getRelativePath($fullPath));
            $obj->setDescription(null);
            $obj->setExtension($extension);
            $obj->setMimeType($mimeType);
            $obj->setSize($size);
            $obj->setMtime($mtime);
            $obj->setDimensions($dimensions);
            $obj->setWidth($width);
            $obj->setHeight($height);
            $obj->setLockedFile(1);
            $obj->setActivitiesLocked(1);
            $obj->save(true);

            $objMiniGallery = new MiniGallery();
            $objMiniGallery->setContentCoverMediaId($_SESSION['coverMediaId']);
            $objMiniGallery->setFolderId($_SESSION['folderId']);
            $objMiniGallery->setFileId($obj->getId());
            $objMiniGallery->setName(basename($fullPath));
            $objMiniGallery->setPath($this->getRelativePath($fullPath));
            $objMiniGallery->setStatus(1);
            $objMiniGallery->setPostDate(QCubed\QDateTime::now());
            $objMiniGallery->save();

            $objContentCoverMedia = ContentCoverMedia::loadById($_SESSION['coverMediaId']);
            $objContentCoverMedia->setPostUpdateDate(QDateTime::now());
            $objContentCoverMedia->save();

            $objFolder = Folders::loadById($_SESSION['folderId']);

            if ($objFolder->getLockedFile() == 0) {
                $objFolder->setLockedFile(1);
                $objFolder->save();
            }

            print json_encode(array(
                'filename' => basename($fullPath),
                'path' => $this->getRelativePath($fullPath),
                'extension' => $extension,
                'type' => $this->options['FileType'],
                'error' => 0,
                'size' => $size,
                'mtime' => $mtime,
                'dimensions' => $dimensions
            ), JSON_UNESCAPED_UNICODE);
        }

        /**
         * Get the width of an image file.
         *
         * @param string $path
         * @return int|string
         */
        public static function getImageWidth(string $path): mixed
        {
            if (!is_file($path)) {
                return '0';
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($ext === 'svg') {
                return '0';
            }

            $imageSize = @getimagesize($path);

            if ($imageSize && in_array($ext, self::getImageExtensions(), true)) {
                return ($imageSize[0] ?? '0');
            }

            return '0';
        }

        /**
         * Get the height of an image file.
         *
         * @param string $path
         * @return int|string
         */
        public static function getImageHeight(string $path): mixed
        {
            if (!is_file($path)) {
                return '0';
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            if ($ext === 'svg') {
                return '0';
            }

            $imageSize = @getimagesize($path);

            if ($imageSize && in_array($ext, self::getImageExtensions(), true)) {
                return ($imageSize[1] ?? '0');
            }

            return '0';
        }

        /**
         * Supported image extensions.
         *
         * @return array
         */
        public static function getImageExtensions(): array
        {
            return array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg');
        }
    }

    try {
        $objHandler = new MiniGalleryFileHandler($options);
    } catch (Throwable $e) {
        http_response_code(500);

        error_log('Upload error: ' . $e->getMessage());
        error_log($e->getTraceAsString());

        $decoded = json_decode($e->getMessage(), true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            echo json_encode($decoded, JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'error' => 'Failed to start the file/s upload system.',
                'details' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }

        exit;
    }