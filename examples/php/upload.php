<?php

    require_once('../qcubed.inc.php');
    require_once ('../../src/FileHandler.php');

    use QCubed\Plugin\FileHandler;
    use QCubed\Exception\Caller;
    use QCubed\Exception\InvalidCast;

    $options = array(
        //'ImageResizeQuality' => 75, // Default 90
        //'ImageResizeFunction' => 'imagecopyresized', // Default imagecopyresampled
        //'ImageResizeSharpen' => false, // Default true
        //'TempFolders' => ['thumbnail', 'medium', 'large'], // Please read the UploadHandler description and manual
        //'ResizeDimensions' => [320, 480, 1500], // Please read the UploadHandler description and manual
        //'DestinationPath' => null, // Please read the UploadHandler description and manual
        'AcceptFileTypes' => ['gif', 'jpg', 'jpeg', 'png', 'pdf', 'ppt', 'docx', 'xlsx', 'txt', 'mp4', 'mov', 'svg', 'zip'], // Default null
        'DestinationPath' => !empty($_SESSION["filePath"]) ? $_SESSION["filePath"] : null, // Default null
        //'MaxFileSize' => 1024 * 1024 * 2 // 2 MB // Default null
        //'UploadExists' => 'overwrite', // increment || overwrite Default 'increment'
    );


    /**
     * Class CustomFileUploadHandler
     *
     * Handles file uploads, processes and stores file metadata,
     * and updates associated folder information.
     */
    class CustomFileUploadHandler extends FileHandler
    {
        /**
         * Uploads file information and updates file records in the database.
         *
         * This method processes a file by setting its metadata, saves the file to the database,
         * and associates files without a folder ID with a default folder.
         *
         * @return void
         * @throws Caller
         * @throws InvalidCast
         */
        protected function uploadInfo(): void
        {
            parent::uploadInfo();

            if ($this->options['FileError'] == 0) {
                $obj = new Files();
                $obj->setName(basename($this->options['FileName']));
                $obj->setType('file');
                $obj->setPath($this->getRelativePath($this->options['FileName']));
                $obj->setDescription(null);
                $obj->setExtension($this->getExtension($this->options['FileName']));
                $obj->setMimeType($this->getMimeType($this->options['FileName']));
                $obj->setSize($this->options['FileSize']);
                $obj->setMtime(filemtime($this->options['FileName']));
                $obj->setDimensions($this->getDimensions($this->options['FileName']));
                $obj->setWidth($this->getImageWidth($this->options['FileName']));
                $obj->setHeight($this->getImageHeight($this->options['FileName']));
                $obj->save(true);
            }

            $filesWithoutFolder = [];

            // Find files without a folder ID
            foreach (Files::loadAll() as $file) {
                if ($file->FolderId === null) {
                    $filesWithoutFolder[] = $file->Id;
                }
            }

            // Update folderId for files without a folder ID
            foreach ($filesWithoutFolder as $fileId) {
                $file = Files::loadById($fileId);
                $file->setFolderId($_SESSION['folderId']);
                $file->save();
            }
        }

        /**
         * Get the width of an image file
         *
         * @param string $path The file path of the image
         *
         * @return int|string The width of the image in pixels, or '0' if the width is not available
         */
        public static function getImageWidth(string $path): mixed
        {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $ImageSize = getimagesize($path);

            if (in_array($ext, self::getImageExtensions())) {
                return ($ImageSize[0] ?? '0');
            }

            return '0';
        }

        /**
         * Get the height of an image specified by its file path.
         *
         * @param string $path The file path of the image.
         *
         * @return mixed The height of the image as an integer, or '0' if the height cannot be determined.
         */
        public static function getImageHeight(string $path): mixed
        {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $ImageSize = getimagesize($path);

            if (in_array($ext, self::getImageExtensions())) {
                return ($ImageSize[1] ?? '0');
            }

            return '0';
        }

        /**
         * Returns an array of supported image file extensions.
         *
         * @return array An array of strings representing image file extensions.
         */
        public static function getImageExtensions(): array
        {
            return array('jpg', 'jpeg', 'bmp', 'png', 'webp', 'gif', 'svg');
        }
    }

    try {
        $objHandler = new CustomFileUploadHandler($options);
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Upload handler creation error: ' . $e->getMessage());
        echo json_encode(['error' => 'Failed to start the file/s upload system.']);
        exit;
    }